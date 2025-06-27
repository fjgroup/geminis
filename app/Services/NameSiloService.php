<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NameSiloService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $apiVersion;
    protected string $apiFormat;

    public function __construct()
    {
        $this->apiKey = config('services.namesilo.key');
        $this->apiUrl = config('services.namesilo.url');
        $this->apiVersion = config('services.namesilo.version', '1');
        $this->apiFormat = config('services.namesilo.format', 'json');

        if (empty($this->apiKey)) {
            Log::critical('NameSilo API Key no está configurada en config/services.php o .env.');
            throw new Exception('El servicio de dominios no está configurado correctamente (API Key faltante).');
        }
    }

    private function buildUrl(string $operation, array $params = []): string
    {
        $queryParams = array_merge([
            'version' => $this->apiVersion,
            'type' => $this->apiFormat,
            'key' => $this->apiKey,
        ], $params);
        $url = rtrim($this->apiUrl, '/') . '/' . trim($operation, '/');
        return $url . '?' . http_build_query($queryParams);
    }

    public function checkDomainAvailability(string $domainName): array
    {
        $url = $this->buildUrl('checkRegisterAvailability', ['domains' => $domainName]);
        $defaultErrorMessage = 'No se pudo verificar la disponibilidad del dominio en este momento.';
        $baseErrorPayload = ['status' => 'error', 'domain_name' => $domainName, 'price' => null, 'is_premium' => false, 'duration' => null, 'renewal_price' => null, 'message' => $defaultErrorMessage];

        try {
            $response = Http::timeout(10)->get($url);
            $responseBody = $response->body();

            if (!$response->successful()) {
                Log::error("NameSilo API: Falla HTTP en checkRegisterAvailability para {$domainName}. Status: {$response->status()}", [
                    'url' => $url, 'response_body' => $responseBody,
                ]);
                return array_merge($baseErrorPayload, ['message' => 'Error de comunicación con el registrador.']);
            }

            $data = $response->json();
            if (!$data) {
                Log::error("NameSilo API: Respuesta JSON vacía o inválida para checkRegisterAvailability {$domainName}", [
                    'url' => $url, 'response_body' => $responseBody,
                ]);
                return array_merge($baseErrorPayload, ['message' => 'Respuesta inválida del registrador.']);
            }

            $reply = $data['reply'] ?? null;

            if (!$reply || !isset($reply['code'])) {
                Log::error("NameSilo API: 'reply' o 'reply.code' no encontrado en respuesta para checkRegisterAvailability {$domainName}", ['response_data' => $data, 'response_body' => $responseBody]);
                return array_merge($baseErrorPayload, ['message' => 'Respuesta inesperada del registrador (faltan campos reply o code).']);
            }

            $replyCode = (string)$reply['code'];
            $replyDetail = $reply['detail'] ?? 'Operación procesada.';
            $domainFromResponse = null;

            if ($replyCode === '300') {
                // Caso: Dominio disponible
                // Estructura observada: $reply['available']['domain'] es el objeto/array con los detalles finales.
                if (isset($reply['available']['domain']) && (is_object($reply['available']['domain']) || is_array($reply['available']['domain']))) {
                    $details = (array) $reply['available']['domain']; // Castear a array para acceso uniforme

                    if (isset($details['domain']) && is_string($details['domain'])) {
                        $domainFromResponse = $details['domain'];
                    } else {
                        Log::warning("NameSilo API (available): La clave 'domain' (nombre) no es un string o no existe dentro del objeto 'available.domain'.", [
                            'details_object' => $details, 'reply' => $reply, 'response_body' => $responseBody
                        ]);
                        return array_merge($baseErrorPayload, ['message' => 'Error al parsear nombre de dominio disponible desde el registrador (E300ADNP).']);
                    }

                    if (strtolower($domainFromResponse) !== strtolower($domainName)) {
                        Log::warning("NameSilo API (available): Nombre de dominio en respuesta ('{$domainFromResponse}') no coincide con solicitado ('{$domainName}'). Se usará el de la respuesta.", ['reply' => $reply]);
                    }

                    $price = isset($details['price']) ? (float)$details['price'] : null;
                    $isPremium = isset($details['premium']) ? (((int)$details['premium'] === 1) || $details['premium'] === true || $details['premium'] === '1') : false;
                    $duration = isset($details['duration']) ? (int)$details['duration'] : null;
                    $renewalPrice = isset($details['renew']) ? (float)$details['renew'] : null;

                    return [
                        'status' => 'available', 'domain_name' => $domainFromResponse,
                        'price' => $price, 'is_premium' => $isPremium, 'duration' => $duration, 'renewal_price' => $renewalPrice,
                        'message' => $replyDetail === 'success' || $replyDetail === 'Operación procesada.' ? "¡El dominio {$domainFromResponse} está disponible!" : $replyDetail,
                    ];
                // Caso: Dominio no disponible (la clave es el TLD, ej. $reply['unavailable']['example.com'])
                // O, si solo se consultó uno, puede ser $reply['unavailable']['domain'] (string)
                } elseif (isset($reply['unavailable']['domain'])) {
                    $unavailableDomainData = $reply['unavailable']['domain'];
                     if (is_string($unavailableDomainData)) {
                        $domainFromResponse = $unavailableDomainData;
                    } elseif ((is_object($unavailableDomainData) || is_array($unavailableDomainData)) && isset(((array)$unavailableDomainData)['domain'])) {
                        $domainFromResponse = ((array)$unavailableDomainData)['domain'];
                    } else {
                        $domainFromResponse = $domainName;
                        Log::warning("NameSilo API (unavailable): Estructura de nombre de dominio inesperada.", ['data' => $unavailableDomainData, 'response_body' => $responseBody]);
                    }
                    return ['status' => 'unavailable', 'domain_name' => $domainFromResponse, 'price' => null, 'is_premium' => false, 'duration' => null, 'renewal_price' => null, 'message' => $replyDetail === 'success' || $replyDetail === 'Operación procesada.' ? "El dominio {$domainFromResponse} no está disponible." : $replyDetail];
                // Caso: Dominio inválido
                } elseif (isset($reply['invalid']['domain'])) {
                    $invalidDomainData = $reply['invalid']['domain'];
                    if (is_string($invalidDomainData)) {
                        $domainFromResponse = $invalidDomainData;
                    } elseif ((is_object($invalidDomainData) || is_array($invalidDomainData)) && isset(((array)$invalidDomainData)['domain'])) {
                        $domainFromResponse = ((array)$invalidDomainData)['domain'];
                    } else {
                        $domainFromResponse = $domainName;
                        Log::warning("NameSilo API (invalid): Estructura de nombre de dominio inesperada.", ['data' => $invalidDomainData, 'response_body' => $responseBody]);
                    }
                    return ['status' => 'invalid', 'domain_name' => $domainFromResponse, 'price' => null, 'is_premium' => false, 'duration' => null, 'renewal_price' => null, 'message' => $replyDetail === 'success' || $replyDetail === 'Operación procesada.' ? "La sintaxis del dominio {$domainFromResponse} es inválida." : $replyDetail];
                }

                Log::warning("NameSilo API: Código 300 pero estructura de 'reply' inesperada para checkRegisterAvailability {$domainName}", ['reply' => $reply, 'response_body' => $responseBody]);
                return array_merge($baseErrorPayload, ['message' => 'Respuesta del registrador no concluyente (E300Parse).']);

            } elseif (in_array($replyCode, ['280', '256'])) {
                 return ['status' => 'invalid', 'domain_name' => $domainName, 'price' => null, 'is_premium' => false, 'duration' => null, 'renewal_price' => null, 'message' => $replyDetail . " (Dominio: {$domainName})"];
            } elseif ($replyCode === '266') {
                 return ['status' => 'unavailable', 'domain_name' => $domainName, 'price' => null, 'is_premium' => false, 'duration' => null, 'renewal_price' => null, 'message' => $replyDetail === 'success' || $replyDetail === 'Operación procesada.' ? "El dominio {$domainName} ya está en tu cuenta o no se puede registrar." : $replyDetail];
            } else {
                Log::error("NameSilo API: Error en checkRegisterAvailability para {$domainName}. Code: {$replyCode}", ['detail' => $replyDetail, 'reply' => $reply, 'response_body' => $responseBody]);
                return array_merge($baseErrorPayload, ['message' => "Error del registrador ({$replyCode}): {$replyDetail}"]);
            }
        } catch (Exception $e) {
            Log::error("NameSilo API: Excepción en checkRegisterAvailability para {$domainName}: " . $e->getMessage(), ['exception' => $e, 'url' => $url]);
            return array_merge($baseErrorPayload, ['message' => 'Excepción al contactar al registrador.']);
        }
    }

    public function getTldPricingInfo(array $tldsToQuery = []): array
    {
        $url = $this->buildUrl('getPrices');
        $errorPayload = [];

        try {
            $response = Http::timeout(20)->get($url);
            $responseBody = $response->body();

            if (!$response->successful()) {
                Log::error("NameSilo API: Falla HTTP en getPrices. Status: {$response->status()}", [
                    'url' => $url, 'response_body' => $responseBody,
                ]);
                return $errorPayload;
            }

            $data = $response->json();
            if (!$data) {
                Log::error("NameSilo API: Respuesta JSON vacía o inválida para getPrices", [
                    'url' => $url, 'response_body' => $responseBody,
                ]);
                return $errorPayload;
            }

            $reply = $data['reply'] ?? null;

            if (!$reply || !isset($reply['code'])) {
                Log::error("NameSilo API: 'reply' o 'reply.code' no encontrado en respuesta para getPrices", ['response_data' => $data, 'response_body' => $responseBody]);
                return $errorPayload;
            }

            $replyCode = (string)($reply['code'] ?? 'unknown');
            $replyDetail = $reply['detail'] ?? 'No hay detalle.';

            if ($replyCode === '300') {
                $parsedPrices = [];
                $normalizedTldsToQuery = array_map('strtolower', $tldsToQuery);

                foreach ($reply as $tldKey => $prices) {
                    if (!is_array($prices) || !isset($prices['registration']) || !isset($prices['renew']) || !isset($prices['transfer'])) {
                        continue;
                    }

                    $cleanTld = ltrim(strtolower($tldKey), '.');

                    if (empty($normalizedTldsToQuery) || in_array($cleanTld, $normalizedTldsToQuery)) {
                        $parsedPrices[$cleanTld] = [
                            'tld' => $cleanTld,
                            'registration' => (float) $prices['registration'],
                            'renewal' => (float) $prices['renew'],
                            'transfer' => (float) $prices['transfer'],
                            'currency' => 'USD',
                        ];
                    }
                }
                return $parsedPrices;
            } else {
                Log::error("NameSilo API: Error en getPrices. Code: {$replyCode}", ['detail' => $replyDetail, 'reply' => $reply, 'response_body' => $responseBody]);
                return $errorPayload;
            }
        } catch (Exception $e) {
            Log::error("NameSilo API: Excepción en getPrices: " . $e->getMessage(), ['exception' => $e, 'url' => $url]);
            return $errorPayload;
        }
    }
}
