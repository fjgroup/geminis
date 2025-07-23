<?php

namespace App\Domains\Orders\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Orders\Infrastructure\Http\Requests\UpdateOrderRequest;
use App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controlador Admin para gestión de órdenes
 * 
 * Aplica arquitectura hexagonal - ubicado en Infrastructure layer
 * Maneja HTTP requests/responses para administración de órdenes
 */
class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request): InertiaResponse
    {
        // TODO: Implementar autorización cuando exista OrderPolicy
        // $this->authorize('viewAny', Order::class);

        $filters = $request->only(['status', 'client_id', 'date_from', 'date_to', 'search']);

        // Por ahora usamos OrderConfigurableOption como base hasta tener modelo Order
        $query = OrderConfigurableOption::with(['product'])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($filters['client_id'] ?? null, function ($query, $clientId) {
                $query->where('client_email', function ($subQuery) use ($clientId) {
                    $subQuery->select('email')
                        ->from('users')
                        ->where('id', $clientId)
                        ->limit(1);
                });
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_email', 'like', "%{$search}%")
                      ->orWhere('order_id', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($productQuery) use ($search) {
                          $productQuery->where('name', 'like', "%{$search}%");
                      });
                });
            });

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'client_email' => $order->client_email,
                    'product_name' => $order->product->name ?? 'Producto no encontrado',
                    'total_price' => $order->total_price,
                    'currency_code' => $order->currency_code,
                    'is_active' => $order->is_active,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        // Obtener clientes para filtros
        $clients = User::where('role', 'client')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(int $orderId): InertiaResponse
    {
        // TODO: Implementar autorización cuando exista OrderPolicy
        // $this->authorize('view', $order);

        // Obtener todos los items de la orden
        $orderItems = OrderConfigurableOption::with(['product', 'configurableOption'])
            ->where('order_id', $orderId)
            ->get();

        if ($orderItems->isEmpty()) {
            abort(404, 'Orden no encontrada');
        }

        // Agrupar información de la orden
        $firstItem = $orderItems->first();
        $orderData = [
            'id' => $orderId,
            'order_id' => $firstItem->order_id,
            'client_email' => $firstItem->client_email,
            'currency_code' => $firstItem->currency_code,
            'created_at' => $firstItem->created_at,
            'updated_at' => $firstItem->updated_at,
            'total_amount' => $orderItems->sum('total_price'),
            'items' => $orderItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? 'Producto no encontrado',
                    'option_name' => $item->option_name,
                    'option_value' => $item->option_value,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'is_active' => $item->is_active,
                ];
            }),
        ];

        return Inertia::render('Admin/Orders/Show', [
            'order' => $orderData,
        ]);
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(int $orderId): InertiaResponse
    {
        // TODO: Implementar autorización cuando exista OrderPolicy
        // $this->authorize('update', $order);

        $orderItems = OrderConfigurableOption::with(['product'])
            ->where('order_id', $orderId)
            ->get();

        if ($orderItems->isEmpty()) {
            abort(404, 'Orden no encontrada');
        }

        $firstItem = $orderItems->first();
        $orderData = [
            'id' => $orderId,
            'order_id' => $firstItem->order_id,
            'client_email' => $firstItem->client_email,
            'currency_code' => $firstItem->currency_code,
            'is_active' => $firstItem->is_active,
            'created_at' => $firstItem->created_at,
            'updated_at' => $firstItem->updated_at,
        ];

        return Inertia::render('Admin/Orders/Edit', [
            'order' => $orderData,
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, int $orderId): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Actualizar todos los items de la orden
            $updated = OrderConfigurableOption::where('order_id', $orderId)
                ->update([
                    'is_active' => $validated['status'] === 'active',
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return redirect()->back()->withErrors(['error' => 'Orden no encontrada']);
            }

            Log::info('Orden actualizada por admin', [
                'order_id' => $orderId,
                'admin_id' => auth()->id(),
                'changes' => $validated,
            ]);

            return redirect()->route('admin.orders.show', $orderId)
                ->with('success', 'Orden actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error actualizando orden', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al actualizar la orden']);
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(int $orderId): RedirectResponse
    {
        try {
            // TODO: Implementar autorización cuando exista OrderPolicy
            // $this->authorize('delete', $order);

            $deleted = OrderConfigurableOption::where('order_id', $orderId)->delete();

            if ($deleted === 0) {
                return redirect()->back()->withErrors(['error' => 'Orden no encontrada']);
            }

            Log::info('Orden eliminada por admin', [
                'order_id' => $orderId,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.orders.index')
                ->with('success', 'Orden eliminada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error eliminando orden', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al eliminar la orden']);
        }
    }

    /**
     * Get order statistics for dashboard
     */
    public function getOrderStats(): array
    {
        try {
            $totalOrders = OrderConfigurableOption::distinct('order_id')->count();
            $activeOrders = OrderConfigurableOption::where('is_active', true)
                ->distinct('order_id')->count();
            $totalRevenue = OrderConfigurableOption::sum('total_price');
            $recentOrders = OrderConfigurableOption::where('created_at', '>=', now()->subDays(7))
                ->distinct('order_id')->count();

            return [
                'total_orders' => $totalOrders,
                'active_orders' => $activeOrders,
                'total_revenue' => $totalRevenue,
                'recent_orders' => $recentOrders,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de órdenes', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_orders' => 0,
                'active_orders' => 0,
                'total_revenue' => 0,
                'recent_orders' => 0,
            ];
        }
    }
}
