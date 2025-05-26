<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importar Auth


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);
        $user = Auth::user();
        $query = Order::with(['client', 'items']);

        if ($user->hasRole('reseller')) {
            // Si es un revendedor, solo mostrar órdenes de sus clientes
            $query->whereHas('client', function ($q) use ($user) {
                $q->where('reseller_id', $user->id);
            });
        }
        // Para admin, no se aplica filtro adicional, verá todas.
        // Para client, esta ruta de admin no debería ser accedida o la política viewAny lo manejaría,
        // pero si accediera, el query no filtraría específicamente para él aquí.
        $orders = $query->latest('order_date')->paginate(10);

        return inertia('Admin/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['client', 'items', 'items.product', 'items.productPricing']);

        return inertia('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
