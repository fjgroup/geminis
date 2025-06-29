<?php
namespace App\Console\Commands;

use App\Models\ClientService;
use App\Models\InvoiceItem;
use Illuminate\Console\Command;

class FixDomainPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:domain-pricing {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix domain pricing in client services using override_price from invoice items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🔍 Searching for domain services with incorrect pricing...');

        // Buscar servicios de dominio que tienen billing_amount = 0 o incorrecto
        $domainServices = ClientService::with(['product.productType', 'productPricing'])
            ->whereHas('product.productType', function ($query) {
                $query->where('slug', 'domain-registration');
            })
            ->where(function ($query) {
                $query->where('billing_amount', 0)
                    ->orWhere('billing_amount', '<=', 1); // Dominios muy baratos probablemente incorrectos
            })
            ->get();

        if ($domainServices->isEmpty()) {
            $this->info('✅ No domain services found with incorrect pricing.');
            return;
        }

        $this->info("📋 Found {$domainServices->count()} domain services with potentially incorrect pricing:");

        $updatedCount = 0;

        foreach ($domainServices as $service) {
            // Buscar el invoice item correspondiente para obtener el precio correcto
            $invoiceItem = InvoiceItem::whereHas('invoice', function ($query) use ($service) {
                $query->where('client_id', $service->client_id);
            })
                ->where('product_id', $service->product_id)
                ->where('description', 'LIKE', '%' . $service->domain_name . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($invoiceItem && $invoiceItem->unit_price > 0) {
                $currentPrice = $service->billing_amount;
                $correctPrice = $invoiceItem->unit_price;

                $this->line("🔧 Service ID: {$service->id} | Domain: {$service->domain_name}");
                $this->line("   Current: \${$currentPrice} → Correct: \${$correctPrice}");

                if (! $dryRun) {
                    $service->update(['billing_amount' => $correctPrice]);
                    $updatedCount++;
                    $this->info("   ✅ Updated!");
                } else {
                    $this->info("   📝 Would update (dry run)");
                }
            } else {
                $this->warn("⚠️  Service ID: {$service->id} | Domain: {$service->domain_name} - No invoice item found");
            }
        }

        if (! $dryRun) {
            $this->info("✅ Successfully updated {$updatedCount} domain services.");
        } else {
            $this->info("📝 Dry run completed. {$domainServices->count()} services would be processed.");
            $this->info("💡 Run without --dry-run to apply changes.");
        }
    }
}
