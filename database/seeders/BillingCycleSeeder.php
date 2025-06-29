<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\DiscountPercentage;
use Illuminate\Database\Seeder;

class BillingCycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener descuentos (deben existir primero)
        $sinDescuento        = DiscountPercentage::where('slug', 'sin-descuento')->first();
        $descuentoBasico     = DiscountPercentage::where('slug', 'hosting-basico')->first();
        $descuentoIntermedio = DiscountPercentage::where('slug', 'hosting-intermedio')->first();
        $descuentoPremium    = DiscountPercentage::where('slug', 'hosting-premium')->first();
        $descuentoBianual    = DiscountPercentage::where('slug', 'hosting-bianual')->first();
        $descuentoTrianual   = DiscountPercentage::where('slug', 'hosting-trianual')->first();

        $cycles = [
            [
                'name'                   => 'Mensual',
                'slug'                   => 'monthly',
                'days'                   => 30,

            ],
            [
                'name'                   => 'Trimestral',
                'slug'                   => 'quarterly',
                'days'                   => 91,

            ],
            [
                'name'                   => 'Semestral',
                'slug'                   => 'semi_annually',
                'days'                   => 182,

            ],
            [
                'name'                   => 'Anual',
                'slug'                   => 'annually',
                'days'                   => 365,

            ],
            [
                'name'                   => 'Bienal',
                'slug'                   => 'biennially',
                'days'                   => 730,

            ],
            [
                'name'                   => 'Trienal',
                'slug'                   => 'triennially',
                'days'                   => 1095,

            ],
            [
                'name'                   => 'Única vez',
                'slug'                   => 'one_time',
                'days'                   => 0,

            ],
        ];

        foreach ($cycles as $cycle) {
            BillingCycle::updateOrCreate(
                ['slug' => $cycle['slug']],
                $cycle
            );
        }

        $this->command->info('✅ Ciclos de facturación con descuentos creados correctamente');
    }
}
