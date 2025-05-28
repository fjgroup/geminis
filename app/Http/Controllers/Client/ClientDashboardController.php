<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $clientServices = $user->clientServices()
                                ->with(['product', 'productPricing', 'billingCycle'])
                                ->get();
        
        $pendingOrdersCount = $user->orders()
                                   ->whereIn('status', ['paid_pending_execution', 'pending_provisioning'])
                                   ->count();
        
        $unpaidInvoicesCount = $user->invoices()
                                    ->where('status', 'unpaid')
                                    ->count();

        return Inertia::render('Client/ClientDashboard', [
            'clientServices' => $clientServices,
            'pendingOrdersCount' => $pendingOrdersCount,
            'unpaidInvoicesCount' => $unpaidInvoicesCount,
            'accountBalance' => $user->balance,
            'formattedAccountBalance' => $user->formatted_balance,
        ]);
    }

    /**
     * Display a listing of available products for clients.
     *
     * @param  Request  $request
     * @return \Inertia\Response
     */
    public function listProducts(Request $request)
    {
        // Authorization: Ensure user is authenticated.
        // Specific policy for viewing products can be added if needed.
        // For now, any authenticated client can view products.
        // $this->authorize('viewAny', Product::class); // Example if ProductPolicy exists

        $products = \App\Models\Product::where('status', 'active') // Assuming an 'status' column for active products
            ->with(['productPricings.billingCycle'])
            ->paginate(10); // Paginate results

        return Inertia::render('Client/Products/Index', [
            'products' => $products,
        ]);
    }
}
