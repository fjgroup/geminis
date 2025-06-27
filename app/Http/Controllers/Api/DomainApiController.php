<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NameSiloService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Para logging si es necesario

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
            'domain' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Nombre de dominio no válido o no proporcionado.', 'errors' => $validator->errors()], 422);
        }

        $domainName = $validator->validated()['domain'];
        $serviceResponse = $this->nameSiloService->checkDomainAvailability($domainName);

        $frontendData = [
            'available' => ($serviceResponse['status'] === 'available'),
            'domain_name' => $serviceResponse['domain_name'],
            'is_new' => ($serviceResponse['status'] === 'available'),
            'price' => $serviceResponse['price'] ?? null,
            'is_premium' => $serviceResponse['is_premium'] ?? false,
            'message' => $serviceResponse['message'],
            'status_from_provider' => $serviceResponse['status'],
        ];

        if ($serviceResponse['status'] === 'error') {
            // El servicio ya debería haber logueado el error con más detalle.
            // Devolvemos el mensaje del servicio al frontend.
            return response()->json(['status' => 'error', 'message' => $serviceResponse['message'], 'data' => $frontendData], 500);
        }

        return response()->json(['status' => 'success', 'data' => $frontendData]);
    }

    /**
     * Get TLD pricing information directly from NameSilo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTldPricingInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tlds' => 'sometimes|array', // 'tlds' es opcional
            'tlds.*' => 'string|max:10|alpha_num', // Cada TLD como string, ej. "com", "net" (alpha_num para evitar puntos)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Lista de TLDs no válida.', 'errors' => $validator->errors()], 422);
        }

        $requestedTlds = $validator->validated()['tlds'] ?? [];

        // 1. Obtener precios de NameSilo
        // $nameSiloService->getTldPricingInfo ya devuelve un array ['tld' => ['registration' => X, ...]]
        // o un array vacío si hay error o no hay TLDs.
        $nameSiloPrices = $this->nameSiloService->getTldPricingInfo($requestedTlds);

        if ($nameSiloPrices === null) { // Podría ser null si el servicio tiene un error grave no capturado antes
            Log::error('NameSiloService::getTldPricingInfo devolvió null inesperadamente.');
            return response()->json(['status' => 'error', 'message' => 'No se pudo obtener la información de precios de TLDs del proveedor.'], 500);
        }

        // 2. Formatear para el frontend: convertir el array asociativo a una lista de objetos
        $formattedTldList = [];
        foreach ($nameSiloPrices as $tld => $prices) {
            // $prices ya contiene 'tld', 'registration', 'renewal', 'transfer', 'currency'
            // Solo necesitamos asegurar que la estructura sea una lista de estos objetos.
             $formattedTldList[] = [
                'tld' => $tld, // ej. "com"
                'name' => '.' . $tld, // ej. ".com" para mostrar
                'name_silo_info' => $prices, // toda la info de precios de namesilo para este tld
                // Ya no se incluyen 'internal_product_id', 'internal_product_slug', 'pricings' (internos)
                // porque el objetivo es devolver solo la info de NameSilo.
                // El frontend (SelectDomainPage) necesitará ser ajustado para usar esta nueva estructura.
            ];
        }

        return response()->json(['status' => 'success', 'data' => $formattedTldList]);
    }
}
