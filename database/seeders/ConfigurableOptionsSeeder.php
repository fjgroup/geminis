<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOptionPricing;
use App\Models\Product;
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

        // 1. Grupo: Espacio en Disco
        $espacioGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'espacio-disco'],
            [
                'name'          => 'Espacio en Disco',
                'description'   => 'Espacio adicional en disco para hosting',
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
                'name'          => 'Espacio Adicional',
                'value'         => 'espacio_gb',
                'description'   => 'Espacio adicional en GB',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 500,
                'display_order' => 1,
            ]
        );

        // Precios para espacio - Solo mensual activo (según requerimiento)
        $this->createOptionPricing($espacioOption, $mensual, 0.50, true);
        $this->createOptionPricing($espacioOption, $trimestral, 1.50, false); // Desactivado
        $this->createOptionPricing($espacioOption, $semestral, 3.00, false);  // Desactivado
        $this->createOptionPricing($espacioOption, $anual, 6.00, false);     // Desactivado
        $this->createOptionPricing($espacioOption, $bienal, 12.00, false);    // Desactivado
        $this->createOptionPricing($espacioOption, $trienal, 18.00, false);   // Desactivado

        // 2. Grupo: vCPU
        $vcpuGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'vcpu'],
            [
                'name'          => 'vCPU',
                'description'   => 'Núcleos de CPU virtuales adicionales',
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
                'name'          => 'vCPU Adicional',
                'value'         => 'vcpu_cores',
                'description'   => 'Núcleos de CPU adicionales',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 15,
                'display_order' => 1,
            ]
        );

        // Precios para vCPU - Solo mensual activo
        $this->createOptionPricing($vcpuOption, $mensual, 5.00, true);
        $this->createOptionPricing($vcpuOption, $trimestral, 15.00, false); // Desactivado
        $this->createOptionPricing($vcpuOption, $semestral, 30.00, false);  // Desactivado
        $this->createOptionPricing($vcpuOption, $anual, 60.00, false);     // Desactivado
        $this->createOptionPricing($vcpuOption, $bienal, 120.00, false);    // Desactivado
        $this->createOptionPricing($vcpuOption, $trienal, 180.00, false);   // Desactivado

        // 3. Grupo: vRAM (Memoria RAM)
        $vramGroup = ConfigurableOptionGroup::updateOrCreate(
            ['slug' => 'vram'],
            [
                'name'          => 'vRam',
                'description'   => 'Memoria Ram',
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
                'name'          => 'vRAM Adicional',
                'value'         => 'vram_gb',
                'description'   => 'Memoria RAM adicional en GB',
                'option_type'   => 'quantity',
                'is_required'   => false,
                'is_active'     => true,
                'min_value'     => 1,
                'max_value'     => 15,
                'display_order' => 1,
            ]
        );

                                                                           // Precios para vRAM - Solo mensual activo
        $this->createOptionPricing($vramOption, $mensual, 1.00, true);     // $0.75 por GB
        $this->createOptionPricing($vramOption, $trimestral, 3.00, false); // Desactivado
        $this->createOptionPricing($vramOption, $semestral, 6.00, false);  // Desactivado
        $this->createOptionPricing($vramOption, $anual, 12.00, false);      // Desactivado
        $this->createOptionPricing($vramOption, $bienal, 24.00, false);    // Desactivado
        $this->createOptionPricing($vramOption, $trienal, 36.00, false);   // Desactivado

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
        $this->createOptionPricing($spamOption, $mensual, 3.00, true);
        $this->createOptionPricing($spamOption, $trimestral, 9.00, false); // Desactivado
        $this->createOptionPricing($spamOption, $semestral, 18.00, false);  // Desactivado
        $this->createOptionPricing($spamOption, $anual, 36.00, false);     // Desactivado
        $this->createOptionPricing($spamOption, $bienal, 72.00, false);    // Desactivado
        $this->createOptionPricing($spamOption, $trienal, 108.00, false);   // Desactivado

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
    private function createOptionPricing($option, $billingCycle, $price, $isActive = true)
    {
        if (! $billingCycle) {
            return;
        }

        ConfigurableOptionPricing::updateOrCreate(
            [
                'configurable_option_id' => $option->id,
                'billing_cycle_id'       => $billingCycle->id,
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
