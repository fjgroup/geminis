<?php

namespace App\Jobs;

use App\Models\InvoiceItem;
use App\Models\ClientService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionClientServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public InvoiceItem $invoiceItem;

    /**
     * Create a new job instance.
     *
     * @param InvoiceItem $invoiceItem
     */
    public function __construct(InvoiceItem $invoiceItem)
    {
        $this->invoiceItem = $invoiceItem->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        // Reload the invoiceItem with necessary relations
        $invoiceItem = InvoiceItem::with([
            'invoice.client',
            'product.productType',
            'productPricing.billingCycle',
            'clientService' // Eager load clientService relation
        ])->find($this->invoiceItem->id);

        if (!$invoiceItem) {
            Log::error("ProvisionClientServiceJob: InvoiceItem ID {$this->invoiceItem->id} not found. Skipping job.");
            return;
        }

        // Validate essential related models early
        if (!$invoiceItem->invoice || !$invoiceItem->invoice->client) {
            Log::error("ProvisionClientServiceJob: Invoice or Client not found for InvoiceItem ID: {$invoiceItem->id}. Skipping job.");
            return;
        }
        if (!$invoiceItem->product || !$invoiceItem->product->productType) {
            Log::error("ProvisionClientServiceJob: Product or ProductType not found for InvoiceItem ID: {$invoiceItem->id}. Skipping job.");
            return;
        }
        if (!$invoiceItem->productPricing || !$invoiceItem->productPricing->billingCycle) {
            Log::error("ProvisionClientServiceJob: ProductPricing or BillingCycle not found for InvoiceItem ID: {$invoiceItem->id}. Skipping job.");
            return;
        }

        Log::info("ProvisionClientServiceJob: Iniciando procesamiento para InvoiceItem ID: {$invoiceItem->id} de la Factura ID: {$invoiceItem->invoice_id}, Tipo de Item: {$invoiceItem->item_type}");

        // Attempt to find the ClientService associated with this InvoiceItem
        // This could be directly linked via client_service_id (for renewals)
        // or it might need to be identified/created (for new services)
        $clientService = $invoiceItem->clientService; // This should be loaded if client_service_id is set

        DB::beginTransaction();
        try {
            if ($invoiceItem->item_type === 'renewal') {
                // --- HANDLE RENEWAL ---
                if (!$clientService) {
                    Log::error("ProvisionClientServiceJob: [Renewal] ClientService no encontrado para InvoiceItem ID: {$invoiceItem->id}, pero es un item de renovación. Se requiere client_service_id. Revisar flujo.");
                    DB::rollBack(); // Rollback as this is an inconsistent state for renewal
                    // Optionally, you could attempt to find a matching service if client_service_id was missing, but it's safer to expect it.
                    return; // Stop processing this item
                }

                Log::info("ProvisionClientServiceJob: [Renewal] Procesando renovación para ClientService ID: {$clientService->id}");

                // Update existing ClientService for renewal
                $clientService->client_id = $invoiceItem->invoice->client_id; // Re-affirm client_id
                $clientService->reseller_id = $invoiceItem->invoice->client->reseller_id ?? null; // Re-affirm reseller_id
                // Product, product_pricing, billing_cycle should generally remain the same for a simple renewal.
                // If upgrades/downgrades change these, that's a more complex scenario.
                $clientService->billing_amount = $invoiceItem->total_price; // Update billing amount from the renewal invoice item

                $currentNextDueDate = Carbon::parse($clientService->next_due_date);
                $newNextDueDate = $currentNextDueDate->copy(); // Start from the current due date for calculation
                $billingCycle = $invoiceItem->productPricing->billingCycle;

                if ($billingCycle) {
                    if (isset($billingCycle->period_unit) && isset($billingCycle->period_amount) && is_numeric($billingCycle->period_amount) && $billingCycle->period_amount > 0) {
                        switch (strtolower($billingCycle->period_unit)) {
                            case 'day': $newNextDueDate->addDays($billingCycle->period_amount); break;
                            case 'month': $newNextDueDate->addMonthsNoOverflow($billingCycle->period_amount); break;
                            case 'year': $newNextDueDate->addYearsNoOverflow($billingCycle->period_amount); break;
                            default:
                                Log::warning("ProvisionClientServiceJob: [Renewal] Unknown billing cycle unit '{$billingCycle->period_unit}' for ClientService ID: {$clientService->id}. Using 1 month default.");
                                $newNextDueDate->addMonth();
                        }
                    } elseif (isset($billingCycle->days) && is_numeric($billingCycle->days) && $billingCycle->days > 0) {
                        $newNextDueDate->addDays((int)$billingCycle->days);
                    } else {
                        Log::warning("ProvisionClientServiceJob: [Renewal] BillingCycle period info missing/invalid for ClientService ID: {$clientService->id}. Using 1 month default.");
                        $newNextDueDate->addMonth();
                    }
                } else {
                    Log::warning("ProvisionClientServiceJob: [Renewal] BillingCycle not found for ClientService ID: {$clientService->id}. Using 1 month default for new due date.");
                    $newNextDueDate->addMonth();
                }
                $clientService->next_due_date = $newNextDueDate->toDateString();
                $clientService->status = 'active'; // Ensure service is active after renewal
                $clientService->notes = ($clientService->notes ? $clientService->notes . "
" : '') . "Servicio renovado por Job el " . Carbon::now()->toDateTimeString() . ". Nueva fecha de vencimiento: " . $clientService->next_due_date;

                $clientService->save();
                Log::info("ProvisionClientServiceJob: [Renewal] ClientService ID {$clientService->id} renovado. Nueva fecha de vencimiento: {$clientService->next_due_date}.");

            } else {
                // --- HANDLE NEW SERVICE PROVISIONING (existing logic) ---
                if ($clientService && $clientService->status !== 'pending' && $clientService->status !== 'provisioning_failed') {
                    // A service exists and is not in a state that this job should re-process for initial provisioning
                    Log::info("ProvisionClientServiceJob: [New Service] ClientService ID {$clientService->id} para InvoiceItem ID {$invoiceItem->id} no está en estado 'pending' o 'provisioning_failed' (estado actual: {$clientService->status}). Saliendo del job para este item.");
                    DB::commit(); // Commit any changes if this was part of a larger transaction (though usually one job per item)
                    return;
                }

                // If clientService doesn't exist, it means it was not pre-created.
                // This job typically expects a 'pending' service created by another process (e.g. payment confirmation)
                if (!$clientService) {
                    Log::error("ProvisionClientServiceJob: [New Service] ClientService no encontrado para InvoiceItem ID: {$invoiceItem->id}, y el item no es de renovación. Se esperaba un servicio en estado 'pending'. Revisar flujo de creación de servicios.");
                    // Depending on policy, you might create it here, or fail.
                    // For now, assuming it MUST exist in 'pending' state if not a renewal.
                    DB::rollBack();
                    return;
                }

                Log::info("ProvisionClientServiceJob: [New Service] Aprovisionando nuevo servicio para ClientService ID: {$clientService->id}");

                // Update existing ClientService (which should be in 'pending' state)
                $clientService->client_id = $invoiceItem->invoice->client_id;
                $clientService->reseller_id = $invoiceItem->invoice->client->reseller_id ?? null;
                $clientService->product_id = $invoiceItem->product_id;
                $clientService->product_pricing_id = $invoiceItem->product_pricing_id;
                $clientService->billing_cycle_id = $invoiceItem->productPricing->billingCycle->id;
                $clientService->domain_name = $invoiceItem->domain_name; // Domain from invoice item
                $clientService->billing_amount = $invoiceItem->total_price;

                // === SIMULATED PROVISIONING LOGIC ===
                $clientService->username = $clientService->username ?: ('user_' . strtolower(Str::random(6)));
                $clientService->password_encrypted = $clientService->password_encrypted ?: ('sim_pass_' . Str::random(10));

                if (empty($clientService->registration_date)) {
                    $clientService->registration_date = Carbon::now();
                }
                $currentRegistrationDate = Carbon::parse($clientService->registration_date);
                $nextDueDate = $currentRegistrationDate->copy();
                $billingCycle = $invoiceItem->productPricing->billingCycle;

                if ($billingCycle) {
                    if (isset($billingCycle->period_unit) && isset($billingCycle->period_amount) && is_numeric($billingCycle->period_amount) && $billingCycle->period_amount > 0) {
                        switch (strtolower($billingCycle->period_unit)) {
                            case 'day': $nextDueDate->addDays($billingCycle->period_amount); break;
                            case 'month': $nextDueDate->addMonthsNoOverflow($billingCycle->period_amount); break;
                            case 'year': $nextDueDate->addYearsNoOverflow($billingCycle->period_amount); break;
                            default:
                                Log::warning("ProvisionClientServiceJob: [New Service] Unknown billing cycle unit '{$billingCycle->period_unit}' for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default.");
                                $nextDueDate->addMonth();
                        }
                    } elseif (isset($billingCycle->days) && is_numeric($billingCycle->days) && $billingCycle->days > 0) {
                        $nextDueDate->addDays((int)$billingCycle->days);
                    } else {
                        Log::warning("ProvisionClientServiceJob: [New Service] BillingCycle period info missing/invalid for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default.");
                        $nextDueDate->addMonth();
                    }
                } else {
                    Log::warning("ProvisionClientServiceJob: [New Service] BillingCycle not found for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default for due date.");
                    $nextDueDate->addMonth();
                }
                $clientService->next_due_date = $nextDueDate->toDateString();
                $clientService->notes = ($clientService->notes ? $clientService->notes . "
" : '') . "Servicio (re)aprovisionado por Job el " . Carbon::now()->toDateTimeString();
                // === END SIMULATED PROVISIONING LOGIC ===

                // Determine final status based on product type for new services
                if (str_contains(strtolower($invoiceItem->product->productType->slug ?? ''), 'domain')) {
                    // For domain types, admin will activate it via AdminInvoiceController@activateServices.
                    // Status should remain 'pending' or a specific pending domain state set during creation.
                    Log::info("ProvisionClientServiceJob: [New Service] ClientService ID {$clientService->id} (Domain) for InvoiceItem ID {$invoiceItem->id} remains/set to '{$clientService->status}'. Admin to activate.");
                } else {
                    $clientService->status = 'active'; // Mark non-domains as active
                }

                $clientService->save();

                // Ensure InvoiceItem is linked to ClientService if not already
                if (is_null($invoiceItem->client_service_id) || $invoiceItem->client_service_id !== $clientService->id) {
                    $invoiceItem->client_service_id = $clientService->id;
                    $invoiceItem->save();
                }
                Log::info("ProvisionClientServiceJob: [New Service] ClientService ID {$clientService->id} procesado para InvoiceItem ID: {$invoiceItem->id}. Estado final: {$clientService->status}.");
            }

            DB::commit();
            Log::info("ProvisionClientServiceJob: Procesamiento completado exitosamente para InvoiceItem ID: {$invoiceItem->id}.");

        } catch (Throwable $e) {
            DB::rollBack();
            $errorMessage = "ProvisionClientServiceJob: Fallo al procesar InvoiceItem ID: {$invoiceItem->id} (Tipo: {$invoiceItem->item_type}). Error: " . $e->getMessage();
            Log::error($errorMessage, [
                'exception_class' => get_class($e),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000) // Increased snippet length
            ]);

            if ($clientService && $clientService->exists) {
                // For new services, mark as provisioning_failed.
                // For renewals, it might be better to leave the service active and log the error,
                // as the service was already active. Or introduce a 'renewal_failed' status.
                // For now, we'll use a generic note.
                if ($invoiceItem->item_type !== 'renewal') {
                    $clientService->status = 'provisioning_failed';
                }
                $clientService->notes = ($clientService->notes ? $clientService->notes . "
" : '') . "Fallo de procesamiento por Job ({$invoiceItem->item_type}): " . $e->getMessage();
                $clientService->saveQuietly(); // Save without triggering observers if any
            }
            // Re-throw the exception to allow the job to be marked as failed and potentially retried by the queue worker
            throw $e;
        }
    }
}
