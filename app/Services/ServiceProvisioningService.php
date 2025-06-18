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

                // Calcular next_due_date basado en BillingCycle->days
                if (!$billingCycle || !property_exists($billingCycle, 'days') || !is_numeric($billingCycle->days) || $billingCycle->days <= 0) {
                    $daysValue = 'N/A';
                    if ($billingCycle && property_exists($billingCycle, 'days')) {
                        $daysValue = is_scalar($billingCycle->days) ? strval($billingCycle->days) : gettype($billingCycle->days);
                    } elseif ($billingCycle) {
                        $daysValue = 'propiedad days NO EXISTE';
                    } else {
                        $daysValue = 'billingCycle ES NULL';
                    }
                    Log::error("[SPS] Configuración inválida para BillingCycle ID: " . ($billingCycle->id ?? 'desconocido') . " al crear nuevo servicio para ProductPricing ID: {$productPricing->id} - la propiedad 'days' es inválida: " . $daysValue . ". Saltando creación de este servicio.");
                    // No se puede continuar sin una duración de ciclo válida para un nuevo servicio.
                    // Opcionalmente, se podría asignar un fallback muy largo y marcar para revisión manual.
                    // Por ahora, se omite la creación de este servicio específico.
                    // $nextDueDate->addYears(100); // Fallback anterior, ahora se omite.
                    // continue; // Saltar al siguiente invoiceItem si no se puede determinar la duración
                    // Mejor aún, si esto es crítico, la factura no debería llegar a este punto o el item debería marcarse como fallido.
                    // Para este ejemplo, si no hay días, el servicio no se crea con fecha válida.
                    // Considerar una excepción o una política más estricta.
                    // Por ahora, si esto falla, el servicio no se creará con una fecha de vencimiento correcta,
                    // lo que podría ser problemático. Se loguea el error.
                    // Si se decide continuar, la fecha de vencimiento será igual a la de registro.
                    Log::warning("[SPS] BillingCycle period info not found or invalid for ProductPricing ID: {$productPricing->id}. Next due date will be same as registration date. Manual review needed.");
                } else {
                    $nextDueDate->addDays((int)$billingCycle->days);
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
                    Log::info("[SPS] Processing existing service ID: {$service->id}. Current status: {$service->status}, Next Due Date: {$service->next_due_date->toDateString()}");
                    $service->loadMissing(['productPricing.billingCycle', 'product']); // Ensure current service relations are loaded

                    $billingCycleForItem = $invoiceItem->productPricing->billingCycle; // Cycle being paid for

                    if (!$billingCycleForItem || !property_exists($billingCycleForItem, 'days') || !is_numeric($billingCycleForItem->days) || $billingCycleForItem->days <= 0) {
                        $daysValue = 'N/A';
                        if ($billingCycleForItem && property_exists($billingCycleForItem, 'days')) {
                            $daysValue = is_scalar($billingCycleForItem->days) ? strval($billingCycleForItem->days) : gettype($billingCycleForItem->days);
                        } elseif ($billingCycleForItem) {
                            $daysValue = 'propiedad days NO EXISTE';
                        } else {
                            $daysValue = 'billingCycleForItem ES NULL';
                        }
                        Log::error("[SPS] Configuración de ciclo inválida para InvoiceItem ID: {$invoiceItem->id} (BillingCycle ID: " . ($billingCycleForItem->id ?? 'desconocido') . ") - 'days' es inválido: " . $daysValue . ". No se puede procesar la extensión de fecha.");
                    } else {
                        // Determine if this is a renewal or an upgrade invoice item
                        $itemDescription = strtolower($invoiceItem->description);
                        $isRenewal = str_contains($itemDescription, 'renewal') || str_contains($itemDescription, 'renovación');
                        // Description for upgrade items from ClientServiceController: "Cargo por actualización a PRODUCT_NAME (CYCLE_NAME)"
                        $isUpgrade = str_contains($itemDescription, 'actualización a') || str_contains($itemDescription, 'upgrade to');


                        if ($isRenewal) {
                            Log::info("[SPS] InvoiceItem ID: {$invoiceItem->id} identified as RENEWAL for service ID: {$service->id}.");
                            $baseDate = Carbon::parse($service->next_due_date);
                            if ($baseDate->isPast()) {
                                Log::info("[SPS] Service ID: {$service->id} next_due_date is in the past. Using current date as base for renewal calculation.");
                                $baseDate = Carbon::now();
                            }
                            $newDueDate = $baseDate->copy()->addDays((int)$billingCycleForItem->days);
                            $service->next_due_date = $newDueDate->toDateString();
                            Log::info("[SPS] Service ID: {$service->id} RENEWED. New next_due_date: {$service->next_due_date}. Old: {$baseDate->toDateString()}");

                            if (in_array(strtolower($service->status), ['suspended', 'pending', 'cancelled', 'terminated'])) {
                                $service->status = 'active'; // Reactivate on renewal
                                Log::info("[SPS] Service ID: {$service->id} status updated to 'active' due to renewal.");
                            }
                        } elseif ($isUpgrade) {
                            Log::info("[SPS] InvoiceItem ID: {$invoiceItem->id} identified as UPGRADE payment for service ID: {$service->id}. Next_due_date already set by upgrade process to {$service->next_due_date->toDateString()}. No change to next_due_date here.");
                            // For upgrades, next_due_date is set in ClientServiceController@processUpgradeDowngrade.
                            // Status might be 'pending_configuration' if product_id changed, or 'active'.
                            // If it was 'active' and only cycle changed, it remains 'active'.
                            // If it became 'pending_configuration', it stays that way for admin action.
                            // No specific status change here unless a suspended service was upgraded and should become active/pending_config.
                            if (strtolower($service->status) === 'suspended') { // If somehow an upgrade was processed for a suspended service
                                 // If product_id changed during upgrade, it would be pending_configuration, otherwise active
                                if ($service->product_id !== $service->getOriginal('product_id', $service->product_id)) { // Checking if product_id was changed in current loaded model state (it was already saved by controller)
                                     $service->status = 'pending_configuration';
                                } else {
                                     $service->status = 'active';
                                }
                                Log::info("[SPS] Service ID: {$service->id} status updated to '{$service->status}' post-upgrade payment from suspended state.");
                            }
                        } else {
                            Log::warning("[SPS] InvoiceItem ID: {$invoiceItem->id} for service ID: {$service->id} is neither clearly a renewal nor an upgrade. Description: '{$invoiceItem->description}'. Next_due_date NOT extended based on this item alone.");
                            // Potentially apply a default extension if that's the desired behavior for unknown items linked to a service.
                            // For now, only explicit renewals extend.
                        }
                        $service->save();
                        Log::info("[SPS] Service ID: {$service->id} saved. Final status: {$service->status}, Next Due Date: {$service->next_due_date->toDateString()}");
                    }

                    // This service existed and was processed (renewal/reactivation/upgrade paid)
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
