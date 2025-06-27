<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NameSiloService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DomainApiController extends Controller
{
    protected NameSiloService $nameSiloService;

    public function __construct(NameSiloService $nameSiloService)
    {
        $this->nameSiloService = $nameSiloService;
    }

    /**
     * Check domain availability.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'domain' => 'required|string|max:255', // Podría añadirse una regex para validar mejor el formato de dominio
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Nombre de dominio no válido o no proporcionado.', 'errors' => $validator->errors()], 422);
        }

        $domainName = $validator->validated()['domain'];
        $serviceResponse = $this->nameSiloService->checkDomainAvailability($domainName);

        // Mapear la respuesta del servicio a la estructura que el frontend espera
        $frontendData = [
            'available' => ($serviceResponse['status'] === 'available'),
            'domain_name' => $serviceResponse['domain_name'],
            'is_new' => ($serviceResponse['status'] === 'available'), // Asumir 'is_new' si está disponible para registro
            'price' => $serviceResponse['price'] ?? null, // Precio de NameSilo
            'is_premium' => $serviceResponse['is_premium'] ?? false,
            'message' => $serviceResponse['message'],
            'status_from_provider' => $serviceResponse['status'], // Para depuración o lógica avanzada en frontend
        ];

        if ($serviceResponse['status'] === 'error') {
            return response()->json(['status' => 'error', 'message' => $serviceResponse['message'], 'data' => $frontendData], 500);
        }

        return response()->json(['status' => 'success', 'data' => $frontendData]);
    }

    /**
     * Get TLD pricing information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTldPricingInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tlds' => 'sometimes|array',
            'tlds.*' => 'string|max:10', // ej. "com", "net"
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Lista de TLDs no válida.', 'errors' => $validator->errors()], 422);
        }

        $requestedTlds = $validator->validated()['tlds'] ?? []; // TLDs específicos solicitados por el frontend

        // 1. Obtener precios de NameSilo (costo/referencia)
        // Si $requestedTlds está vacío, NameSiloService->getTldPricingInfo podría devolver todos o un conjunto por defecto.
        $nameSiloPrices = $this->nameSiloService->getTldPricingInfo($requestedTlds);

        // 2. Obtener Productos Internos de Dominio (product_type_id = 3)
        $internalDomainProductsRaw = \App\Models\Product::where('product_type_id', 3) // 3 = Registro de Dominio
            ->where('status', 'active')
            ->with(['pricings.billingCycle', 'productType']) // productType para el slug si se usa
            ->get();

        // Asumimos que Product->name es el TLD sin el punto (ej. "com", "net")
        // O si hay un campo `tld` dedicado, usarlo: ->keyBy('tld')
        $internalDomainProducts = $internalDomainProductsRaw->keyBy(function ($product) {
            return strtolower(str_replace('.', '', $product->name)); // Normalizar a minúsculas y sin punto
        });

        $responseTlds = [];

        // 3. Mapear y Combinar Datos
        // Iterar sobre los productos internos para asegurar que solo ofrecemos TLDs que tenemos configurados.
        foreach ($internalDomainProducts as $tldKey => $internalProduct) {
            $nameSiloTldInfo = $nameSiloPrices[$tldKey] ?? null;

            $formattedPricings = $internalProduct->pricings->map(function ($pricing) {
                return [
                    'id' => $pricing->id,
                    'term' => $pricing->billingCycle->name, // ej. "Anual", "2 Años"
                    'years' => $pricing->billingCycle->period_in_years ?? $pricing->billingCycle->period_in_months / 12 ?? 1, // Calcular años
                    'price' => (float) $pricing->price,
                    'currency_code' => $pricing->currency_code,
                    'setup_fee' => (float) ($pricing->setup_fee ?? 0),
                    // Añadir más detalles del billing_cycle si es necesario
                ];
            })->sortBy('years')->values()->all(); // Ordenar por duración y reindexar

            if(empty($formattedPricings)) {
                // Si un producto de dominio interno no tiene precios de venta, podemos omitirlo o marcarlo.
                // Por ahora lo omitimos.
                continue;
            }

            $responseTlds[] = [
                'tld' => $tldKey,
                'internal_product_id' => $internalProduct->id,
                'internal_product_slug' => $internalProduct->slug, // Para construir URLs si es necesario
                'name_silo_info' => $nameSiloTldInfo, // Puede ser null si no hay info de NameSilo para este TLD
                'pricings' => $formattedPricings, // Nuestros precios de venta
            ];
        }

        // Si $requestedTlds no estaba vacío, podríamos querer filtrar $responseTlds para devolver solo esos.
        // Sin embargo, al iterar sobre $internalDomainProducts, ya estamos limitados a lo que ofrecemos.
        // Si un TLD solicitado no es un producto interno, no aparecerá.
        if (!empty($requestedTlds)) {
            $responseTlds = array_filter($responseTlds, function ($tldData) use ($requestedTlds) {
                return in_array($tldData['tld'], $requestedTlds);
            });
            $responseTlds = array_values($responseTlds); // Reindexar array
        }


        return response()->json(['status' => 'success', 'data' => $responseTlds]);
    }
}
