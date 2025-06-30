<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ResellerProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el tipo de producto Reseller
        $resellerType = ProductType::where('slug', 'reseller-hosting')->first();

        if (! $resellerType) {
            $this->command->error('❌ Tipo de producto Reseller no encontrado');
            return;
        }

        // Obtener ciclos de facturación
        $mensual = BillingCycle::where('slug', 'mensual')->first();
        $trimestral = BillingCycle::where('slug', 'trimestral')->first();
        $semestral = BillingCycle::where('slug', 'semestral')->first();
        $anual = BillingCycle::where('slug', 'anual')->first();
        $bienal = BillingCycle::where('slug', 'bienal')->first();
        $trienal = BillingCycle::where('slug', 'trienal')->first();

        if (!$mensual || !$trimestral || !$semestral || !$anual || !$bienal || !$trienal) {
            $this->command->error('❌ Ciclos de facturación no encontrados');
            return;
        }

        // Crear producto Reseller Básico
        $resellerBasico = Product::updateOrCreate(
            ['slug' => 'reseller-basico'],
            [
                'name'                     => 'Reseller Básico',
                'description'              => 'Plan básico de hosting reseller para emprendedores que desean iniciar su negocio de hosting',
                'module_name'              => 'whm',
                'owner_id'                 => null,
                'status'                   => 'active',
                'is_publicly_available'    => true,
                'is_resellable_by_default' => false, // Los planes reseller no se revenden
                'display_order'            => 1,
                'product_type_id'          => $resellerType->id,
                // Landing page
                'landing_page_slug'        => 'reseller-basico',
                'landing_page_description' => 'Perfecto para emprendedores que quieren iniciar su negocio de hosting',
                'features_list'            => [
                    '20 cuentas cPanel incluidas',
                    '50GB de espacio en disco SSD',
                    'Panel WHM (Web Host Manager)',
                    'Marca blanca completa',
                    'DNS privados personalizables',
                    'Certificados SSL gratuitos',
                    'Migración gratuita de clientes',
                    'Soporte técnico especializado',
                    'Copias de seguridad diarias',
                    'Softaculous incluido',
                ],
                'call_to_action_text'      => 'Comenzar Negocio',
                'auto_setup'               => false, // Requiere configuración manual
                'requires_approval'        => true,  // Requiere aprobación
                'setup_fee'                => 0.00,
                'track_stock'              => false,
            ]
        );

        // Precios para Reseller Básico (precio base: $25/mes)
        $this->createProductPricing($resellerBasico, $mensual, 25.00);
        $this->createProductPricing($resellerBasico, $trimestral, 71.25); // 5% descuento
        $this->createProductPricing($resellerBasico, $semestral, 133.50); // 11% descuento
        $this->createProductPricing($resellerBasico, $anual, 246.00);     // 18% descuento
        $this->createProductPricing($resellerBasico, $bienal, 444.00);    // 26% descuento
        $this->createProductPricing($resellerBasico, $trienal, 585.00);   // 35% descuento

        $this->command->info('✅ Producto Reseller Básico creado correctamente');
    }

    /**
     * Helper method to create product pricing
     */
    private function createProductPricing(Product $product, BillingCycle $billingCycle, float $price): void
    {
        ProductPricing::updateOrCreate(
            [
                'product_id' => $product->id,
                'billing_cycle_id' => $billingCycle->id,
            ],
            [
                'price' => $price,
                'setup_fee' => 0.00,
                'is_active' => true,
            ]
        );
    }
}
