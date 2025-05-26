<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = $request->user()->invoices()->with('items')->paginate(10); // Asumiendo una relación 'invoices' en el modelo User

        return Inertia::render('Client/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'client',
            'reseller', // Cargar reseller si es necesario mostrarlo
            'items',
            'items.orderItem',
            'items.clientService',
        ]);

        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }
}
