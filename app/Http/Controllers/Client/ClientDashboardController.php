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
        $clientServices = $request->user()
                                    ->clientServices()
                                    ->with(['product', 'prices'])
                                    ->get();

        return Inertia::render('Client/ClientDashboard', [
            'clientServices' => $clientServices,
        ]);
    }
}
