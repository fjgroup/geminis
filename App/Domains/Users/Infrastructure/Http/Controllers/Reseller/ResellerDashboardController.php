<?php

namespace App\Domains\Users\Infrastructure\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Users\Models\User; // For fetching clients
use App\Domains\ClientServices\Models\ClientService; // For fetching service stats
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ResellerDashboardController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $reseller = Auth::user();

        // Fetch the reseller's clients
        // Using the existing relationship on the User model for a reseller to get their clients
        $clients = $reseller->clients() // Assuming 'clients' is the HasMany relationship from User to User
                            ->orderBy('created_at', 'desc')
                            ->get();
                        // Consider pagination: ->paginate(10);

        // Fetch count of active services under these clients
        $clientIds = $clients->pluck('id');
        $activeServicesCount = ClientService::whereIn('client_id', $clientIds)
                                            ->where('status', 'Active')
                                            ->count();

        return Inertia::render('Reseller/ResellerDashboard', [
            'clients' => $clients->map(function ($client) { // Select only necessary client data
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'created_at' => $client->created_at,
                    // Add other fields if needed for the client list on dashboard
                ];
            }),
            'clientCount' => $clients->count(),
            'activeServicesCount' => $activeServicesCount,
        ]);
    }
}
