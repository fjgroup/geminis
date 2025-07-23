<?php

namespace App\Domains\Users\Infrastructure\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
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

        // Contar facturas que están pagadas pero sus servicios están pendientes de activación/confirmación
        $pendingServicesInvoicesCount = $user->invoices()
                                             ->whereIn('status', ['pending_activation', 'pending_confirmation'])
                                             ->count();

        $unpaidInvoicesCount = $user->invoices()
                                    ->where('status', 'unpaid')
                                    ->count();

        return Inertia::render('Client/ClientDashboard', [
            'clientServices' => $clientServices,
            'pendingServicesInvoicesCount' => $pendingServicesInvoicesCount, // Nombre de variable actualizado
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

        $products = Product::where('status', 'active') // Assuming an 'status' column for active products
            ->with(['pricings.billingCycle']) // Corrected relationship name to match model
            ->paginate(10); // Paginate results

        return Inertia::render('Client/Products/Index', [
            'products' => $products,
        ]);
    }
}
