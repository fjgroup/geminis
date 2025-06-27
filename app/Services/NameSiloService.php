<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NameSiloService
{
    protected $apiKey;
    protected $apiUrl;
    protected $apiVersion;
    protected $apiFormat;

    public function __construct()
    {
        $this->apiKey = config('services.namesilo.key');
        $this->apiUrl = config('services.namesilo.url');
        $this->apiVersion = config('services.namesilo.version');
        $this->apiFormat = config('services.namesilo.format', 'xml'); // Default to xml if not set, though we prefer json

        if (empty($this->apiKey)) {
            throw new Exception('NameSilo API key is not configured.');
        }
    }

    /**
     * Build the full API URL for a given operation.
     *
     * @param string $operation The API operation (e.g., checkRegisterAvailability).
     * @param array $params Additional query parameters.
     * @return string The full API URL.
     */
    private function buildUrl(string $operation, array $params = []): string
    {
        $queryParams = array_merge([
            'version' => $this->apiVersion,
            'type' => $this->apiFormat,
            'key' => $this->apiKey,
        ], $params);

        return $this->apiUrl . '/' . $operation . '?' . http_build_query($queryParams);
    }

    /**
     * Check domain availability.
     *
     * @param string $domainName
     * @return array ['available' => bool, 'is_premium' => bool, 'price' => float|null, 'message' => string]
     */
    public function checkDomainAvailability(string $domainName): array
    {
        $url = $this->buildUrl('checkRegisterAvailability', ['domains' => $domainName]);

        try {
            $response = Http::timeout(10)->get($url); // 10 second timeout

            if (!$response->successful()) {
                Log::error("NameSilo API request failed for checkRegisterAvailability ({$domainName}). Status: {$response->status()}", [
                    'response_body' => $response->body(),
                ]);
                return ['available' => false, 'is_premium' => false, 'price' => null, 'message' => 'Error al contactar al registrador. Intente más tarde.'];
            }

            // NameSilo responses are typically XML by default, but we request JSON.
            // If type=json, the structure is usually a root object 'namesilo'.
            $data = $response->json();

            // Log::debug("NameSilo checkRegisterAvailability response for {$domainName}:", $data);

            $reply = $data['namesilo']['reply'] ?? null;

            if (!$reply) {
                Log::error("NameSilo checkRegisterAvailability: 'reply' not found in response for {$domainName}", $data);
                return ['status' => 'error', 'message' => 'Respuesta inesperada del registrador.', 'domain_name' => $domainName];
            }

            $replyCode = $reply['code'] ?? null;
            $replyDetail = $reply['detail'] ?? 'No detail provided.';

            if ($replyCode == '300') { // Success
                if (isset($reply['available']['domain'])) { // Single domain query, available
                    $domainData = $reply['available'];
                    return [
                        'status' => 'available',
                        'domain_name' => $domainData['domain'],
                        'price' => isset($domainData['price']) ? (float)$domainData['price'] : null,
                        'is_premium' => isset($domainData['premium']) ? ($domainData['premium'] === '1') : false,
                        'duration' => isset($domainData['duration']) ? (int)$domainData['duration'] : 1,
                        'message' => "¡El dominio {$domainData['domain']} está disponible!",
                    ];
                } elseif (isset($reply['unavailable']['domain'])) { // Single domain query, unavailable
                    $domainData = $reply['unavailable'];
                    return [
                        'status' => 'unavailable',
                        'domain_name' => $domainData['domain'],
                        'price' => null,
                        'is_premium' => false,
                        'message' => "El dominio {$domainData['domain']} no está disponible.",
                    ];
                } elseif (isset($reply['invalid']['domain'])) { // Single domain query, invalid
                     $domainData = $reply['invalid'];
                    return [
                        'status' => 'invalid',
                        'domain_name' => $domainData['domain'],
                        'price' => null,
                        'is_premium' => false,
                        'message' => "El dominio {$domainData['domain']} no es válido.",
                    ];
                }
                // Fallback for code 300 if structure isn't as expected for single domain
                Log::warning("NameSilo checkRegisterAvailability: Code 300 but unexpected structure for {$domainName}", (array)$reply);
                return ['status' => 'error', 'message' => 'Respuesta del registrador no concluyente.', 'domain_name' => $domainName];

            } elseif ($replyCode == '280') { // Domain is invalid
                 return [
                        'status' => 'invalid',
                        'domain_name' => $domainName, // NameSilo might not return the domain name in this case
                        'price' => null,
                        'is_premium' => false,
                        'message' => $replyDetail . " (Dominio: {$domainName})",
                    ];
            } else { // Other error codes
                Log::error("NameSilo API error for checkRegisterAvailability ({$domainName}). Code: {$replyCode}", ['detail' => $replyDetail]);
                return ['status' => 'error', 'message' => "Error del registrador: {$replyDetail}", 'domain_name' => $domainName];
            }

        } catch (Exception $e) {
            Log::error("Exception during NameSilo checkRegisterAvailability for {$domainName}: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Error de comunicación con el registrador. Intente más tarde.', 'domain_name' => $domainName];
        }
    }

    /**
     * Get TLD pricing information.
     *
     * @param array $tldsToQuery List of TLDs بدون el punto (e.g., ['com', 'net', 'org'])
     * @return array ['tld' => ['registration' => price, 'renewal' => price, ...], ...]
     */
    public function getTldPricingInfo(array $tldsToQuery = []): array
    {
        // NameSilo's getPrices API returns all TLDs if no specific TLD is queried via parameters.
        // It doesn't seem to have a direct way to query specific TLDs in a single call via simple GET params other than by parsing the full list.
        // If $tldsToQuery is provided, we will filter the results.

        $url = $this->buildUrl('getPrices');

        try {
            $response = Http::timeout(15)->get($url); // 15 second timeout

            if (!$response->successful()) {
                Log::error("NameSilo API request failed for getPrices. Status: {$response->status()}", [
                    'response_body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            // Log::debug("NameSilo getPrices response:", $data);

            $pricingInfo = [];
            if (isset($data['namesilo']['reply']['code']) && $data['namesilo']['reply']['code'] == '300') {
                // Prices are listed directly under the 'reply' object, with each TLD being a key.
                foreach ($data['namesilo']['reply'] as $tld => $prices) {
                    if (is_array($prices) && isset($prices['registration']) && isset($prices['renew'])) { // Basic check for a TLD entry
                        $cleanTld = str_replace('.', '', $tld); // Ensure TLD is without a dot
                        if (empty($tldsToQuery) || in_array($cleanTld, $tldsToQuery)) {
                            $pricingInfo[$cleanTld] = [
                                'registration' => (float) $prices['registration'],
                                'renewal' => (float) $prices['renew'],
                                'transfer' => (float) $prices['transfer'],
                                'currency' => 'USD', // NameSilo prices are in USD
                            ];
                        }
                    }
                }
                return $pricingInfo;
            } else {
                 Log::warning("NameSilo API error for getPrices. Code: {$data['namesilo']['reply']['code']}", [
                    'detail' => $data['namesilo']['reply']['detail'] ?? 'No detail',
                ]);
                return [];
            }

        } catch (Exception $e) {
            Log::error("Exception during NameSilo getPrices: " . $e->getMessage());
            return [];
        }
    }
}
