<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product; // Required for product name
use App\Models\BillingCycle; // Required for billing cycle name
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRenewalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-renewals {--days=15 : Number of days before due date to generate renewal invoice} {--dry-run : If enabled, no invoices will be actually created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates renewal invoices for active client services that are approaching their next due date.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('GenerateRenewalInvoices command started.');
        $this->info('GenerateRenewalInvoices command started...');

        $daysBeforeDueDate = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run mode enabled. No invoices will be created.');
            Log::info('GenerateRenewalInvoices running in dry-run mode.');
        }

        // Calculate the target date for services needing renewal
        $targetRenewalDate = Carbon::now()->addDays($daysBeforeDueDate)->toDateString();

        // Fetch active services due for renewal
        // Eager load necessary relationships to avoid N+1 query problems.
        $servicesToRenew = ClientService::with([
                'product:id,name', // Select only necessary fields from product
                'productPricing.billingCycle:id,name', // Select only necessary fields
                'client:id,currency_code,reseller_id', // Select only necessary fields
                'renewalInvoices' => function ($query) {
                    // This sub-query loads existing unpaid/pending renewal invoices for each service
                    $query->whereIn('status', ['unpaid', 'pending_confirmation', 'draft', 'overdue'])
                          ->whereHas('items', function($itemQuery) {
                              $itemQuery->where('item_type', 'renewal');
                          });
                }
            ])
            ->where('status', 'active') // Only active services
            ->whereDate('next_due_date', '<=', $targetRenewalDate) // Due on or before the target date
            ->whereDate('next_due_date', '>', Carbon::now()->toDateString()) // Not already past due for this run
            ->get();

        if ($servicesToRenew->isEmpty()) {
            $this->info('No services due for renewal within the next ' . $daysBeforeDueDate . ' days.');
            Log::info('No services due for renewal within the next ' . $daysBeforeDueDate . ' days.');
            return Command::SUCCESS; // Use Command constants for return codes
        }

        $this->info("Found {$servicesToRenew->count()} services to process for renewal.");
        Log::info("Found {$servicesToRenew->count()} services to process for renewal.");

        foreach ($servicesToRenew as $service) {
            $this->line("Processing service ID: {$service->id} " . ($service->domain_name ? "({$service->domain_name})" : "Product ID: {$service->product_id}"));

            // Check if a relevant unpaid/pending renewal invoice already exists for this specific service
            $existingUnpaidRenewalInvoice = $service->renewalInvoices
                ->where('client_id', $service->client_id) // Double check client_id consistency
                ->first(function ($invoice) use ($service) {
                    // Ensure the invoice item is indeed for this client_service_id
                    return $invoice->items->contains(function ($item) use ($service) {
                        return $item->client_service_id === $service->id && $item->item_type === 'renewal';
                    });
                });

            if ($existingUnpaidRenewalInvoice) {
                $this->warn("Service ID: {$service->id} already has a relevant existing invoice (ID: {$existingUnpaidRenewalInvoice->id}, Status: {$existingUnpaidRenewalInvoice->status}). Skipping.");
                Log::info("Service ID: {$service->id} already has a relevant existing invoice (ID: {$existingUnpaidRenewalInvoice->id}, Status: {$existingUnpaidRenewalInvoice->status}). Skipping.");
                continue;
            }

            if ($dryRun) {
                $this->info("[DRY RUN] Would create renewal invoice for service ID: {$service->id}");
                Log::info("[DRY RUN] Would create renewal invoice for service ID: {$service->id}");
                continue;
            }

            DB::beginTransaction();
            try {
                $productName = $service->product ? $service->product->name : 'N/A';
                $billingCycleName = $service->productPricing && $service->productPricing->billingCycle ? $service->productPricing->billingCycle->name : 'N/A';
                // Determine currency, defaulting to USD if not set on client
                $currency = $service->client && !empty($service->client->currency_code) ? $service->client->currency_code : 'USD';

                // Create the Invoice
                $invoice = Invoice::create([
                    'client_id' => $service->client_id,
                    'reseller_id' => $service->client ? $service->client->reseller_id : null,
                    'invoice_number' => 'INV-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid()), // Consider a more robust unique ID generation
                    'issue_date' => Carbon::now()->toDateString(),
                    'due_date' => $service->next_due_date,
                    'status' => 'unpaid', // Initial status
                    'subtotal' => $service->billing_amount,
                    'total_amount' => $service->billing_amount, // Assuming no complex tax calculations for now
                    'currency_code' => $currency,
                    'notes_to_client' => "Renovación del servicio: {$productName} " . ($service->domain_name ? "({$service->domain_name})" : ""),
                    'admin_notes' => "Factura de renovación generada automáticamente para el servicio ID: {$service->id} por el comando invoices:generate-renewals.",
                    'requested_date' => Carbon::now(), // Date of request/generation
                    // 'payment_gateway_slug' => null, // Or set a default if applicable
                ]);

                // Create the InvoiceItem
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'client_service_id' => $service->id, // Link to the client service being renewed
                    'product_id' => $service->product_id,
                    'description' => "Renovación: {$productName} " . ($service->domain_name ? "({$service->domain_name})" : "") . " - Ciclo: {$billingCycleName}",
                    'quantity' => 1,
                    'unit_price' => $service->billing_amount,
                    'total_price' => $service->billing_amount,
                    'item_type' => 'renewal', // Crucial for identifying this as a renewal item
                ]);

                DB::commit();
                $this->info("Created renewal invoice ID: {$invoice->id} for service ID: {$service->id}");
                Log::info("Created renewal invoice ID: {$invoice->id} for service ID: {$service->id}");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to create renewal invoice for service ID: {$service->id}. Error: " . $e->getMessage());
                Log::error("Failed to create renewal invoice for service ID: {$service->id}. Error: " . $e->getMessage(), [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info('GenerateRenewalInvoices command finished.');
        Log::info('GenerateRenewalInvoices command finished.');
        return Command::SUCCESS;
    }
}
