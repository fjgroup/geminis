<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Products\Models\Product;
use App\Domains\Shared\Services\SearchService;
use App\Domains\Shared\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Buscar clientes
     */
    public function searchClients(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $clients = $this->searchService->searchUsers($searchTerm, 'client', 20);

        return $this->searchResponse($clients);
    }

    /**
     * Buscar revendedores
     */
    public function searchResellers(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $resellers = $this->searchService->searchUsers($searchTerm, 'reseller', 20);

        return $this->searchResponse($resellers);
    }

    /**
     * Obtener precios de un producto
     */
    public function getProductPricings(Product $product): JsonResponse
    {
        try {
            $pricings = $product->pricings()
                ->where('is_active', true)
                ->orderBy('price')
                ->get(['id', 'billing_cycle', 'price', 'currency_code']);

            $mappedPricings = $pricings->map(fn($pricing) => [
                'value' => $pricing->id,
                'label' => "{$pricing->billing_cycle} - {$pricing->price} {$pricing->currency_code}",
                'price' => $pricing->price,
                'billing_cycle_name' => $pricing->billing_cycle,
                'currency_code' => $pricing->currency_code
            ]);

            return $this->successResponse($mappedPricings);

        } catch (\Exception $e) {
            Log::error('Error al obtener precios del producto', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return $this->serverErrorResponse('Error al obtener precios del producto');
        }
    }
}
