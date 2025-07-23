<?php

namespace App\Domains\Orders\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de aplicación para creación de servicios de cliente
 * 
 * Aplica Single Responsibility Principle - solo crea servicios de cliente
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ClientServiceCreationService
{
    /**
     * Crear servicios de cliente desde el carrito
     */
    public function createClientServicesFromCart(User $client, array $cart, Invoice $invoice): void
    {
        $clientServicesCollection = [];

        foreach ($cart['accounts'] as $account) {
            $domainNameForService = $account['domain_info']['domain_name'] ?? null;

            // Crear servicio para domain_info
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $service = $this->createDomainService($client, $account['domain_info']);
                if ($service) {
                    $clientServicesCollection[] = $service;
                }
            }

            // Crear servicio para primary_service
            if (!empty($account['primary_service'])) {
                $service = $this->createPrimaryService($client, $account['primary_service'], $domainNameForService);
                if ($service) {
                    $clientServicesCollection[] = $service;
                }
            }

            // Crear servicios para additional_services
            if (!empty($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalService) {
                    $service = $this->createAdditionalService($client, $additionalService, $domainNameForService);
                    if ($service) {
                        $clientServicesCollection[] = $service;
                    }
                }
            }
        }

        // Guardar todos los servicios
        foreach ($clientServicesCollection as $service) {
            $service->save();
        }

        Log::info('Client services created successfully', [
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'services_count' => count($clientServicesCollection)
        ]);
    }

    /**
     * Crear servicio de dominio
     */
    private function createDomainService(User $client, array $item): ?ClientService
    {
        $productModel = Product::find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            Log::error("Error creating domain service: product or pricing not found", ['item' => $item]);
            return null;
        }

        $unitPrice = (isset($item['override_price']) && is_numeric($item['override_price']))
            ? (float) $item['override_price']
            : (float) $pricingModel->price;

        return new ClientService([
            'client_id' => $client->id,
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'billing_cycle_id' => $pricingModel->billing_cycle_id,
            'domain_name' => $item['domain_name'],
            'status' => 'pending',
            'registration_date' => Carbon::now(),
            'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
            'notes' => "Extensión: .{$item['tld_extension']}",
            'first_payment_amount' => $unitPrice,
            'billing_amount' => $unitPrice,
        ]);
    }

    /**
     * Crear servicio principal
     */
    private function createPrimaryService(User $client, array $item, ?string $domainNameForService): ?ClientService
    {
        $productModel = Product::with(['productType', 'configurableOptionGroups.options.pricings'])
            ->find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            Log::error("Error creating primary service: product or pricing not found", ['item' => $item]);
            return null;
        }

        // Obtener notas de opciones configurables
        $configurableOptionsForServiceNotes = $this->getConfigurableOptionsNotes($item);

        // Calcular precio final (esto debería venir del InvoiceCreationService, pero por simplicidad lo calculamos aquí)
        $finalUnitPrice = (float) $pricingModel->price;

        return new ClientService([
            'client_id' => $client->id,
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'billing_cycle_id' => $pricingModel->billing_cycle_id,
            'domain_name' => $domainNameForService,
            'status' => 'pending',
            'registration_date' => Carbon::now(),
            'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
            'notes' => implode("\n", $configurableOptionsForServiceNotes),
            'first_payment_amount' => $finalUnitPrice,
            'billing_amount' => $finalUnitPrice,
        ]);
    }

    /**
     * Crear servicio adicional
     */
    private function createAdditionalService(User $client, array $item, ?string $domainNameForService): ?ClientService
    {
        $productModel = Product::with('productType')->find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            Log::error("Error creating additional service: product or pricing not found", ['item' => $item]);
            return null;
        }

        $unitPrice = (float) $pricingModel->price;

        return new ClientService([
            'client_id' => $client->id,
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'billing_cycle_id' => $pricingModel->billing_cycle_id,
            'domain_name' => $domainNameForService,
            'status' => 'pending',
            'registration_date' => Carbon::now(),
            'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
            'first_payment_amount' => $unitPrice,
            'billing_amount' => $unitPrice,
        ]);
    }

    /**
     * Obtener notas de opciones configurables
     */
    private function getConfigurableOptionsNotes(array $item): array
    {
        $configurableOptionsForServiceNotes = [];
        
        $cartItemId = $item['cart_item_id'] ?? null;
        if ($cartItemId) {
            $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                ->where('is_active', true)
                ->get();

            foreach ($configurableOptions as $configOption) {
                $serviceNote = $this->generateDetailedServiceNote(
                    $configOption->group_name,
                    $configOption->option_name,
                    $configOption->quantity
                );
                if ($serviceNote) {
                    $configurableOptionsForServiceNotes[] = $serviceNote;
                }
            }
        }

        return $configurableOptionsForServiceNotes;
    }

    /**
     * Calcular próxima fecha de vencimiento
     */
    private function calculateNextDueDate(BillingCycle $billingCycle): Carbon
    {
        $startDate = Carbon::today();

        $multiplier = (int) ($billingCycle->cycle_multiplier ?? 1);
        if ($multiplier < 1) {
            Log::warning('Invalid cycle multiplier, using 1 as default', [
                'billing_cycle_id' => $billingCycle->id,
                'original_multiplier' => $billingCycle->cycle_multiplier,
            ]);
            $multiplier = 1;
        }

        $unitInput = $billingCycle->cycle_unit ?? 'month';
        $unit = strtolower(trim($unitInput));
        if (empty($unit)) {
            Log::warning('Empty cycle unit, using month as default', [
                'billing_cycle_id' => $billingCycle->id,
                'original_unit' => $billingCycle->cycle_unit,
            ]);
            $unit = 'month';
        }

        switch ($unit) {
            case 'day':
            case 'days':
                return $startDate->addDays($multiplier);
            case 'week':
            case 'weeks':
                return $startDate->addWeeks($multiplier);
            case 'month':
            case 'months':
                return $startDate->addMonthsNoOverflow($multiplier);
            case 'quarter':
            case 'quarters':
                return $startDate->addMonthsNoOverflow($multiplier * 3);
            case 'year':
            case 'years':
                return $startDate->addYearsNoOverflow($multiplier);
            default:
                Log::error('Unknown billing cycle unit', [
                    'billing_cycle_id' => $billingCycle->id,
                    'cycle_unit_received' => $unitInput,
                    'cycle_multiplier_received' => $billingCycle->cycle_multiplier ?? 'N/A',
                ]);
                return $startDate->addMonthsNoOverflow(1);
        }
    }

    /**
     * Generar nota detallada para el servicio basada en las opciones configurables
     */
    private function generateDetailedServiceNote(string $groupName, string $optionName, float $quantity): string
    {
        // Mapear nombres de grupos a descripciones más específicas
        $groupMappings = [
            'Espacio en Disco' => 'GB de espacio web adicional',
            'vCPU' => 'vCPU adicionales',
            'vRam' => 'GB de RAM adicional',
            'Memoria RAM' => 'GB de RAM adicional',
            'Transferencia' => 'GB de transferencia adicional',
            'Seguridad Email' => 'servicio de seguridad email',
            'SpamExperts' => 'protección SpamExperts',
            'Backup' => 'servicios de backup adicionales',
            'SSL' => 'certificados SSL adicionales',
        ];

        // Obtener la descripción específica o usar el nombre del grupo como fallback
        $description = $groupMappings[$groupName] ?? strtolower($groupName);

        // Generar la nota según el tipo de recurso
        if ($quantity > 1) {
            // Para cantidades mayores a 1, mostrar la cantidad específica
            if (str_contains($description, 'GB') || str_contains($description, 'vCPU')) {
                return "{$quantity} {$description}";
            } else {
                return "{$quantity} unidades de {$description}";
            }
        } else {
            // Para cantidad 1, usar descripción simple
            if (str_contains($description, 'servicio') || str_contains($description, 'protección')) {
                return ucfirst($description) . " activado";
            } else {
                return "1 {$description}";
            }
        }
    }
}
