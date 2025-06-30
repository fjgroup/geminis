<?php

namespace App\Console\Commands;

use App\Models\ClientService;
use App\Services\PricingCalculatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateServiceBillingAmounts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'services:update-billing-amounts {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Update billing amounts for existing services to include base resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🔄 Updating service billing amounts...');

        $services = ClientService::with(['product', 'productPricing.billingCycle'])
            ->whereHas('product')
            ->whereHas('productPricing')
            ->get();

        $this->info("📊 Found {$services->count()} services to process");

        $pricingCalculator = app(PricingCalculatorService::class);
        $updated = 0;
        $errors = 0;

        foreach ($services as $service) {
            try {
                // Calculate correct price using PricingCalculatorService
                $priceCalculation = $pricingCalculator->calculateProductPrice(
                    $service->product_id,
                    $service->billing_cycle_id,
                    [] // No configurable options for existing services
                );

                $newBillingAmount = $priceCalculation['total'];
                $oldBillingAmount = $service->billing_amount;

                if (abs($newBillingAmount - $oldBillingAmount) > 0.01) {
                    $this->line("📦 Service ID {$service->id} ({$service->product->name}):");
                    $this->line("   Old: ${$oldBillingAmount} → New: ${$newBillingAmount}");
                    
                    if (!$dryRun) {
                        $service->billing_amount = $newBillingAmount;
                        $service->save();
                    }
                    
                    $updated++;
                } else {
                    $this->line("✅ Service ID {$service->id} already has correct billing amount");
                }

            } catch (\Exception $e) {
                $this->error("❌ Error updating service ID {$service->id}: " . $e->getMessage());
                Log::error("Error updating service billing amount", [
                    'service_id' => $service->id,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }

        $this->info("\n📈 Summary:");
        $this->info("✅ Services updated: {$updated}");
        $this->info("❌ Errors: {$errors}");
        
        if ($dryRun) {
            $this->info("🔍 This was a dry run. Run without --dry-run to apply changes.");
        } else {
            $this->info("✅ Billing amounts updated successfully!");
        }

        return 0;
    }
}
