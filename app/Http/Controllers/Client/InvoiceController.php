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
    public function show(Invoice $invoice) // Removed Request $request
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'client', // Already loaded by policy check if using $invoice->client_id for auth
            'reseller', 
            'items',
            'items.orderItem.product', // Example: load product through orderItem
            'items.clientService', // If applicable
            'order' // Load the associated order if it exists
        ]);
        
        // Get authenticated user and explicitly include balance and formatted_balance
        $authUser = Auth::user();
        $userResource = null;
        if ($authUser) {
            $userResource = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'balance' => $authUser->balance, // Assuming 'balance' is a direct attribute or casted
                'formatted_balance' => $authUser->formatted_balance, // Accessor
            ];
        }


        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice,
            'auth' => ['user' => $userResource] // Pass necessary auth user details
        ]);
    }
}
