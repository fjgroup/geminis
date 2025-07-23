<?php
namespace App\Domains\Products\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Products\Application\Services\ProductPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador API para cálculo de precios en arquitectura hexagonal
 *
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Usa ProductPricingService de la capa de aplicación
 */
class PricingController extends Controller
{
    public function __construct(
        private ProductPricingService $pricingService
    ) {}

    /**
     * Calcular precio de un producto individual
     */
    public function calculateProductPrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id'             => 'required|integer|exists:products,id',
            'billing_cycle_id'       => 'required|integer|exists:billing_cycles,id',
            'configurable_options'   => 'nullable|array',
            'configurable_options.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $calculation = $this->pricingService->calculateProductPrice(
                $request->product_id,
                $request->billing_cycle_id,
                $request->configurable_options ?? []
            );

            return response()->json([
                'success' => true,
                'data'    => $calculation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular el precio',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcular precio total del carrito
     */
    public function calculateCartTotal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items'                          => 'required|array|min:1',
            'items.*.product_id'             => 'required|integer|exists:products,id',
            'items.*.billing_cycle_id'       => 'required|integer|exists:billing_cycles,id',
            'items.*.configurable_options'   => 'nullable|array',
            'items.*.configurable_options.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos del carrito inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // TODO: Implementar calculateCartTotal en ProductPricingService
            throw new \Exception('Cart total calculation not implemented yet');

            return response()->json([
                'success' => true,
                'data'    => $calculation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular el total del carrito',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcular precio para usuarios públicos (no logueados)
     */
    public function calculatePublicPrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id'             => 'required|integer|exists:products,id',
            'billing_cycle_id'       => 'required|integer|exists:billing_cycles,id',
            'configurable_options'   => 'nullable|array',
            'configurable_options.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $calculation = $this->pricingService->calculateProductPrice(
                $request->product_id,
                $request->billing_cycle_id,
                $request->configurable_options ?? []
            );

            return response()->json([
                'success' => true,
                'data'    => $calculation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular el precio',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener precio mensual simple de un producto para el admin
     */
    public function getAdminProductPrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID de producto inválido',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $productId = $request->product_id;

            // Obtener solo el precio mensual del producto (billing_cycle_id = 1)
            $pricing = \App\Models\ProductPricing::where('product_id', $productId)
                ->where('billing_cycle_id', 1) // Solo mensual
                ->first();

            if (! $pricing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Precio mensual no encontrado para este producto',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'product_id'      => $productId,
                    'monthly_price'   => (float) $pricing->price,
                    'setup_fee'       => (float) $pricing->setup_fee,
                    'currency_code'   => $pricing->currency_code,
                    'formatted_price' => number_format($pricing->price, 2) . ' ' . $pricing->currency_code,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el precio del producto',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener recursos base de un producto
     */
    public function getProductBaseResources(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID de producto inválido',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // TODO: Implementar getProductBaseResources en ProductPricingService
            throw new \Exception('Product base resources not implemented yet');

            return response()->json([
                'success' => true,
                'data'    => [
                    'product_id'     => $request->product_id,
                    'base_resources' => $baseResources,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recursos base',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información de precios para múltiples productos y ciclos
     */
    public function getBulkPricing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products'                       => 'required|array|min:1',
            'products.*.product_id'          => 'required|integer|exists:products,id',
            'products.*.billing_cycle_ids'   => 'required|array|min:1',
            'products.*.billing_cycle_ids.*' => 'integer|exists:billing_cycles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de productos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $results = [];

            foreach ($request->products as $productData) {
                $productResults = [];

                foreach ($productData['billing_cycle_ids'] as $billingCycleId) {
                    $calculation = $this->pricingService->calculateProductPrice(
                        $productData['product_id'],
                        $billingCycleId,
                        []// Sin opciones configurables para precios base
                    );

                    $productResults[] = $calculation;
                }

                $results[] = [
                    'product_id'   => $productData['product_id'],
                    'pricing_data' => $productResults,
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener precios masivos',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
