<?php

namespace Database\Seeders;

use App\Models\DiscountPercentage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiscountPercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'Sin Descuento',
                'slug' => 'sin-descuento',
                'description' => 'No aplica descuento',
                'percentage' => 0.00,
                'applicable_product_types' => null, // Aplica a todos
            ],
            [
                'name' => 'Descuento Hosting Básico',
                'slug' => 'hosting-basico',
                'description' => 'Descuentos para productos de hosting básico',
                'percentage' => 5.00, // 5% trimestral
                'applicable_product_types' => ['Hosting', 'VPS'],
            ],
            [
                'name' => 'Descuento Hosting Intermedio',
                'slug' => 'hosting-intermedio',
                'description' => 'Descuentos para productos de hosting intermedio',
                'percentage' => 11.00, // 11% semestral
                'applicable_product_types' => ['Hosting', 'VPS'],
            ],
            [
                'name' => 'Descuento Hosting Premium',
                'slug' => 'hosting-premium',
                'description' => 'Descuentos para productos de hosting premium',
                'percentage' => 18.00, // 18% anual
                'applicable_product_types' => ['Hosting', 'VPS'],
            ],
            [
                'name' => 'Descuento Hosting Bianual',
                'slug' => 'hosting-bianual',
                'description' => 'Descuentos para productos de hosting bianual',
                'percentage' => 26.00, // 26% bianual
                'applicable_product_types' => ['Hosting', 'VPS'],
            ],
            [
                'name' => 'Descuento Hosting Trianual',
                'slug' => 'hosting-trianual',
                'description' => 'Descuentos para productos de hosting trianual',
                'percentage' => 35.00, // 35% trianual
                'applicable_product_types' => ['Hosting', 'VPS'],
            ],
        ];

        foreach ($discounts as $discount) {
            DiscountPercentage::updateOrCreate(
                ['slug' => $discount['slug']],
                $discount
            );
        }

        $this->command->info('✅ Descuentos creados correctamente');
    }
}
