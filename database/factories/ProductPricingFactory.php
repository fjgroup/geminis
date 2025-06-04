<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\BillingCycle;
use App\Models\ProductPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductPricing>
 */
class ProductPricingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductPricing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'billing_cycle_id' => BillingCycle::factory(),
            'price' => $this->faker->randomFloat(2, 5, 200), // e.g., 5.00 to 200.00
            'setup_fee' => $this->faker->randomElement([0, $this->faker->randomFloat(2, 10, 50)]),
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'GBP']), // Add more as needed
            'is_active' => true,
            // 'display_name' => $this->faker->optional()->sentence(3), // If you add this field
            // 'discount_percentage' => $this->faker->optional()->randomFloat(2, 5, 50), // If you add this
            // 'is_recurring' => true, // If you add this
        ];
    }
}
