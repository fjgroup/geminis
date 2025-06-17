<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ClientService;
use App\Models\BillingCycle; // Asegurarse de importar BillingCycle
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transacciones si decidimos moverlas aquí

class ServiceProvisioningService
{
    /**
     * Provision services for a given paid invoice.
     * Iterates through invoice items, creates client services if applicable,
     * and updates the invoice status accordingly.
     *
     * @param Invoice $invoice
     * @return array Summary of actions (e.g., services_created_count)
     */
    public function provisionServicesForInvoice(Invoice $invoice): array
    {
        // Asegurarse de que las relaciones necesarias estén cargadas para evitar N+1 queries.
        // El controlador que llama a este servicio debería haber hecho $invoice->loadMissing(...)
        // pero podemos añadir una carga aquí por si acaso, aunque es mejor hacerlo antes.
        $invoice->loadMissing([
            'items.product.productType',
            'items.productPricing.billingCycle',
            'client' // Necesario para client_id y reseller_id si se toma del cliente
        ]);

        $servicesCreatedCount = 0;
        $hasAnyServiceToCreateOrProvision = false;

        // Es buena idea envolver esto en una transacción de base de datos
        // si el llamador no lo ha hecho ya, para asegurar la atomicidad.
        // Por ahora, asumimos que el llamador maneja la transacción principal (pago + provisión).

        foreach ($invoice->items as $invoiceItem) {
            Log::info("[ServiceProvisioningService] Processing InvoiceItem ID: {$invoiceItem->id} for Invoice ID: {$invoice->id}");

            // Condición para crear un nuevo servicio:
            // 1. El InvoiceItem tiene un producto asociado.
            // 2. El InvoiceItem NO está ya asociado a un ClientService existente.
            // 3. El InvoiceItem tiene un ProductPricing asociado.
            // 4. El ProductPricing tiene un BillingCycle asociado.
            // 5. El Producto asociado es de un tipo que debe generar una instancia de servicio.
            if (
                $invoiceItem->product_id &&
                !$invoiceItem->client_service_id && // Chequea si ya está vinculado
                $invoiceItem->productPricing &&
                $invoiceItem->productPricing->billingCycle &&
                $invoiceItem->product &&
                $invoiceItem->product->productType &&
                $invoiceItem->product->productType->creates_service_instance
            ) {
                $hasAnyServiceToCreateOrProvision = true; // Marcar que al menos un item es 'provisionable'
                Log::info("[ServiceProvisioningService] Conditions met to create ClientService for InvoiceItem ID: {$invoiceItem->id}");

                $productPricing = $invoiceItem->productPricing;
                $billingCycle = $productPricing->billingCycle;
                // Usar la fecha de pago de la factura como fecha de registro, o la fecha actual si no está definida.
                $registrationDate = $invoice->paid_date ? Carbon::parse($invoice->paid_date) : Carbon::now();
                $nextDueDate = $registrationDate->copy();

                // Calcular next_due_date basado en BillingCycle
                if (isset($billingCycle->period_unit) && isset($billingCycle->period_amount) && is_numeric($billingCycle->period_amount) && $billingCycle->period_amount > 0) {
                    switch (strtolower($billingCycle->period_unit)) {
                        case 'day':
                        case 'days':
                            $nextDueDate->addDays($billingCycle->period_amount);
                            break;
                        case 'month':
                        case 'months':
                            $nextDueDate->addMonthsNoOverflow($billingCycle->period_amount);
                            break;
                        case 'year':
                        case 'years':
                            $nextDueDate->addYearsNoOverflow($billingCycle->period_amount);
                            break;
                        default:
                            Log::warning("[ServiceProvisioningService] Unknown billing cycle unit '{$billingCycle->period_unit}' for ProductPricing ID: {$productPricing->id}. Defaulting next_due_date to 1 month.");
                            $nextDueDate->addMonth(); // Fallback seguro
                    }
                } elseif (isset($billingCycle->days) && is_numeric($billingCycle->days) && $billingCycle->days > 0) {
                    $nextDueDate->addDays((int)$billingCycle->days);
                } else {
                    Log::warning("[ServiceProvisioningService] BillingCycle period info not found or invalid for ProductPricing ID: {$productPricing->id}. Defaulting next_due_date to 100 years (error/manual check indicator).");
                    $nextDueDate->addYears(100); // Indica un problema que necesita revisión manual
                }

                $serviceStatus = 'pending_configuration'; // Estado inicial estándar

                $clientServiceData = [
                    'client_id' => $invoice->client_id,
                    'reseller_id' => $invoice->client->reseller_id, // Asumiendo que el cliente tiene una relación reseller_id
                    'product_id' => $invoiceItem->product_id,
                    'product_pricing_id' => $productPricing->id,
                    'billing_cycle_id' => $billingCycle->id, // Guardar el billing_cycle_id
                    'domain_name' => $invoiceItem->domain_name,
                    'status' => $serviceStatus,
                    'registration_date' => $registrationDate->toDateString(),
                    'next_due_date' => $nextDueDate->toDateString(),
                    'billing_amount' => $productPricing->price, // Monto recurrente
                    'notes' => 'Servicio creado automáticamente desde Factura #' . $invoice->invoice_number,
                    // 'username', 'password_encrypted' se pueden dejar nulos o manejar después
                ];

                Log::info("[ServiceProvisioningService] Preparing to create ClientService with data:", $clientServiceData);
                $clientService = ClientService::create($clientServiceData);
                Log::info("[ServiceProvisioningService] ClientService created with ID: {$clientService->id} for InvoiceItem ID: {$invoiceItem->id}");

                // Vincular el nuevo ClientService al InvoiceItem
                $invoiceItem->client_service_id = $clientService->id;
                $invoiceItem->save();
                Log::info("[ServiceProvisioningService] InvoiceItem ID: {$invoiceItem->id} updated with ClientService ID: {$clientService->id}");
                $servicesCreatedCount++;

                // Opcional: Despachar un Job para el aprovisionamiento si es un proceso largo o externo
                // if (class_exists(\App\Jobs\ProvisionClientServiceJob::class)) {
                //     \App\Jobs\ProvisionClientServiceJob::dispatch($clientService);
                //     Log::info("[ServiceProvisioningService] Dispatched ProvisionClientServiceJob for ClientService ID: {$clientService->id}");
                // }

            } elseif ($invoiceItem->client_service_id) {
                Log::info("[SPS] InvoiceItem ID: {$invoiceItem->id} is linked to existing ClientService ID: {$invoiceItem->client_service_id}. Processing as potential renewal/reactivation.");
                // Ensure all necessary relations for the item and the service are loaded.
                // productPricing.billingCycle for the invoice item (what was paid for).
                // clientService.billingCycle for the service's current cycle (though less critical here than the item's).
                $invoiceItem->loadMissing(['clientService.billingCycle', 'productPricing.billingCycle']);

                $service = $invoiceItem->clientService;
                if ($service) {
                    Log::info("[SPS] Processing existing service ID: {$service->id}. Current status: {$service->status}, Next Due Date: {$service->next_due_date}");

                    $billingCycleForItem = $invoiceItem->productPricing->billingCycle;

                    if (!$billingCycleForItem) {
                        Log::error("[SPS] BillingCycle for InvoiceItem ID: {$invoiceItem->id} (ProductPricing ID: {$invoiceItem->product_pricing_id}) not found. Cannot extend due date.");
                    } else {
                        Log::info("[SPS] BillingCycle for item: Name: {$billingCycleForItem->name}, Months: " . ($billingCycleForItem->period_amount ?? 'N/A') . " " . ($billingCycleForItem->period_unit ?? 'N/A') . " (or days: " . ($billingCycleForItem->days ?? 'N/A') . ")");

                        // Use current date if next_due_date is in the past (e.g., for reactivating an expired service)
                        $baseDate = Carbon::parse($service->next_due_date);
                        if ($baseDate->isPast()) {
                            Log::info("[SPS] Service ID: {$service->id} next_due_date is in the past. Using current date as base for renewal calculation.");
                            $baseDate = Carbon::now();
                        }
                        $newDueDate = $baseDate->copy();

                        $periodUnit = isset($billingCycleForItem->period_unit) ? strtolower($billingCycleForItem->period_unit) : null;
                        $periodAmount = isset($billingCycleForItem->period_amount) && is_numeric($billingCycleForItem->period_amount) ? (int)$billingCycleForItem->period_amount : 0;
                        $days = isset($billingCycleForItem->days) && is_numeric($billingCycleForItem->days) ? (int)$billingCycleForItem->days : 0;

                        if ($periodAmount > 0 && $periodUnit) {
                            switch ($periodUnit) {
                                case 'day': case 'days': $newDueDate->addDays($periodAmount); break;
                                case 'month': case 'months': $newDueDate->addMonthsNoOverflow($periodAmount); break;
                                case 'year': case 'years': $newDueDate->addYearsNoOverflow($periodAmount); break;
                                default: Log::warning("[SPS] Unknown billing cycle unit '{$billingCycleForItem->period_unit}'.");
                            }
                        } elseif ($days > 0) {
                            $newDueDate->addDays($days);
                        } else {
                             Log::error("[SPS] BillingCycle period info for item not found or invalid for BillingCycle ID: {$billingCycleForItem->id}. Cannot extend due date.");
                        }

                        Log::info("[SPS] Calculated new Next Due Date: {$newDueDate->toDateString()} from base: {$baseDate->toDateString()} for service ID: {$service->id}");
                        $service->next_due_date = $newDueDate->toDateString();

                        if (in_array(strtolower($service->status), ['suspended', 'pending', 'cancelled', 'terminated'])) { // Consider 'cancelled' or 'terminated' if payment implies reactivation
                            $service->status = 'active';
                            Log::info("[SPS] Service ID: {$service->id} status updated to 'active'.");
                        }
                        $service->save();
                        Log::info("[SPS] Service ID: {$service->id} next_due_date updated to {$service->next_due_date}. Status: {$service->status}");
                    }

                    // This service existed and was processed (renewal/reactivation)
                    // If its status is now active, pending, or pending_configuration, it might influence invoice status.
                    if (in_array(strtolower($service->status), ['pending_configuration', 'pending', 'active'])) {
                         $hasAnyServiceToCreateOrProvision = true;
                    }
                } else {
                    Log::warning("[SPS] ClientService ID: {$invoiceItem->client_service_id} linked in InvoiceItem ID: {$invoiceItem->id} not found in database.");
                }
            } else {
                Log::info("[ServiceProvisioningService] Conditions not met or not applicable to create ClientService for InvoiceItem ID: {$invoiceItem->id}. Product ID: " . ($invoiceItem->product_id ?? 'N/A') . ", Creates Instance: " . (isset($invoiceItem->product->productType) ? ($invoiceItem->product->productType->creates_service_instance ? 'true' : 'false') : 'N/A'));
            }
        }

        // Update invoice status based on whether any services require provisioning/activation
        // This logic might need refinement based on overall desired invoice workflow.
        // For instance, if an invoice contains both new services and renewals,
        // 'pending_activation' might be set if new services are created, even if renewals are just date extensions.
        if ($hasAnyServiceToCreateOrProvision && $invoice->status === 'paid') {
            $invoice->status = 'pending_activation'; // Or 'processing' if that's more appropriate
            $invoice->save();
            Log::info("[SPS] Invoice ID: {$invoice->id} status updated to 'pending_activation' due to services requiring provisioning/activation.");
        } elseif (!$hasAnyServiceToCreateOrProvision && $invoice->status === 'paid') {
            // If invoice is paid and no items required creation or were in a pre-active state,
            // it implies all items were renewals of already active services or non-instance items.
            // The invoice might be considered fully processed or 'completed' in terms of service delivery.
            // However, changing status here might conflict with other workflows.
            // For now, we only change to 'pending_activation' if needed.
            Log::info("[SPS] Invoice ID: {$invoice->id} is 'paid' and no new services created or pending services found to activate.");
        } elseif (!$hasAnyServiceToCreateOrProvision && $invoice->status === 'pending_activation') {
            Log::info("[SPS] Invoice ID: {$invoice->id} was 'pending_activation' but no services found to create/provision further in this run.");
        }


        return [
            'services_created_count' => $servicesCreatedCount,
            'invoice_final_status' => $invoice->status
        ];
    }
}
