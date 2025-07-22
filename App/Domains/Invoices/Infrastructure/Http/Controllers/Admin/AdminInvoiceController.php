<?php

namespace App\Domains\Invoices\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Domains\Invoices\Models\Invoice;
use App\Domains\Invoices\Services\InvoiceManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador de facturas para administradores en arquitectura hexagonal
 *
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a InvoiceManagementService
 * - Ubicado en Infrastructure layer como Input Adapter
 */
class AdminInvoiceController extends Controller
{
    public function __construct(
        private InvoiceManagementService $invoiceManagementService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = $request->only(['status', 'client_id', 'date_from', 'date_to']);
        
        $invoicesResult = $this->invoiceManagementService->getInvoices($filters, 10);
        $pendingFundAdditions = $this->invoiceManagementService->getPendingFundAdditions();

        if (!$invoicesResult['success']) {
            Log::error('Error obteniendo facturas en AdminInvoiceController', [
                'filters' => $filters,
                'error' => $invoicesResult['message']
            ]);
            
            // En caso de error, mostrar página vacía
            $invoicesResult['data'] = collect()->paginate(10);
        }

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoicesResult['data'],
            'pendingFundAdditions' => $pendingFundAdditions,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', Invoice::class);

        $formDataResult = $this->invoiceManagementService->getFormData();

        if (!$formDataResult['success']) {
            Log::error('Error obteniendo datos del formulario de factura', [
                'error' => $formDataResult['message']
            ]);
            
            // Datos por defecto en caso de error
            $formDataResult['data'] = [
                'clients' => collect(),
                'possibleStatuses' => ['unpaid', 'paid', 'cancelled'],
                'defaultCurrency' => 'USD',
                'currencies' => ['USD', 'EUR', 'GBP']
            ];
        }

        return Inertia::render('Admin/Invoices/Create', $formDataResult['data']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManualInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $result = $this->invoiceManagementService->createManualInvoice($request->validated());

        if ($result['success']) {
            return redirect()->route('admin.invoices.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): InertiaResponse
    {
        $this->authorize('view', $invoice);

        $result = $this->invoiceManagementService->getInvoiceShowData($invoice);

        if (!$result['success']) {
            Log::error('Error obteniendo datos de factura para mostrar', [
                'invoice_id' => $invoice->id,
                'error' => $result['message']
            ]);
            
            // Datos mínimos en caso de error
            $result['data'] = [
                'invoice' => $invoice,
                'showManualPaymentForm' => false,
                'manualPaymentFormData' => []
            ];
        }

        return Inertia::render('Admin/Invoices/Show', $result['data']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): InertiaResponse
    {
        $this->authorize('update', $invoice);

        $invoice->load('client:id,name,email');

        $formDataResult = $this->invoiceManagementService->getFormData();
        $formData = $formDataResult['success'] ? $formDataResult['data'] : [
            'clients' => collect(),
            'possibleStatuses' => ['unpaid', 'paid', 'cancelled'],
            'currencies' => ['USD', 'EUR', 'GBP']
        ];

        return Inertia::render('Admin/Invoices/Edit', [
            'invoice' => $invoice,
            ...$formData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $result = $this->invoiceManagementService->updateInvoice($invoice, $request->validated());

        if ($result['success']) {
            return redirect()->route('admin.invoices.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $result = $this->invoiceManagementService->deleteInvoice($invoice);

        if ($result['success']) {
            return redirect()->route('admin.invoices.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Registrar transacción manual para una factura
     */
    public function storeManualTransaction(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'required|string|max:100|unique:transactions,gateway_transaction_id',
        ]);

        $result = $this->invoiceManagementService->storeManualTransaction($invoice, $validated);

        if ($result['success']) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Activar servicios asociados a una factura
     */
    public function activateServices(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $result = $this->invoiceManagementService->activateInvoiceServices($invoice);

        if ($result['success']) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }
}
