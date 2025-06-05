<?php

namespace App\Jobs;

use App\Models\InvoiceItem; // Changed from OrderItem
use App\Models\ClientService;
// use App\Models\OrderActivity; // Not used
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // For Str::random
use Throwable; // For catching exceptions

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionClientServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public InvoiceItem $invoiceItem; // Changed from OrderItem

    /**
     * Create a new job instance.
     *
     * @param InvoiceItem $invoiceItem
     */
    public function __construct(InvoiceItem $invoiceItem) // Changed from OrderItem
    {
        // Store a version of the model without relations to prevent issues with serialization.
        // Relations needed in handle() should be reloaded.
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
        $invoiceItem = InvoiceItem::with(['invoice.client', 'product.productType', 'productPricing.billingCycle', 'clientService'])
                                ->find($this->invoiceItem->id);

        if (!$invoiceItem) {
            Log::error("ProvisionClientServiceJob: InvoiceItem ID {$this->invoiceItem->id} not found. Skipping job.");
            return;
        }

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

        Log::info("ProvisionClientServiceJob: Iniciando aprovisionamiento para InvoiceItem ID: {$invoiceItem->id} de la Factura ID: {$invoiceItem->invoice_id}");

        $clientService = $invoiceItem->clientService; // Service should have been created in 'pending' state

        if (!$clientService) {
            Log::error("ProvisionClientServiceJob: ClientService no encontrado para InvoiceItem ID: {$invoiceItem->id}, aunque debería haber sido creado. Revisar flujo de pago.");
            // Optionally, attempt to create it here if that's desired fallback.
            // For now, we assume it must exist.
            return;
        }

        // If service is already active or in a non-pending state that shouldn't be auto-processed
        if ($clientService->status !== 'pending') {
            Log::info("ProvisionClientServiceJob: ClientService ID {$clientService->id} para InvoiceItem ID {$invoiceItem->id} no está en estado 'pending' (estado actual: {$clientService->status}). Saliendo del job.");
            return;
        }

        DB::beginTransaction();
        try {
            // Update existing ClientService, assuming it was created with basic details
            $clientService->client_id = $invoiceItem->invoice->client_id;
            $clientService->reseller_id = $invoiceItem->invoice->client->reseller_id ?? null;
            $clientService->product_id = $invoiceItem->product_id;
            $clientService->product_pricing_id = $invoiceItem->product_pricing_id;
            $clientService->billing_cycle_id = $invoiceItem->productPricing->billingCycle->id; // Ensure this relation is loaded
            $clientService->domain_name = $invoiceItem->domain_name;
            // billing_amount should have been set on creation, but can be re-confirmed
            $clientService->billing_amount = $invoiceItem->total_price; // total_price from InvoiceItem

            // === LÓGICA DE APROVISIONAMIENTO REAL (SIMULADA) ===
            // sleep(2); // Simulate work
            $clientService->username = $clientService->username ?: ('user_' . strtolower(Str::random(6)));
            $clientService->password_encrypted = $clientService->password_encrypted ?: ('sim_pass_' . Str::random(10));

            if (empty($clientService->registration_date)) {
                $clientService->registration_date = Carbon::now();
            }
            // Use registration_date as the base for next_due_date calculation
            $currentRegistrationDate = Carbon::parse($clientService->registration_date);
            $nextDueDate = $currentRegistrationDate->copy();
            $billingCycle = $invoiceItem->productPricing->billingCycle;

            if ($billingCycle) {
                 // Assuming BillingCycle has 'period_unit' and 'period_amount'
                if (isset($billingCycle->period_unit) && isset($billingCycle->period_amount) && is_numeric($billingCycle->period_amount) && $billingCycle->period_amount > 0) {
                    switch (strtolower($billingCycle->period_unit)) {
                        case 'day': $nextDueDate->addDays($billingCycle->period_amount); break;
                        case 'month': $nextDueDate->addMonthsNoOverflow($billingCycle->period_amount); break;
                        case 'year': $nextDueDate->addYearsNoOverflow($billingCycle->period_amount); break;
                        default:
                            Log::warning("ProvisionClientServiceJob: Unknown billing cycle unit '{$billingCycle->period_unit}' for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default.");
                            $nextDueDate->addMonth();
                    }
                } elseif (isset($billingCycle->days) && is_numeric($billingCycle->days) && $billingCycle->days > 0) { // Fallback to 'days'
                    $nextDueDate->addDays((int)$billingCycle->days);
                } else {
                    Log::warning("ProvisionClientServiceJob: BillingCycle period info missing/invalid for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default.");
                    $nextDueDate->addMonth();
                }
            } else {
                Log::warning("ProvisionClientServiceJob: BillingCycle not found for InvoiceItem ID: {$invoiceItem->id}. Using 1 month default for due date.");
                $nextDueDate->addMonth();
            }
            $clientService->next_due_date = $nextDueDate->toDateString();
            $clientService->notes = ($clientService->notes ? $clientService->notes . "\n" : '') . "Servicio (re)aprovisionado por Job el " . Carbon::now()->toDateTimeString();
            // === FIN LÓGICA DE APROVISIONAMIENTO REAL (SIMULADA) ===

            // Determine final status based on product type
            if (str_contains(strtolower($invoiceItem->product->productType->slug ?? ''), 'domain')) {
                // For domain types, leave as 'pending' or set to a specific pending domain state.
                // Admin will activate it via AdminInvoiceController@activateServices.
                // If it's already 'pending', no change needed here for status.
                // Or, if a specific state for domains post-job is desired:
                // $clientService->status = 'pending_domain_registration';
                Log::info("ProvisionClientServiceJob: ClientService ID {$clientService->id} (Domain) for InvoiceItem ID {$invoiceItem->id} remains/set to '{$clientService->status}'. Admin to activate.");
            } else {
                $clientService->status = 'active'; // Mark non-domains as active
            }

            $clientService->save();

            // Ensure InvoiceItem is linked to ClientService if not already (should be done in payWithBalance)
            if (is_null($invoiceItem->client_service_id) || $invoiceItem->client_service_id !== $clientService->id) {
                 $invoiceItem->client_service_id = $clientService->id;
                 $invoiceItem->save();
            }

            DB::commit();
            Log::info("ProvisionClientServiceJob: ClientService ID {$clientService->id} procesado para InvoiceItem ID: {$invoiceItem->id}. Estado final: {$clientService->status}.");

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("ProvisionClientServiceJob: Fallo al aprovisionar InvoiceItem ID: {$invoiceItem->id}. Error: " . $e->getMessage(), [
                'exception_class' => get_class($e),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 500)
            ]);

            if ($clientService && $clientService->exists) {
                $clientService->status = 'provisioning_failed';
                $clientService->notes = ($clientService->notes ? $clientService->notes . "\n" : '') . "Fallo de aprovisionamiento por Job: " . $e->getMessage();
                $clientService->saveQuietly();
            }
            throw $e;
        }
    }
}
