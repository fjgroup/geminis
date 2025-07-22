<?php

namespace App\Domains\Products\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Products\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * API Controller para productos en arquitectura hexagonal
 * Input Adapter para requests de API REST
 */
class ProductApiController extends Controller
{
    // Tipos de producto hardcodeados por ahora para claridad
    private const PRODUCT_TYPE_HOSTING = 1;
    private const PRODUCT_TYPE_VPS = 2;
    private const PRODUCT_TYPE_EMAIL = 7;
    private const PRODUCT_TYPE_SSL = 4;
    private const PRODUCT_TYPE_LICENSE = 6;
    private const PRODUCT_TYPE_DOMAIN = 3;

    private function getProductsByTypes(array $typeIds): JsonResponse
    {
        $products = Product::whereIn('product_type_id', $typeIds)
            ->where('status', 'active') // Asumiendo que 'active' es el status para productos visibles/comprables
            ->with(['pricings.billingCycle', 'productType', /*'configurableOptionGroups.options.pricings'*/]) // configurableOptionGroups se puede añadir si se usa extensivamente
            ->orderBy('display_order', 'asc') // Opcional: ordenar
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($products);
    }

    public function getMainServices(): JsonResponse
    {
        return $this->getProductsByTypes([
            self::PRODUCT_TYPE_HOSTING,
            self::PRODUCT_TYPE_VPS,
            self::PRODUCT_TYPE_EMAIL,
        ]);
    }

    public function getSslCertificates(): JsonResponse
    {
        return $this->getProductsByTypes([self::PRODUCT_TYPE_SSL]);
    }

    public function getSoftwareLicenses(): JsonResponse
    {
        return $this->getProductsByTypes([self::PRODUCT_TYPE_LICENSE]);
    }

    public function getDomainRegistrationProducts(): JsonResponse
    {
        return $this->getProductsByTypes([self::PRODUCT_TYPE_DOMAIN]);
    }

    // Método genérico si se prefiere usar un solo endpoint con ID o slug
    // public function getProductsByType(Request $request, $typeIdentifier): JsonResponse
    // {
    //     $productTypeId = null;
    //     if (is_numeric($typeIdentifier)) {
    //         $productTypeId = $typeIdentifier;
    //     } else {
    //         $productType = ProductType::where('slug', $typeIdentifier)->first();
    //         if ($productType) {
    //             $productTypeId = $productType->id;
    //         } else {
    //             return response()->json(['error' => 'Product type not found'], 404);
    //         }
    //     }

    //     $products = Product::where('product_type_id', $productTypeId)
    //         ->where('status', 'active')
    //         ->with(['pricings.billingCycle', 'productType'])
    //         ->orderBy('name', 'asc')
    //         ->get();

    //     return response()->json($products);
    // }
}
