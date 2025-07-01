<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOptionPricing;
use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Database\Seeder;

class ConfigurableOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener productos de hosting
        $hostingEco   = Product::where('slug', 'hosting-web-eco')->first();
        $hostingPro   = Product::where('slug', 'hosting-web-pro')->first();
        $hostingUltra = Product::where('slug', 'hosting-web-ultra')->first();

        // Obtener ciclos de facturación
        $mensual    = BillingCycle::where('slug', 'monthly')->first();
        $trimestral = BillingCycle::where('slug', 'quarterly')->first();
        $semestral  = BillingCycle::where('slug', 'semi_annually')->first();
        $anual      = BillingCycle::where('slug', 'annually')->first();
        $bienal     = BillingCycle::where('slug', 'biennially')->first();
        $trienal    = BillingCycle::where('slug', 'triennially')->first();

        // 1. Grupo: Espacio para Crecer
        $espacioGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'espacio-disco'],
            [
                'name'          => '🚀 Espacio para Crecer',
                'description'   => 'Más espacio para que tu negocio crezca sin límites. Sube todas las fotos, videos y archivos que necesites.',
                'display_order' => 1,
                'is_active'     => true,
                'is_required'   => false,
            ]
        );

        // Opciones de espacio
        $espacioOption = ConfigurableOption::updateOrCreate(
            ['slug' => 'espacio-adicional'],
            [
                'group_id'      => $espacioGroup->id,
                'name'          => '📁 Espacio Extra',
                'value'         => 'espacio_gb',
                'description'   => 'Cada GB extra te permite subir más contenido, fotos de productos, videos promocionales y archivos importantes para hacer crecer tu negocio.',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 500,
                'display_order' => 1,
            ]
        );

        // Precios para espacio - Solo mensual activo (según requerimiento)
        $this->createOptionPricing($espacioOption, $hostingEco, $mensual, 0.50, true);
        $this->createOptionPricing($espacioOption, $hostingEco, $trimestral, 1.50, false); // Desactivado
        $this->createOptionPricing($espacioOption, $hostingEco, $semestral, 3.00, false);  // Desactivado
        $this->createOptionPricing($espacioOption, $hostingEco, $anual, 6.00, false);      // Desactivado
        $this->createOptionPricing($espacioOption, $hostingEco, $bienal, 12.00, false);    // Desactivado
        $this->createOptionPricing($espacioOption, $hostingEco, $trienal, 18.00, false);   // Desactivado

        // 2. Grupo: Potencia Turbo
        $vcpuGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'vcpu'],
            [
                'name'          => '⚡ Potencia Turbo',
                'description'   => 'Más velocidad para que tu sitio web cargue súper rápido y tus clientes no se vayan. ¡Convierte más visitas en ventas!',
                'display_order' => 2,
                'is_active'     => true,
                'is_required'   => false,
            ]
        );

        // Opciones de vCPU
        $vcpuOption = ConfigurableOption::updateOrCreate(
            ['slug' => 'vcpu-adicional'],
            [
                'group_id'      => $vcpuGroup->id,
                'name'          => '🔥 Núcleo Extra',
                'value'         => 'vcpu_cores',
                'description'   => 'Cada núcleo extra hace que tu sitio responda más rápido. Ideal para tiendas online, blogs con muchas visitas o sitios con videos.',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 15,
                'display_order' => 1,
            ]
        );

        // Precios para vCPU - Solo mensual activo
        $this->createOptionPricing($vcpuOption, $hostingEco, $mensual, 5.00, true);
        $this->createOptionPricing($vcpuOption, $hostingEco, $trimestral, 15.00, false); // Desactivado
        $this->createOptionPricing($vcpuOption, $hostingEco, $semestral, 30.00, false);  // Desactivado
        $this->createOptionPricing($vcpuOption, $hostingEco, $anual, 60.00, false);      // Desactivado
        $this->createOptionPricing($vcpuOption, $hostingEco, $bienal, 120.00, false);    // Desactivado
        $this->createOptionPricing($vcpuOption, $hostingEco, $trienal, 180.00, false);   // Desactivado

        // 3. Grupo: Memoria Inteligente
        $vramGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'vram'],
            [
                'name'          => '🧠 Memoria Inteligente',
                'description'   => 'Más memoria para que tu sitio maneje múltiples visitantes al mismo tiempo sin problemas. Perfecto para días de alta demanda.',
                'display_order' => 3,
                'is_active'     => true,
                'is_required'   => false,
            ]
        );

        // Opciones de vRAM
        $vramOption = ConfigurableOption::updateOrCreate(
            ['slug' => 'vram-adicional'],
            [
                'group_id'      => $vramGroup->id,
                'name'          => '💾 GB de Memoria',
                'value'         => 'vram_gb',
                'description'   => 'Cada GB extra permite que tu sitio funcione sin interrupciones, incluso cuando tienes muchos visitantes comprando al mismo tiempo.',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 15,
                'display_order' => 1,
            ]
        );

                                                                                        // Precios para vRAM - Solo mensual activo
        $this->createOptionPricing($vramOption, $hostingEco, $mensual, 1.00, true);     // $0.75 por GB
        $this->createOptionPricing($vramOption, $hostingEco, $trimestral, 3.00, false); // Desactivado
        $this->createOptionPricing($vramOption, $hostingEco, $semestral, 6.00, false);  // Desactivado
        $this->createOptionPricing($vramOption, $hostingEco, $anual, 12.00, false);     // Desactivado
        $this->createOptionPricing($vramOption, $hostingEco, $bienal, 24.00, false);    // Desactivado
        $this->createOptionPricing($vramOption, $hostingEco, $trienal, 36.00, false);   // Desactivado

        // 4. Grupo: Seguridad Email
        $spamGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'seguridad-email'],
            [
                'name'          => 'Seguridad Email',
                'description'   => 'Protección avanzada para correo electrónico',
                'display_order' => 4,
                'is_active'     => true,
                'is_required'   => false,
            ]
        );

        // Opciones de SpamExperts
        $spamOption = ConfigurableOption::updateOrCreate(
            ['slug' => 'spamexperts-email-security'],
            [
                'group_id'      => $spamGroup->id,
                'name'          => 'SpamExperts Email Security',
                'value'         => 'spamexperts_enabled',
                'description'   => 'Protección avanzada contra spam y malware',
                'option_type'   => 'checkbox',
                'is_required'   => false,
                'is_active'     => true,
                'display_order' => 1,
            ]
        );

        // Precios para SpamExperts - Solo mensual activo, desmarcado por defecto
        $this->createOptionPricing($spamOption, $hostingEco, $mensual, 3.00, true);
        $this->createOptionPricing($spamOption, $hostingEco, $trimestral, 9.00, false); // Desactivado
        $this->createOptionPricing($spamOption, $hostingEco, $semestral, 18.00, false); // Desactivado
        $this->createOptionPricing($spamOption, $hostingEco, $anual, 36.00, false);     // Desactivado
        $this->createOptionPricing($spamOption, $hostingEco, $bienal, 72.00, false);    // Desactivado
        $this->createOptionPricing($spamOption, $hostingEco, $trienal, 108.00, false);  // Desactivado

        // Asociar grupos con productos de hosting
        if ($hostingEco) {
            $hostingEco->configurableOptionGroups()->syncWithoutDetaching([
                $espacioGroup->id,
                $vcpuGroup->id,
                $spamGroup->id,
            ]);
        }

        if ($hostingPro) {
            $hostingPro->configurableOptionGroups()->syncWithoutDetaching([
                $espacioGroup->id,
                $vcpuGroup->id,
                $spamGroup->id,
            ]);
        }

        if ($hostingUltra) {
            $hostingUltra->configurableOptionGroups()->syncWithoutDetaching([
                $espacioGroup->id,
                $vcpuGroup->id,
                $spamGroup->id,
            ]);
        }

        $this->command->info('✅ Opciones configurables creadas correctamente');
        $this->command->info('   - Espacio en Disco: $0.50/GB/mes');
        $this->command->info('   - vCPU: $6.00/vCPU/mes');
        $this->command->info('   - SpamExperts: $3.00/mes');
    }

    /**
     * Crear pricing para una opción en un ciclo específico
     */
    private function createOptionPricing($option, $product, $billingCycle, $price, $isActive = true)
    {
        if (! $billingCycle || ! $product) {
            return;
        }

        // Buscar el ProductPricing específico para este producto y ciclo
        $productPricing = ProductPricing::where('product_id', $product->id)
            ->where('billing_cycle_id', $billingCycle->id)
            ->first();

        if (! $productPricing) {
            $this->command->warn("⚠️ No se encontró ProductPricing para el producto {$product->name} y el ciclo {$billingCycle->name}");
            return;
        }

        ConfigurableOptionPricing::updateOrCreate(
            [
                'configurable_option_id' => $option->id,
                'billing_cycle_id'       => $productPricing->billing_cycle_id,
            ],
            [
                'price'         => $price,
                'setup_fee'     => 0.00,
                'currency_code' => 'USD',
                'is_active'     => $isActive,
            ]
        );
    }
}
