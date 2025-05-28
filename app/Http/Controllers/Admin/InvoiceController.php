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
// use Illuminate\Support\Carbon; // Already imported
// use Illuminate\Support\Facades\DB; // Already imported

class InvoiceController extends Controller
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

        $invoice->load(['client', 'reseller', 'items.orderItem', 'items.clientService']); // Cargar relaciones

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
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
}
