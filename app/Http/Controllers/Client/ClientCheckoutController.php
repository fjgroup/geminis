<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\Client\PlaceOrderRequest; // Añadir al inicio del archivo
use App\Actions\Client\PlaceOrderAction;        // Añadir al inicio del archivo
use Illuminate\Support\Facades\Auth;            // Añadir al inicio del archivo
use Illuminate\Http\RedirectResponse;           // Añadir al inicio del archivo
use Illuminate\Support\Facades\Log;             // Añadir al inicio del archivo
use Illuminate\Validation\ValidationException;  // Añadir al inicio del archivo
use Illuminate\Database\Eloquent\ModelNotFoundException; // Añadir al inicio del archivo
use Exception; // Asegurarse que Exception esté importado

class ClientCheckoutController extends Controller
{
    /**
     * Show the form for creating a new order/invoice for a specific product.
     *
     * @param  Product  $product
     * @return InertiaResponse
     */
    public function showProductCheckoutPage(Product $product): InertiaResponse
    {
        // Autorización similar a la que tenía ClientOrderController@showOrderForm
        // $this->authorize('view', $product); // Asumiendo ProductPolicy@view
        // For now, policies are not strictly enforced in this step for this controller.
        // This can be added later if ProductPolicy or a general CheckoutPolicy is established.

        $product->load(['pricings.billingCycle', 'configurableOptionGroups.options', 'productType']);

        return Inertia::render('Client/Checkout/ProductCheckoutPage', [ // Actualizar la ruta de Inertia
            'product' => $product,
        ]);
    }

    public function submitProductCheckout(PlaceOrderRequest $request, Product $product, PlaceOrderAction $placeOrderAction): RedirectResponse
    {
        // Podríamos considerar una política aquí, ej. $this->authorize('create', Invoice::class);
        // o $this->authorize('checkout', $product);

        $validatedData = $request->validated();
        $client = Auth::user();

        // Añadir ip_address al $validatedData si no viene del request y PlaceOrderAction lo espera
        if (!isset($validatedData['ip_address'])) {
            $validatedData['ip_address'] = $request->ip();
        }

        try {
            $invoice = $placeOrderAction->execute($product, $validatedData, $client);

            if (!$invoice) {
                Log::error("PlaceOrderAction returned null during checkout for product ID {$product->id} and client ID {$client->id}");
                return redirect()->back()->withInput()->with('error', 'No se pudo crear una factura para su solicitud. Por favor, inténtelo de nuevo.');
            }

            return redirect()->route('client.invoices.show', $invoice->id)
                                ->with('success', 'Factura generada exitosamente. Por favor, proceda con el pago.');

        } catch (ValidationException $e) {
            // Los errores de validación de PlaceOrderRequest ya deberían manejarse antes de llegar aquí.
            // Esto sería para validaciones internas de la acción, si las hubiera.
            Log::warning("ValidationException en submitProductCheckout: " . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            Log::error("Recurso no encontrado durante el checkout (ej. ProductPricing): " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'exception_trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                                ->withInput()
                                ->with('error', 'La opción de precio seleccionada no es válida o no se encontró. Por favor, inténtelo de nuevo.');
        } catch (Exception $e) {
            Log::error("Error al procesar el checkout para el producto ID {$product->id}: " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'exception_trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                                ->withInput()
                                ->with('error', 'Ocurrió un error inesperado al procesar su solicitud. Por favor, inténtelo de nuevo.');
        }
    }
}
