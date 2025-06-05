<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\OrderItem;
use App\Models\ClientService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 3);
        // Ensure product_pricing_id is fetched after product_id is potentially resolved by ProductPricingFactory
        $productPricing = ProductPricing::factory()->create(); // Creates associated Product and BillingCycle too

        $unit_price = $productPricing->price;
        $total_price = $unit_price * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'client_service_id' => null, // Can be associated later
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_price' => $total_price,
            'description' => $this->faker->optional()->sentence,
            'domain_name' => null, // Set specifically if product requires domain
            // 'provisioning_data' => null, // JSON field
            // 'status' => 'pending', // If items have their own status
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (OrderItem $item) {
            // If the associated product requires a domain, generate one.
            if ($item->product && $item->product->productType && $item->product->productType->requires_domain && !$item->domain_name) {
                $item->domain_name = $this->faker->unique()->domainName;
                $item->save();
            }
        });
    }
}
