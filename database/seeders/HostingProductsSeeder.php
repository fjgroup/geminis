<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductType;
use App\Models\BillingCycle;
use App\Models\ConfigurableOptionGroup;
use Illuminate\Database\Seeder;

class HostingProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de producto Hosting
        $hostingType = ProductType::where('slug', 'web-hosting')->first();
        
        if (!$hostingType) {
            $this->command->error('❌ Tipo de producto Hosting no encontrado');
            return;
        }

        // Obtener ciclos de facturación
        $mensual = BillingCycle::where('slug', 'monthly')->first();
        $trimestral = BillingCycle::where('slug', 'quarterly')->first();
        $semestral = BillingCycle::where('slug', 'semi_annually')->first();
        $anual = BillingCycle::where('slug', 'annually')->first();

        // Obtener grupos de opciones configurables
        $espacioGroup = ConfigurableOptionGroup::where('slug', 'espacio-disco')->first();
        $vcpuGroup = ConfigurableOptionGroup::where('slug', 'vcpu')->first();
        $spamGroup = ConfigurableOptionGroup::where('slug', 'seguridad-email')->first();

        // 1. Hosting Web Eco
        $hostingEco = Product::updateOrCreate(
            ['slug' => 'hosting-web-eco'],
            [
                'name' => 'Hosting Web Eco',
                'description' => 'Plan básico de hosting web con recursos esenciales para sitios pequeños',
                'module_name' => 'cpanel',
                'owner_id' => null,
                'status' => 'active',
                'is_publicly_available' => true,
                'is_resellable_by_default' => true,
                'display_order' => 2,
                'product_type_id' => $hostingType->id,
                'auto_setup' => true,
                'requires_approval' => false,
                'setup_fee' => 0.00,
                'track_stock' => false,
            ]
        );

        // Precios para Hosting Eco (precio base: $5/mes)
        $this->createProductPricing($hostingEco, $mensual, 5.00);
        $this->createProductPricing($hostingEco, $trimestral, 5.00); // Se aplicará descuento 5%
        $this->createProductPricing($hostingEco, $semestral, 5.00); // Se aplicará descuento 11%
        $this->createProductPricing($hostingEco, $anual, 5.00); // Se aplicará descuento 18%

        // 2. Hosting Web Plus
        $hostingPlus = Product::updateOrCreate(
            ['slug' => 'hosting-web-plus'],
            [
                'name' => 'Hosting Web Plus',
                'description' => 'Plan intermedio con más recursos y funcionalidades avanzadas',
                'module_name' => 'cpanel',
                'owner_id' => null,
                'status' => 'active',
                'is_publicly_available' => true,
                'is_resellable_by_default' => true,
                'display_order' => 3,
                'product_type_id' => $hostingType->id,
                'auto_setup' => true,
                'requires_approval' => false,
                'setup_fee' => 0.00,
                'track_stock' => false,
            ]
        );

        // Precios para Hosting Plus (precio base: $12/mes)
        $this->createProductPricing($hostingPlus, $mensual, 12.00);
        $this->createProductPricing($hostingPlus, $trimestral, 12.00);
        $this->createProductPricing($hostingPlus, $semestral, 12.00);
        $this->createProductPricing($hostingPlus, $anual, 12.00);

        // 3. Hosting Web Ultra
        $hostingUltra = Product::updateOrCreate(
            ['slug' => 'hosting-web-ultra'],
            [
                'name' => 'Hosting Web Ultra',
                'description' => 'Plan premium con recursos ilimitados y soporte prioritario',
                'module_name' => 'cpanel',
                'owner_id' => null,
                'status' => 'active',
                'is_publicly_available' => true,
                'is_resellable_by_default' => true,
                'display_order' => 4,
                'product_type_id' => $hostingType->id,
                'auto_setup' => true,
                'requires_approval' => false,
                'setup_fee' => 0.00,
                'track_stock' => false,
            ]
        );

        // Precios para Hosting Ultra (precio base: $25/mes)
        $this->createProductPricing($hostingUltra, $mensual, 25.00);
        $this->createProductPricing($hostingUltra, $trimestral, 25.00);
        $this->createProductPricing($hostingUltra, $semestral, 25.00);
        $this->createProductPricing($hostingUltra, $anual, 25.00);

        // Asociar opciones configurables con todos los productos de hosting
        if ($espacioGroup && $vcpuGroup && $spamGroup) {
            $optionGroups = [$espacioGroup->id, $vcpuGroup->id, $spamGroup->id];
            
            $hostingEco->configurableOptionGroups()->syncWithoutDetaching($optionGroups);
            $hostingPlus->configurableOptionGroups()->syncWithoutDetaching($optionGroups);
            $hostingUltra->configurableOptionGroups()->syncWithoutDetaching($optionGroups);
        }

        $this->command->info('✅ Productos de hosting creados correctamente:');
        $this->command->info('   - Hosting Web Eco: $5.00/mes (con descuentos por ciclo)');
        $this->command->info('   - Hosting Web Plus: $12.00/mes (con descuentos por ciclo)');
        $this->command->info('   - Hosting Web Ultra: $25.00/mes (con descuentos por ciclo)');
        $this->command->info('   - Opciones configurables asociadas: Espacio, vCPU, SpamExperts');
    }

    /**
     * Crear pricing para un producto en un ciclo específico
     */
    private function createProductPricing($product, $billingCycle, $price)
    {
        if (!$billingCycle) return;

        ProductPricing::updateOrCreate(
            [
                'product_id' => $product->id,
                'billing_cycle_id' => $billingCycle->id,
            ],
            [
                'price' => $price,
                'setup_fee' => 0.00,
                'currency_code' => 'USD',
                'is_active' => true,
            ]
        );
    }
}
