<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Models\ClientService;
use App\Models\OrderActivity; // Not directly used in this version of the job, ClientServiceObserver handles OrderActivity
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

    public OrderItem $orderItem;

    /**
     * Create a new job instance.
     *
     * @param OrderItem $orderItem
     */
    public function __construct(OrderItem $orderItem)
    {
        // Store a version of the model without relations to prevent issues with serialization.
        // Relations needed in handle() should be reloaded.
        $this->orderItem = $orderItem->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        // Reload the orderItem with necessary relations to ensure fresh data and access to related models.
        // This is crucial because the model passed to the constructor had relations removed.
        $orderItem = OrderItem::with(['order.client', 'product', 'productPricing.billingCycle'])
                                ->find($this->orderItem->id);

        if (!$orderItem) {
            Log::error("ProvisionClientServiceJob: OrderItem ID {$this->orderItem->id} not found. Skipping job.");
            return;
        }

        // Re-assign to the class property if needed elsewhere, or just use the local var.
        // $this->orderItem = $orderItem;

        $order = $orderItem->order;

        if (!$order) {
            Log::error("ProvisionClientServiceJob: Order not found for OrderItem ID: {$orderItem->id}. Skipping job.");
            return;
        }
        if (!$orderItem->product) {
             Log::error("ProvisionClientServiceJob: Product not found for OrderItem ID: {$orderItem->id}. Skipping job.");
            return;
        }
         if (!$orderItem->productPricing || !$orderItem->productPricing->billingCycle) {
             Log::error("ProvisionClientServiceJob: ProductPricing or BillingCycle not found for OrderItem ID: {$orderItem->id}. Skipping job.");
            return;
        }


        Log::info("ProvisionClientServiceJob: Iniciando aprovisionamiento para OrderItem ID: {$orderItem->id} de la Orden ID: {$order->id}");

        // Check if a ClientService already exists for this order_item_id
        $clientService = ClientService::where('order_item_id', $orderItem->id)->first();

        if ($clientService && $clientService->status === 'active') {
            Log::info("ProvisionClientServiceJob: Servicio ya activo (ID: {$clientService->id}) para OrderItem ID: {$orderItem->id}. Saliendo.");
            // Ensure orderItem->client_service_id is set if it somehow wasn't (data consistency check)
            if (is_null($orderItem->client_service_id) || $orderItem->client_service_id !== $clientService->id) {
                 $orderItem->client_service_id = $clientService->id;
                 $orderItem->saveQuietly(); // Avoid triggering observers if not needed
            }
            return;
        }

        DB::beginTransaction();
        try {
            if (!$clientService) {
                $clientService = new ClientService();
                $clientService->order_item_id = $orderItem->id;
                $clientService->client_id = $order->client_id;
                // Ensure client relation is loaded for reseller_id. The 'order.client' load above should handle this.
                $clientService->reseller_id = $order->client->reseller_id ?? null;
                $clientService->order_id = $order->id;
                $clientService->product_id = $orderItem->product_id;
                $clientService->product_pricing_id = $orderItem->product_pricing_id;
                $clientService->billing_cycle_id = $orderItem->productPricing->billingCycle->id;
                $clientService->domain_name = $orderItem->domain_name;
                $clientService->billing_amount = $orderItem->unit_price;
                $clientService->status = 'pending_configuration'; // Initial status before provisioning
            } else if ($clientService->status === 'provisioning_failed') {
                 Log::info("ProvisionClientServiceJob: Reintentando aprovisionamiento para ClientService ID: {$clientService->id} (OrderItem ID: {$orderItem->id}).");
            }


            // === INICIO LÓGICA DE APROVISIONAMIENTO REAL (SIMULADA) ===
            // Simulating some processing time
            // sleep(5);

            $clientService->username = 'user_' . strtolower(Str::random(6));
            // In a real app, ensure this is properly encrypted by model's mutator or here directly
            $clientService->password_encrypted = 'simulated_password_' . Str::random(10);

            // Set registration_date only if it's a new service or was not set
            if (empty($clientService->registration_date)) {
                $clientService->registration_date = Carbon::now();
            }

            $billingCycle = $orderItem->productPricing->billingCycle;
            // Use registration_date as the base for next_due_date calculation
            $currentRegistrationDate = Carbon::parse($clientService->registration_date);
            $nextDueDate = $currentRegistrationDate->copy();

            if ($billingCycle) {
                switch ($billingCycle->type) {
                    case 'day': $nextDueDate->addDays($billingCycle->multiplier); break;
                    case 'month': $nextDueDate->addMonthsNoOverflow($billingCycle->multiplier); break;
                    case 'year': $nextDueDate->addYearsNoOverflow($billingCycle->multiplier); break;
                    default:
                        Log::warning("ProvisionClientServiceJob: Ciclo de facturación desconocido '{$billingCycle->type}' para OrderItem ID: {$orderItem->id}. Usando 1 mes por defecto.");
                        $nextDueDate->addMonth();
                }
            } else {
                 Log::warning("ProvisionClientServiceJob: Ciclo de facturación no encontrado para OrderItem ID: {$orderItem->id}. Usando 1 mes por defecto para fecha de vencimiento.");
                $nextDueDate->addMonth();
            }
            $clientService->next_due_date = $nextDueDate->toDateString();
            $clientService->notes = ($clientService->notes ? $clientService->notes . "\n" : '') . "Servicio aprovisionado automáticamente por Job el " . Carbon::now()->toDateTimeString();
            // === FIN LÓGICA DE APROVISIONAMIENTO REAL (SIMULADA) ===

            $clientService->status = 'active'; // Mark as active
            $clientService->save(); // This will trigger ClientServiceObserver if status changed to 'active'

            // Ensure the original OrderItem is updated with the ClientService ID
            if (is_null($orderItem->client_service_id) || $orderItem->client_service_id !== $clientService->id) {
                 $orderItem->client_service_id = $clientService->id;
                 $orderItem->save(); // Use save() to trigger any OrderItem observers if necessary
            }

            DB::commit();
            Log::info("ProvisionClientServiceJob: Servicio ID {$clientService->id} aprovisionado y activado exitosamente para OrderItem ID: {$orderItem->id}.");

            // The ClientServiceObserver (if service status became 'active')
            // should handle updating the parent Order's status to 'active'.

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("ProvisionClientServiceJob: Fallo al aprovisionar OrderItem ID: {$orderItem->id}. Error: " . $e->getMessage(), [
                'exception_class' => get_class($e),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 500) // Log a snippet of the trace
            ]);

            if ($clientService && ($clientService->exists || $clientService->wasRecentlyCreated)) {
                // Attempt to save failure status without triggering observers again.
                $clientService->status = 'provisioning_failed'; // Ensure this status exists in ENUM
                $clientService->notes = ($clientService->notes ? $clientService->notes . "\n" : '') . "Fallo de aprovisionamiento automático: " . $e->getMessage();
                $clientService->saveQuietly();
            }

            // Re-throw the exception to let the queue worker handle it (e.g., mark as failed, retry)
            throw $e;
        }
    }
}
