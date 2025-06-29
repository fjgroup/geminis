<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\ConfigurableOptionGroup;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductType;
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

        if (! $hostingType) {
            $this->command->error('❌ Tipo de producto Hosting no encontrado');
            return;
        }

        // Obtener ciclos de facturación
        $mensual    = BillingCycle::where('slug', 'monthly')->first();
        $trimestral = BillingCycle::where('slug', 'quarterly')->first();
        $semestral  = BillingCycle::where('slug', 'semi_annually')->first();
        $anual      = BillingCycle::where('slug', 'annually')->first();

        // Obtener grupos de opciones configurables
        $espacioGroup = ConfigurableOptionGroup::where('slug', 'espacio-disco')->first();
        $vcpuGroup    = ConfigurableOptionGroup::where('slug', 'vcpu')->first();
        $spamGroup    = ConfigurableOptionGroup::where('slug', 'seguridad-email')->first();

        // 1. Hosting Web Eco
        $hostingEco = Product::updateOrCreate(
            ['slug' => 'hosting-web-eco'],
            [
                'name'                     => 'Hosting Web Eco',
                'description'              => 'Plan básico de hosting web con recursos esenciales para sitios pequeños',
                'module_name'              => 'cpanel',
                'owner_id'                 => null,
                'status'                   => 'active',
                'is_publicly_available'    => true,
                'is_resellable_by_default' => true,
                'display_order'            => 2,
                'product_type_id'          => $hostingType->id,
                // Recursos base
                'base_disk_space_gb'       => 5.0,
                'base_vcpu_cores'          => 1,
                'base_ram_gb'              => 1.0,
                'base_bandwidth_gb'        => 100,
                'base_email_accounts'      => 10,
                'base_databases'           => 5,
                'base_domains'             => 1,
                'base_subdomains'          => 10,
                // Landing page
                'landing_page_slug'        => 'hosting-eco',
                'landing_page_description' => 'Perfecto para sitios web pequeños y blogs personales',
                'features_list'            => [
                    '5GB de espacio en disco',
                    '1 vCPU core',
                    '1GB de RAM',
                    '100GB de transferencia',
                    '10 cuentas de email',
                    '5 bases de datos MySQL',
                    'Panel de control cPanel',
                    'Certificado SSL gratuito',
                ],
                'call_to_action_text'      => 'Comenzar con Eco',
                'auto_setup'               => true,
                'requires_approval'        => false,
                'setup_fee'                => 0.00,
                'track_stock'              => false,
            ]
        );

        // Precios para Hosting Eco (precio base: $5/mes)
        $this->createProductPricing($hostingEco, $mensual, 5.00);
        $this->createProductPricing($hostingEco, $trimestral, 5.00); // Se aplicará descuento 5%
        $this->createProductPricing($hostingEco, $semestral, 5.00);  // Se aplicará descuento 11%
        $this->createProductPricing($hostingEco, $anual, 5.00);      // Se aplicará descuento 18%

        // 2. Hosting Web Pro (antes Plus)
        $hostingPlus = Product::updateOrCreate(
            ['slug' => 'hosting-web-pro'],
            [
                'name'                     => 'Hosting Web Pro',
                'description'              => 'Plan intermedio con más recursos y funcionalidades avanzadas',
                'module_name'              => 'cpanel',
                'owner_id'                 => null,
                'status'                   => 'active',
                'is_publicly_available'    => true,
                'is_resellable_by_default' => true,
                'display_order'            => 3,
                'product_type_id'          => $hostingType->id,
                // Recursos base
                'base_disk_space_gb'       => 15.0,
                'base_vcpu_cores'          => 2,
                'base_ram_gb'              => 2.0,
                'base_bandwidth_gb'        => 300,
                'base_email_accounts'      => 25,
                'base_databases'           => 15,
                'base_domains'             => 5,
                'base_subdomains'          => 25,
                // Landing page
                'landing_page_slug'        => 'hosting-pro',
                'landing_page_description' => 'Ideal para sitios web en crecimiento y pequeñas empresas',
                'features_list'            => [
                    '15GB de espacio en disco',
                    '2 vCPU cores',
                    '2GB de RAM',
                    '300GB de transferencia',
                    '25 cuentas de email',
                    '15 bases de datos MySQL',
                    '5 dominios adicionales',
                    'Panel de control cPanel',
                    'Certificado SSL gratuito',
                    'Backups diarios',
                ],
                'call_to_action_text'      => 'Elegir Pro',
                'auto_setup'               => true,
                'requires_approval'        => false,
                'setup_fee'                => 0.00,
                'track_stock'              => false,
            ]
        );

        // Precios para Hosting Pro (precio base: $12/mes)
        $this->createProductPricing($hostingPlus, $mensual, 12.00);
        $this->createProductPricing($hostingPlus, $trimestral, 12.00);
        $this->createProductPricing($hostingPlus, $semestral, 12.00);
        $this->createProductPricing($hostingPlus, $anual, 12.00);

        // 3. Hosting Web Ultra
        $hostingUltra = Product::updateOrCreate(
            ['slug' => 'hosting-web-ultra'],
            [
                'name'                     => 'Hosting Web Ultra',
                'description'              => 'Plan premium con recursos ilimitados y soporte prioritario',
                'module_name'              => 'cpanel',
                'owner_id'                 => null,
                'status'                   => 'active',
                'is_publicly_available'    => true,
                'is_resellable_by_default' => true,
                'display_order'            => 4,
                'product_type_id'          => $hostingType->id,
                'auto_setup'               => true,
                'requires_approval'        => false,
                'setup_fee'                => 0.00,
                'track_stock'              => false,
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
        $this->command->info('   - Hosting Web Pro: $12.00/mes (con descuentos por ciclo)');
        $this->command->info('   - Hosting Web Ultra: $25.00/mes (con descuentos por ciclo)');
        $this->command->info('   - Opciones configurables asociadas: Espacio, vCPU, SpamExperts');
    }

    /**
     * Crear pricing para un producto en un ciclo específico
     */
    private function createProductPricing($product, $billingCycle, $price)
    {
        if (! $billingCycle) {
            return;
        }

        ProductPricing::updateOrCreate(
            [
                'product_id'       => $product->id,
                'billing_cycle_id' => $billingCycle->id,
            ],
            [
                'price'         => $price,
                'setup_fee'     => 0.00,
                'currency_code' => 'USD',
                'is_active'     => true,
            ]
        );
    }
}
