<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductPricing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function searchClients(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $clients = User::where('role', 'client')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->select('id', 'name', 'email') // Selecciona solo lo necesario
            ->take(20) // Limita el número de resultados
            ->get()
            ->map(fn($user) => ['value' => $user->id, 'label' => "{$user->name} ({$user->email})"]);

        return response()->json($clients);
    }

    public function searchResellers(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $resellers = User::where('role', 'reseller')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('company_name', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->select('id', 'name', 'company_name')
            ->take(20)
            ->get()
            ->map(fn($user) => ['value' => $user->id, 'label' => $user->name . ($user->company_name ? " ({$user->company_name})" : '')]);

        return response()->json($resellers);
    }

    public function getProductPricings(Product $product): JsonResponse
    {
        $pricings = $product->pricings()
            ->where('is_active', true) // O la lógica que necesites
            ->orderBy('price') // O el orden que prefieras
            ->get(['id', 'billing_cycle', 'price', 'currency_code']);

        $mappedPricings = $pricings->map(fn($pricing) => [
            'value' => $pricing->id, // El ID del pricing, para el valor del select
            'label' => "{$pricing->billing_cycle} - {$pricing->price} {$pricing->currency_code}", // Para mostrar en el select
            'price' => $pricing->price, // El monto numérico
            'billing_cycle_name' => $pricing->billing_cycle, // El nombre crudo del ciclo (ej: 'monthly', 'semi_annually')
            'currency_code' => $pricing->currency_code
        ]);

        return response()->json($mappedPricings);
    }
}
