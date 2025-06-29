<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenericDomainProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Buscar o crear el tipo de producto para dominios
            $domainProductType = ProductType::firstOrCreate(
                ['name' => 'Domain'],
                [
                    'slug'                     => 'domain',
                    'description'              => 'Productos de registro y gestión de dominios',
                    'requires_domain'          => false,
                    'creates_service_instance' => true,
                ]
            );

            // Buscar o crear el ciclo de facturación anual
            $annualBillingCycle = BillingCycle::firstOrCreate(
                ['name' => 'Anual'],
                [
                    'slug' => 'anual',
                    'days' => 365,
                ]
            );

            // Crear o actualizar el producto de dominio genérico con ID 1
            $domainProduct = Product::updateOrCreate(
                ['id' => 1],
                [
                    'name'                     => 'Registro de Dominio',
                    'slug'                     => 'domain-registration',
                    'description'              => 'Producto genérico para el registro de dominios. Los precios se establecen dinámicamente según el TLD.',

                    'module_name'              => 'domain',
                    'owner_id'                 => null, // Producto de plataforma
                    'status'                   => 'active',
                    'is_publicly_available'    => true,
                    'is_resellable_by_default' => true,
                    'display_order'            => 1,
                    'product_type_id'          => $domainProductType->id,
                ]
            );

            // Crear o actualizar el pricing del dominio genérico con ID 1
            $domainPricing = ProductPricing::updateOrCreate(
                ['id' => 1],
                [
                    'product_id'       => $domainProduct->id,
                    'billing_cycle_id' => $annualBillingCycle->id,
                    'price'            => 0.00, // Precio base, se sobrescribe dinámicamente
                    'setup_fee'        => 0.00,
                    'currency_code'    => 'USD',
                    'is_active'        => true,
                ]
            );

            // Actualizar las constantes en el ClientCartController si es necesario
            $this->updateCartControllerConstants();

            $this->command->info('✅ Producto de dominio genérico creado/actualizado:');
            $this->command->info("   - Product ID: {$domainProduct->id}");
            $this->command->info("   - ProductPricing ID: {$domainPricing->id}");
            $this->command->info("   - Product Type: {$domainProductType->name}");
            $this->command->info("   - Billing Cycle: {$annualBillingCycle->name}");
        });
    }

    /**
     * Actualizar las constantes en el código si es necesario
     */
    private function updateCartControllerConstants(): void
    {
        $controllerPath = app_path('Http/Controllers/Client/ClientCartController.php');

        if (file_exists($controllerPath)) {
            $content = file_get_contents($controllerPath);

            // Buscar y reemplazar las constantes si existen
            $patterns = [
                '/genericDomainProductId = 4/' => 'genericDomainProductId = 1',
                '/genericDomainPricingId = 4/' => 'genericDomainPricingId = 1', // Ambos son 1
                '/product_type_id.*?!=.*?3/'   => 'product_type_id != 1',       // Ajustar según el ID del tipo de dominio
            ];

            $updated = false;
            foreach ($patterns as $pattern => $replacement) {
                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $replacement, $content);
                    $updated = true;
                }
            }

            if ($updated) {
                file_put_contents($controllerPath, $content);
                $this->command->info('✅ Constantes actualizadas en ClientCartController');
            }
        }
    }
}
