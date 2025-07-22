<?php

namespace Database\Factories;

use App\Models\ClientService;
use App\Domains\Invoices\Models\Invoice;
use App\Domains\Invoices\Models\InvoiceItem;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'client_service_id' => null,
            'product_id' => Product::factory(),
            'product_pricing_id' => ProductPricing::factory(),
            'item_type' => $this->faker->randomElement(['new_service', 'renewal', 'upgrade', 'addon']),
            'description' => $this->faker->sentence(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'amount' => $this->faker->randomFloat(2, 10, 500),
        ];
    }

    /**
     * Indicate that the item is for a new service.
     */
    public function newService(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'new_service',
            'client_service_id' => ClientService::factory(),
        ]);
    }

    /**
     * Indicate that the item is for a renewal.
     */
    public function renewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'renewal',
            'client_service_id' => ClientService::factory(),
        ]);
    }

    /**
     * Indicate that the item is for an upgrade.
     */
    public function upgrade(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'upgrade',
            'client_service_id' => ClientService::factory(),
        ]);
    }

    /**
     * Indicate that the item is for web hosting.
     */
    public function webHosting(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'web-hosting',
            'description' => 'Web Hosting Service - ' . $this->faker->domainName(),
        ]);
    }

    /**
     * Indicate that the item is an addon.
     */
    public function addon(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'addon',
            'description' => 'Addon Service - ' . $this->faker->words(3, true),
        ]);
    }

    /**
     * Set a specific amount for the item.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Set a specific quantity for the item.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }
}
