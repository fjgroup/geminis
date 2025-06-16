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
                Log::info("[ServiceProvisioningService] InvoiceItem ID: {$invoiceItem->id} already has ClientService ID: {$invoiceItem->client_service_id}. Skipping creation.");
                // Marcar que hay un servicio que podría necesitar activación si está 'pending'
                $invoiceItem->loadMissing('clientService'); // Asegurar que clientService esté cargado
                if ($invoiceItem->clientService && ($invoiceItem->clientService->status === 'pending_configuration' || $invoiceItem->clientService->status === 'pending')) {
                     $hasAnyServiceToCreateOrProvision = true;
                }

            } else {
                Log::info("[ServiceProvisioningService] Conditions not met or not applicable to create ClientService for InvoiceItem ID: {$invoiceItem->id}. Product ID: " . ($invoiceItem->product_id ?? 'N/A') . ", Creates Instance: " . (isset($invoiceItem->product->productType) ? ($invoiceItem->product->productType->creates_service_instance ? 'true' : 'false') : 'N/A'));
            }
        }

        // Actualizar el estado de la factura si se crearon/procesaron servicios que requieren activación
        if ($hasAnyServiceToCreateOrProvision && $invoice->status === 'paid') {
            $invoice->status = 'pending_activation';
            $invoice->save();
            Log::info("[ServiceProvisioningService] Invoice ID: {$invoice->id} status updated to 'pending_activation'.");
        } elseif (!$hasAnyServiceToCreateOrProvision && $invoice->status === 'pending_activation') {
            // Si estaba en pending_activation pero ya no hay nada que activar (quizás se activaron manualmente)
            // se podría considerar volver a 'paid' o a 'active_service' si todos los servicios vinculados están activos.
            // Esto es una lógica más compleja, por ahora lo dejamos así.
            Log::info("[ServiceProvisioningService] Invoice ID: {$invoice->id} was 'pending_activation' but no services found to create/provision further.");
        }


        return [
            'services_created_count' => $servicesCreatedCount,
            'invoice_final_status' => $invoice->status
        ];
    }
}
```
