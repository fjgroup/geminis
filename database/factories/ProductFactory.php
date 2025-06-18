<?php

namespace Database\Factories;

use App\Models\ProductType; // Import ProductType
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Attempt to get a random ProductType id
        // In a real application, ensure ProductTypes are seeded before products or handle this more robustly
        $productTypeId = null;
        if (ProductType::count() > 0) {
            $productTypeId = ProductType::inRandomOrder()->first()->id;
        }

        return [
            'name' => $this->faker->words(3, true), // Example: "Fast Web Hosting"
            'description' => $this->faker->sentence,
            'product_type_id' => $productTypeId, // Assign a product_type_id
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'created_at' => now(),
            'updated_at' => now(),
            // Do NOT include the old 'type' enum field here
        ];
    }
}
