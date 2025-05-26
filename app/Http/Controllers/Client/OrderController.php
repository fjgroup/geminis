<?php

namespace App\Http\Controllers\Client;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductPricing;
use App\Models\ConfigurableOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Necesario si usamos transacciones o helpers de DB
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\Client\StoreClientOrderRequest;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Muestra el formulario para crear una orden para un producto específico.
     *
     * @param  \App\Models\Product  $product
     * @return \Inertia\Response
     */
    public function showOrderForm(Product $product)
    {
        $product->load([
            'productPricings.billingCycle',
            'configurableOptionGroups.configurableOptions.optionPricings.billingCycle'
        ]);

        return Inertia::render('Client/Orders/Create', [
            'product' => $product,
        ]);
    }

    /**
     * Procesa la solicitud para crear una nueva orden.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function placeOrder(StoreClientOrderRequest $request, Product $product)
    {
        $this->authorize('create', Order::class);

        // Obtener el usuario autenticado
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Obtener el product pricing seleccionado
            $productPricing = ProductPricing::with('billingCycle')
                ->findOrFail($request->input('product_pricing_id'));

            // Calcular el precio base del producto
            $basePrice = $productPricing->price;
            $totalAmount = $basePrice;

            // Crear la Order
            $order = Order::create([
                'client_id' => $user->id,
                'order_date' => now(),
                'status' => 'pending_payment', // Estado inicial
                'currency_code' => $user->currency_code ?? 'USD', // Usar moneda del usuario o USD por defecto
                'product_pricing_id' => $productPricing->id,
                'billing_cycle_id' => $productPricing->billing_cycle_id, // Guardar el ciclo de facturación
                // Generar un order_number único (puedes usar un helper o esta combinación simple por ahora)
                'order_number' => 'ORD-' . now()->format('YmdHis') . $user->id . '_' . uniqid(mt_rand(), true),
                'total_amount' => $totalAmount, // Monto total inicial con el precio base del producto
            ]);

            // Crear el OrderItem para el producto principal
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_pricing_id' => $productPricing->id,
                'item_type' => 'product',
                'description' => $product->name,
                'quantity' => 1,
                'unit_price' => $basePrice,
                'total_price' => $basePrice,
                'billing_cycle_id' => $productPricing->billing_cycle_id, // Guardar el ciclo de facturación
            ]);

            // Procesar opciones configurables si existen en el request
            $configurableOptionIds = $request->input('configurable_options', []);

            if (!empty($configurableOptionIds)) {
                // Recuperar las opciones configurables seleccionadas con sus precios para el ciclo de facturación
                $configurableOptions = ConfigurableOption::whereIn('id', $configurableOptionIds)
                    ->with(['optionPricings' => function ($query) use ($productPricing) {
                        $query->where('billing_cycle_id', $productPricing->billing_cycle_id);
                    }])
                    ->get();

                foreach ($configurableOptions as $option) {
                    // Encontrar el precio de la opción para el ciclo de facturación seleccionado
                    $optionPricing = $option->optionPricings->first(); // Asumimos que solo hay uno por opción/ciclo

                    if ($optionPricing) {
                        $optionPrice = $optionPricing->price;
                        $totalAmount += $optionPrice;

                        // Crear OrderItem para la opción configurable
                        OrderItem::create([
                            'order_id' => $order->id,
                            'configurable_option_id' => $option->id,
                            'option_pricing_id' => $optionPricing->id,
                            'item_type' => 'configurable_option',
                            'description' => 'Opción: ' . $option->name,
                            'quantity' => 1,
                            'unit_price' => $optionPrice,
                            'total_price' => $optionPrice,
                            'billing_cycle_id' => $productPricing->billing_cycle_id, // Guardar el ciclo de facturación
                        ]);
                    }
                }

            }

            // Actualizar el monto total de la orden después de sumar las opciones configurables
            $order->total_amount = $totalAmount;
            $order->save();

            // Lógica para generar la factura
            $invoice = Invoice::create([
                'client_id' => $user->id,
                // 'reseller_id' => $order->reseller_id ?? null, // Asignar si la orden tiene este campo y aplica
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(30), // 30 días de vencimiento por defecto
                'status' => 'unpaid',
                'subtotal' => $order->total_amount, // Usar el monto total de la orden como subtotal inicial
                'total_amount' => $order->total_amount, // Usar el monto total de la orden
                'currency_code' => $order->currency_code,
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . $order->id . '-' . Str::random(4), // Generar número de factura único
                // Campos adicionales (tax_amount, paid_date, etc.) se dejan nulos por ahora
            ]);

            // Iterar sobre los ítems de la orden y crear ítems de factura
            $order->load('items'); // Cargar los ítems de la orden si no están ya cargados
            foreach ($order->items as $orderItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    // 'client_service_id' => null, // Nulo por ahora, no aplica para nueva orden de producto
                    'order_item_id' => $orderItem->id,
                    'description' => $orderItem->description,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $orderItem->total_price,
                    'taxable' => true, // Por defecto a true según instrucciones
                    // Campos adicionales (tax_rate, tax_amount, etc.) se dejan por defecto o nulos
                ]);
            }

            // Actualizar la orden con el ID de la factura generada
            $order->invoice_id = $invoice->id;
            $order->save();

            // Fin de la lógica de generación de factura

            // Commit la transacción si todo fue exitoso
            DB::commit();

            // Redirigir a una página de confirmación o al dashboard del cliente.
            return redirect()->route('client.dashboard')->with('success', 'Orden creada exitosamente.');

        } catch (\Exception $e) {
            // Rollback la transacción en caso de error
            DB::rollBack();

            // Puedes loguear el error o manejarlo de otra manera
            Log::error('Error al procesar la orden: ' . $e->getMessage());

            // Redirigir de vuelta con un mensaje de error
            return redirect()->back()->withInput()->with('error', 'Hubo un error al procesar su orden. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Muestra un listado de las órdenes del cliente autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $user = Auth::user();

        $orders = $user->orders()->with(['items', 'productPricing.billingCycle'])
                       ->latest() // Opcional: ordenar por fecha de orden descendente
                       ->paginate(10); // Paginación básica de 10 elementos por página

        return Inertia::render('Client/Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
