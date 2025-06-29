<?php
namespace Database\Seeders;

use App\Models\ConfigurableOptionGroup;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductConfigurableOptionGroupSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Obtener productos y grupos de opciones
        $products     = Product::all();
        $optionGroups = ConfigurableOptionGroup::all();

        // Configuraciones para cada producto (solo grupos que realmente existen)
        $productConfigurations = [
            // Hosting Web Eco (ID 1)
            1 => [
                'Espacio en Disco' => ['base_quantity' => 5, 'display_order' => 1, 'is_required' => true],
                'vCPU'             => ['base_quantity' => 1, 'display_order' => 2, 'is_required' => true],
                'vRam'             => ['base_quantity' => 1, 'display_order' => 3, 'is_required' => true],
                'Seguridad Email'  => ['base_quantity' => 0, 'display_order' => 4, 'is_required' => false],
            ],
            // Hosting Web Pro (ID 2)
            2 => [
                'Espacio en Disco' => ['base_quantity' => 15, 'display_order' => 1, 'is_required' => true],
                'vCPU'             => ['base_quantity' => 2, 'display_order' => 2, 'is_required' => true],
                'vRam'             => ['base_quantity' => 2, 'display_order' => 3, 'is_required' => true],
                'Seguridad Email'  => ['base_quantity' => 0, 'display_order' => 4, 'is_required' => false],
            ],
            // Hosting Web Ultra (ID 3)
            3 => [
                'Espacio en Disco' => ['base_quantity' => 50, 'display_order' => 1, 'is_required' => true],
                'vCPU'             => ['base_quantity' => 4, 'display_order' => 2, 'is_required' => true],
                'vRam'             => ['base_quantity' => 4, 'display_order' => 3, 'is_required' => true],
                'Seguridad Email'  => ['base_quantity' => 0, 'display_order' => 4, 'is_required' => false],
            ],
        ];

        foreach ($productConfigurations as $productId => $configurations) {
            $product = $products->find($productId);

            if (! $product) {
                $this->command->warn("⚠️ Producto con ID {$productId} no encontrado");
                continue;
            }

            foreach ($configurations as $groupName => $config) {
                $optionGroup = $optionGroups->where('name', $groupName)->first();

                if (! $optionGroup) {
                    $this->command->warn("⚠️ Grupo de opciones '{$groupName}' no encontrado");
                    continue;
                }

                // Verificar si la relación ya existe
                if (! $product->configurableOptionGroups()->where('configurable_option_group_id', $optionGroup->id)->exists()) {
                    $product->configurableOptionGroups()->attach($optionGroup->id, [
                        'base_quantity' => $config['base_quantity'],
                        'display_order' => $config['display_order'],
                        'is_required'   => $config['is_required'],
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $this->command->info("✅ Asociado: {$product->name} -> {$groupName} (base: {$config['base_quantity']})");
                } else {
                    $this->command->info("ℹ️ Ya existe: {$product->name} -> {$groupName}");
                }
            }
        }

        $this->command->info('✅ Relaciones producto-grupos de opciones creadas correctamente');
    }
}
