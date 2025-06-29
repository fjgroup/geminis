<?php
namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\DiscountPercentage;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DiscountPercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los descuentos por ciclo
        $discountsByCycle = [
            1 => 0.00,  // Mensual - 0%
            2 => 5.00,  // Trimestral - 5%
            3 => 11.00, // Semestral - 11%
            4 => 18.00, // Anual - 18%
            5 => 26.00, // Bienal - 26%
            6 => 35.00, // Trienal - 35%
        ];

        // Productos ID 1, 2, 3
        $productIds = [1, 2, 3];

        // Obtener nombres de productos y ciclos para generar nombres descriptivos
        $products      = Product::whereIn('id', $productIds)->get();
        $billingCycles = BillingCycle::all();

        foreach ($productIds as $productId) {
            foreach ($discountsByCycle as $cycleId => $percentage) {
                $product = $products->find($productId);
                $cycle   = $billingCycles->find($cycleId);

                if ($product && $cycle) {
                    $name = "Descuento {$product->name} {$cycle->name}";
                    $slug = Str::slug($name);

                    DiscountPercentage::updateOrCreate(
                        [
                            'product_id'       => $productId,
                            'billing_cycle_id' => $cycleId,
                        ],
                        [
                            'name'        => $name,
                            'slug'        => $slug,
                            'description' => "Descuento del {$percentage}% para {$product->name} con facturación {$cycle->name}",
                            'percentage'  => $percentage,
                            'is_active'   => true,
                        ]
                    );

                    $this->command->info("✅ Creado: {$name} ({$percentage}%)");
                } else {
                    $this->command->warn("⚠️ No se encontró producto ID {$productId} o ciclo ID {$cycleId}");
                }
            }
        }

        $this->command->info('✅ Descuentos creados correctamente para productos 1, 2, 3 y todos los ciclos');
    }
}
