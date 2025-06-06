<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia; // Importar Inertia
use App\Models\User; // Added User model import
use Inertia\Response as InertiaResponse; // Added InertiaResponse import
use App\Http\Requests\Admin\StoreManualInvoiceRequest; // New
use App\Models\InvoiceItem; // New
use Illuminate\Support\Facades\DB; // New
use Illuminate\Support\Carbon; // New
use Illuminate\Support\Str; // New
use Illuminate\Support\Facades\Log; // New
use Illuminate\Http\RedirectResponse; // New
use App\Http\Requests\Admin\UpdateInvoiceRequest; // New
use App\Models\ClientService; // Asegurarse de importar ClientService
use App\Models\ProductPricing; // Added for ClientService creation
use App\Models\BillingCycle; // Added for ClientService creation (though accessed via relationship)
use App\Models\InvoiceItem; // Added for updating client_service_id on item
use Exception; // Importar Exception
// use Illuminate\Support\Carbon; // Already imported
// use Illuminate\Support\Facades\DB; // Already imported

/**
 * Controlador para la gestión de facturas dentro del panel de administración.
 * Este controlador maneja operaciones relacionadas con la facturación de clientes,
 * generación de facturas, seguimiento de pagos y vinculación de facturas con
 * servicios de cliente, similar a las funcionalidades encontradas en sistemas
 * como WHMCS para proveedores de hosting web y servicios digitales.
 */
class AdminInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class); // Verificación de autorización

        $invoices = Invoice::with(['client', 'items'])->paginate(10); // Obtener facturas paginadas con relaciones

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return InertiaResponse
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', Invoice::class); // Uses InvoicePolicy@create

        // Fetch clients to populate a dropdown/selector in the Vue component
        // Only fetch users who can be clients (e.g., by role or other criteria)
        // Using a simplified role check for now, assuming 'role' is a direct attribute.
        // Adjust if using a roles package like Spatie/laravel-permission.
        // Updated client fetching logic as per current task description
        $clients = User::whereHas('roles', function ($query) { // Assuming Spatie/Permission or similar 'roles' relationship
            $query->where('name', 'client');
        })->orWhereDoesntHave('roles') // Or users with no specific role if that's allowed
          ->select('id', 'name', 'email') // Select only necessary fields
          ->orderBy('name')
          ->get();

        // If not using a roles package and 'role' column exists:
        // $clients = User::where('role', 'client')->select('id', 'name', 'email')->orderBy('name')->get();


        // Define possible initial statuses for a manual invoice
        $possibleStatuses = ['unpaid', 'paid', 'cancelled']; // As per StoreManualInvoiceRequest

        // Define default currency or list of currencies
        $defaultCurrency = 'USD'; // Or from config
        // Updated currencies list as per current task description
        $currencies = ['USD', 'EUR', 'GBP']; // Example, or from a config/database table

        return Inertia::render('Admin/Invoices/Create', [
            'clients' => $clients,
            'possibleStatuses' => $possibleStatuses,
            'defaultCurrency' => $defaultCurrency,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * This is for MANUALLY created invoices by an Admin.
     *
     * @param  StoreManualInvoiceRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreManualInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class); // Uses InvoicePolicy@create

        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($validatedData['items'] as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }
            // For now, total_amount is same as subtotal. Tax calculation is deferred.
            $totalAmount = $subtotal;

            // Create Invoice
            $invoice = Invoice::create([
                'client_id' => $validatedData['client_id'],
                'reseller_id' => User::find($validatedData['client_id'])->reseller_id ?? null, // Attempt to get reseller_id from client
                'invoice_number' => 'MINV-' . strtoupper(Str::random(10)), // Manual Invoice prefix
                'issue_date' => Carbon::parse($validatedData['issue_date']),
                'due_date' => Carbon::parse($validatedData['due_date']),
                'paid_date' => ($validatedData['status'] === 'paid') ? Carbon::now() : null,
                'status' => $validatedData['status'],
                'subtotal' => $subtotal,
                // tax1_name, tax1_rate, tax1_amount etc. are null for now
                'total_amount' => $totalAmount,
                'currency_code' => $validatedData['currency_code'],
                'notes_to_client' => $validatedData['notes_to_client'] ?? null,
                'admin_notes' => $validatedData['admin_notes'] ?? null,
            ]);

            // Create InvoiceItems
            foreach ($validatedData['items'] as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    // client_service_id is null for manual invoice items unless explicitly linked
                    // order_item_id is null as this is not from an order
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => ($itemData['quantity'] * $itemData['unit_price']),
                    'taxable' => $itemData['taxable'] ?? false, // Default to false if not provided
                ]);
            }

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice->id)
                             ->with('success', 'Manual invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual invoice creation failed: ' . $e->getMessage(), ['exception' => $e, 'data' => $validatedData]);
            return redirect()->route('admin.invoices.create')
                             ->withInput()
                             ->with('error', 'Failed to create manual invoice. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice); // Verificación de autorización

        $invoice->load([
            'client',
            'reseller',
            'items.product',
            'items.clientService',
            // Eager-load the latest completed transaction(s) with their payment method
            'transactions' => function ($query) {
                $query->where('status', 'completed')
                      ->with('paymentMethod') // Eager load the paymentMethod relationship on Transaction
                      ->latest('transaction_date'); // Get the most recent ones first
            }
        ]);

        // Indica si se debe mostrar el formulario de pago manual en la vista
        $showManualPaymentForm = in_array($invoice->status, ['unpaid', 'pending_confirmation']);

        // Prepara datos para el formulario de registro de transacción manual
        $manualPaymentFormData = [
            'amount' => $invoice->total_amount, // Asume que total_amount es el monto pendiente o total de la factura
            'currency_code' => $invoice->currency_code,
            'transaction_date' => now()->toDateString(), // Fecha actual por defecto
            // Considera añadir 'payment_method_id' si quieres preseleccionar uno o pasar una lista de métodos de pago
        ];

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
            'showManualPaymentForm' => $showManualPaymentForm,
            'manualPaymentFormData' => $manualPaymentFormData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Invoice  $invoice
     * @return InertiaResponse
     */
    public function edit(Invoice $invoice): InertiaResponse
    {
        $this->authorize('update', $invoice); // Uses InvoicePolicy@update

        // Load necessary relationships if needed for the edit form display, e.g., client
        $invoice->load('client:id,name,email'); // As per current task, removed 'items' from here

        // Define possible statuses for editing an existing invoice.
        // This might be different from creation statuses.
        // For example, you might not allow changing a 'paid' invoice back to 'unpaid' directly here,
        // or 'refunded' might only be set by specific actions.
        // For now, let's assume a broad set and refine with business logic later.
        $possibleStatuses = ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections']; // All current ENUMs

        // Define list of currencies if currency is editable (usually it's not after creation)
        $currencies = ['USD', 'EUR', 'GBP']; // Example

        return Inertia::render('Admin/Invoices/Edit', [
            'invoice' => $invoice,
            'possibleStatuses' => $possibleStatuses,
            'currencies' => $currencies, // Only if currency_code is made editable
            // For editing, client_id is typically not changed. If it needs to be, then pass clients.
            'clients' => User::select('id', 'name', 'email')->orderBy('name')->get(), // Uncommented as per current task
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateInvoiceRequest  $request
     * @param  Invoice  $invoice
     * @return RedirectResponse
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice); // Uses InvoicePolicy@update

        $validatedData = $request->validated();

        DB::beginTransaction(); // Good practice for updates too
        try {
            // Track original status if status is changing
            $originalStatus = $invoice->status;

            // Update main invoice fields that are present in validatedData
            // The UpdateInvoiceRequest uses 'sometimes', so only present fields are validated & passed.
            $invoice->fill($request->only([
                'client_id', 'issue_date', 'due_date', 'status',
                'currency_code', 'notes_to_client', 'admin_notes'
                // Do not include 'paid_date' here directly from fill, handle it based on status
            ]));

            // Handle paid_date specifically based on status
            if (isset($validatedData['status'])) {
                if ($validatedData['status'] === 'paid' && $originalStatus !== 'paid') {
                    $invoice->paid_date = $request->filled('paid_date') ? Carbon::parse($validatedData['paid_date']) : Carbon::now();
                } elseif ($validatedData['status'] !== 'paid') {
                    $invoice->paid_date = null;
                }
            } elseif ($request->filled('paid_date') && $invoice->status === 'paid') {
                // If status isn't changing but paid_date is provided and status is 'paid'
                $invoice->paid_date = Carbon::parse($validatedData['paid_date']);
            }


            // Recalculation of totals if items were editable would happen here.
            // Since items are not editable in this step, subtotal/total are not changed unless
            // currency changed AND we decided to re-evaluate (very complex, avoid for now).
            // For now, we assume totals are not affected by these edits.
            // If currency_code is changed, it could invalidate existing transactions. Extreme caution.

            $invoice->save();

            // Log activity or trigger events if needed, e.g., if status changed significantly
            if (isset($validatedData['status']) && $validatedData['status'] !== $originalStatus) {
                // Example: Log this change if you had a generic admin activity logger
                // AdminActivity::log('invoice_status_changed', $invoice, ['old' => $originalStatus, 'new' => $invoice->status]);
            }

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice->id)
                             ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Invoice update failed for invoice ID: {$invoice->id}", ['error' => $e->getMessage(), 'data' => $validatedData]);
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Failed to update invoice. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    /**
     * Activate services associated with a paid invoice.
     *
     * @param  Invoice  $invoice
     * @return RedirectResponse
     */
    public function activateServices(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice); // O una política más específica como 'activateServices'

        // Verificar que la factura esté en un estado que permita la activación de servicios
        if (!in_array($invoice->status, ['paid', 'pending_activation'])) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                             ->with('error', "Los servicios para esta factura no pueden activarse en su estado actual ('{$invoice->status}'). Se esperaba 'paid' o 'pending_activation'.");
        }

        $invoice->loadMissing(['items.clientService', 'items.product.productType', 'client']);

        $allServicesActivated = true;
        $servicesProcessedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($invoice->items as $item) {
                // Solo procesar ítems que tienen un producto asociado y cuyo tipo de producto crea una instancia de servicio
                if (!$item->product || !$item->product->productType || !$item->product->productType->creates_service_instance) {
                    continue;
                }

                if ($item->clientService) {
                    if ($item->clientService->status === 'pending') {
                        $item->clientService->status = 'Active';
                        // Opcional: Actualizar registration_date si es relevante en la activación
                        // $item->clientService->registration_date = Carbon::now();
                        $item->clientService->save();
                        $servicesProcessedCount++;
                        // Log o actividad específica de la activación del ClientService si es necesario
                    } elseif ($item->clientService->status === 'Active') {
                        // Ya está activo, no hacer nada o solo log.
                        $servicesProcessedCount++; // Contar como procesado si ya está activo.
                    } else {
                        // El servicio existe pero está en un estado inesperado (ej. cancelled, suspended)
                        Log::warning("ClientService ID {$item->clientService->id} para InvoiceItem ID {$item->id} está en estado '{$item->clientService->status}' y no se activará automáticamente.");
                        $allServicesActivated = false; // Marcar que no todos los servicios aplicables se activaron
                    }
                } else {
                    // No hay ClientService vinculado. Esto indica un problema en el flujo de pago,
                    // ya que ClientInvoiceController@payWithBalance debería haber creado el ClientService en estado 'pending'.
                    Log::error("No se encontró ClientService vinculado para InvoiceItem ID {$item->id} (Producto: {$item->product->name}) en Invoice ID {$invoice->id} durante la activación por admin.");
                    $allServicesActivated = false; // Marcar que no todos los servicios se pudieron activar/verificar
                }
            }

            if ($servicesProcessedCount > 0 && $allServicesActivated) {
                $invoice->status = 'active_service'; // Nuevo estado de Invoice
                $invoice->save();
                 // Log o actividad general de la factura
                $invoice->admin_notes = ($invoice->admin_notes ? $invoice->admin_notes . "\n" : '') . "Servicios activados por admin el " . Carbon::now() . ".";
                $invoice->save();

                DB::commit();
                return redirect()->route('admin.invoices.show', $invoice->id)
                                 ->with('success', 'Servicios activados exitosamente. El estado de la factura se ha actualizado.');
            } elseif ($servicesProcessedCount === 0 && $allServicesActivated) {
                // No había servicios que crear/activar (ej. factura manual sin items que creen servicios)
                // O todos los servicios ya estaban activos.
                // Podríamos cambiar el estado de la factura a 'completed' o 'active_service' si no hay nada más que hacer.
                // Por ahora, si no se procesó nada, no cambiamos estado y damos info.
                DB::commit(); // Cometer cualquier cambio menor (ej. notas si se añadieran)
                return redirect()->route('admin.invoices.show', $invoice->id)
                                 ->with('info', 'No había servicios pendientes de activación para esta factura o ya estaban activos.');
            } else {
                // No todos los servicios aplicables se pudieron activar o estaban en estado correcto.
                // No cambiamos el estado de la factura a 'active_service' para revisión manual.
                // Se podría usar un estado como 'activation_issue'. Por ahora, la mantenemos como 'paid' o 'pending_activation'.
                $invoice->admin_notes = ($invoice->admin_notes ? $invoice->admin_notes . "\n" : '') . "Intento de activación de servicios el " . Carbon::now() . ". Algunos servicios requieren atención.";
                $invoice->save();
                DB::commit();
                return redirect()->route('admin.invoices.show', $invoice->id)
                                 ->with('warning', 'Algunos servicios no pudieron ser activados o requieren atención. Revise los logs y los servicios del cliente.');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error activando servicios para Factura ID {$invoice->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.invoices.show', $invoice->id)
                             ->with('error', 'Ocurrió un error al activar los servicios: ' . $e->getMessage());
        }
    }

    /**
     * Registra una transacción manual para una factura, actualiza su estado y
     * provisiona los servicios asociados (ej. cuentas de hosting, dominios).
     * Esta funcionalidad es esencial en sistemas de gestión para proveedores de hosting
     * (análogos a WHMCS) para procesar pagos externos y automatizar la activación
     * de los servicios contratados por el cliente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice  La factura para la cual se registra el pago.
     * @return \Illuminate\Http\RedirectResponse Redirige a la vista de la factura con un mensaje.
     */
    public function storeManualTransaction(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice); // O una política más específica como 'managePayments'

        $validatedData = $request->validate([
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01', // Debería idealmente coincidir con el total de la factura
            'currency_code' => 'required|string|size:3', // Debería idealmente coincidir con la moneda de la factura
            'payment_gateway_name' => 'required|string|max:255', // Nombre proporcionado por el usuario para la pasarela manual
            'gateway_transaction_id' => 'nullable|string|max:255', // ID de referencia para el pago manual
            'description' => 'nullable|string|max:1000', // Notas para la transacción
        ]);

        // Consideración: Se podría añadir una validación para asegurar que amount y currency_code
        // coincidan con los de la factura ($invoice->total_amount, $invoice->currency_code).
        // Por ahora, se valida que vengan en el request.

        // Verificar que la factura esté en un estado apropiado para registrar un pago
        if (!in_array($invoice->status, ['unpaid', 'pending_confirmation', 'overdue'])) { // Added 'overdue'
            return back()->with('error', 'La factura no está en un estado válido para registrar un pago manual (actual: '.$invoice->status.'). Se esperaba: unpaid, pending_confirmation, overdue.');
        }

        // Protección adicional: si el monto validado no coincide con el total de la factura
        if ((float)$validatedData['amount'] !== (float)$invoice->total_amount) {
            // Esto podría ser un warning o requerir un permiso especial si se permite pago parcial.
            // Por ahora, para simplificar, se espera que el monto coincida con el total.
            // Si se permiten pagos parciales, esta lógica y la actualización del estado de la factura necesitarían ajustes.
            Log::warning("Intento de registrar pago manual en factura {$invoice->id} con monto diferente al total de la factura.", [
                'invoice_amount' => $invoice->total_amount,
                'payment_amount' => $validatedData['amount']
            ]);
            // Decide si quieres detenerte aquí o permitirlo. Por ahora, lo permitiremos pero se loguea.
            // return back()->with('error', 'El monto del pago debe coincidir con el total de la factura.');
        }
        if ($validatedData['currency_code'] !== $invoice->currency_code) {
            return back()->with('error', 'La moneda del pago debe coincidir con la de la factura.');
        }


        DB::beginTransaction();
        try {
            // Crear el registro de la transacción
            $transaction = $invoice->transactions()->create([
                'client_id' => $invoice->client_id,
                'reseller_id' => $invoice->reseller_id, // Added reseller_id
                'payment_method_id' => null, // Opcional: buscar/crear uno para 'manual' o permitir seleccionar uno existente
                'gateway_slug' => 'manual', // Slug genérico para pagos manuales, podría ser el payment_gateway_name normalizado
                'gateway_transaction_id' => $validatedData['gateway_transaction_id'],
                'type' => 'payment',
                'amount' => $validatedData['amount'],
                'currency_code' => $validatedData['currency_code'],
                'status' => 'completed', // Asumimos que si el admin lo registra, está completado
                'description' => $validatedData['description'] ?? 'Pago manual para factura ' . $invoice->invoice_number . '. Método: ' . $validatedData['payment_gateway_name'],
                'transaction_date' => Carbon::parse($validatedData['transaction_date']),
                'fees_amount' => 0, // Opcional, si aplica
                'is_manual_payment' => true, // Indicador de que fue un pago manual
            ]);

            // Actualizar el estado de la factura a pagada
            $invoice->status = 'paid'; // O 'pending_activation' si los servicios deben activarse después
            $invoice->paid_date = Carbon::parse($validatedData['transaction_date']); // O now() si se prefiere la fecha de registro del pago

            // Añadir nota administrativa a la factura
            $adminNote = "Pago manual registrado por admin. Método: {$validatedData['payment_gateway_name']}.";
            if (!empty($validatedData['gateway_transaction_id'])) {
                $adminNote .= " ID Transacción: {$validatedData['gateway_transaction_id']}.";
            }
            $invoice->admin_notes = trim(($invoice->admin_notes ? $invoice->admin_notes . "\n" : '') . $adminNote);

            $invoice->save();

            // Comentario en español: Cargar relaciones necesarias para crear servicios
            // Asegurarse de que items, product, product.productType, productPricing, y productPricing.billingCycle están cargados.
            // También clientService en el item para verificar si ya existe.
            $invoice->loadMissing([
                'items.product.productType', // Para verificar si el producto debe crear un servicio
                'items.productPricing.billingCycle', // Para calcular next_due_date y obtener billing_amount
                'items.clientService' // Para verificar si ya existe un servicio asociado al item
            ]);

            // Comentario en español: Iterar sobre los ítems de la factura para crear servicios si aplica
            foreach ($invoice->items as $invoiceItem) { // Renombrado $item a $invoiceItem para claridad
                // Comentario en español: Condición para crear un nuevo servicio:
                // 1. El InvoiceItem tiene un producto asociado.
                // 2. El InvoiceItem NO está ya asociado a un ClientService.
                // 3. El InvoiceItem tiene un ProductPricing asociado (de donde se saca el ciclo y precio recurrente).
                // 4. El ProductPricing tiene un BillingCycle asociado.
                // 5. El Producto asociado es de un tipo que debe generar una instancia de servicio.
                if (
                    $invoiceItem->product_id &&
                    !$invoiceItem->clientService && // Chequea si la relación cargada es null
                    $invoiceItem->productPricing &&
                    $invoiceItem->productPricing->billingCycle &&
                    $invoiceItem->product && // Asegurar que $invoiceItem->product no es null
                    $invoiceItem->product->productType &&
                    $invoiceItem->product->productType->creates_service_instance
                ) {
                    // Comentario en español: Obtener datos para el nuevo servicio del cliente
                    $productPricing = $invoiceItem->productPricing;
                    $billingCycle = $productPricing->billingCycle;

                    $registrationDate = Carbon::parse($validatedData['transaction_date']); // Usar la fecha validada de la transacción

                    // Comentario en español: Calcular la próxima fecha de vencimiento basado en el ciclo de facturación (días)
                    $nextDueDate = $registrationDate->copy()->addDays($billingCycle->days);

                    // Comentario en español: Determinar el monto de facturación recurrente
                    $recurringAmount = $productPricing->price; // Precio recurrente desde ProductPricing

                    // Comentario en español: Crear el servicio del cliente
                    $clientService = new ClientService([
                        'client_id' => $invoice->client_id,
                        'reseller_id' => $invoice->reseller_id,
                        'product_id' => $invoiceItem->product_id,
                        'product_pricing_id' => $productPricing->id,
                        'billing_cycle_id' => $billingCycle->id,
                        'domain_name' => $invoiceItem->domain_name, // Puede ser null, tomado del InvoiceItem
                        'status' => 'pending_provisioning', // Estado inicial para servicios que requieren aprovisionamiento
                        'registration_date' => $registrationDate->toDateString(),
                        'next_due_date' => $nextDueDate->toDateString(),
                        'billing_amount' => $recurringAmount,
                        'notes' => 'Servicio creado automáticamente desde factura ' . $invoice->invoice_number . ' (pago manual).',
                        // 'server_id' => null, // Si aplica y se puede determinar
                        // 'username' => null, // Si aplica y se puede determinar/generar
                        // 'password' => null, // Si aplica y se debe generar (¡encriptar!)
                    ]);
                    $clientService->save();

                    // Comentario en español: Actualizar el invoice_item con el ID del servicio creado
                    $invoiceItem->client_service_id = $clientService->id;
                    $invoiceItem->save();

                    Log::info("ClientService ID {$clientService->id} creado para InvoiceItem ID {$invoiceItem->id} de Factura ID {$invoice->id}.");
                }
            }

            // Aquí podrías disparar un evento, como InvoicePaidEvent($invoice), si tienes listeners para ello (ej. activar servicios)
            // O un evento específico para ClientServiceCreated si el aprovisionamiento es manejado por listeners.

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Transacción manual registrada, factura actualizada y servicios (si aplican) creados exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar transacción manual para factura ID {$invoice->id}: " . $e->getMessage(), [
                'exception' => $e,
                'data' => $validatedData
            ]);
            return back()->withInput()->with('error', 'Error al registrar la transacción manual: ' . $e->getMessage());
        }
    }
}
