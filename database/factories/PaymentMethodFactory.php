<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Bank Transfer', 'Credit Card', 'PayPal', 'Wire Transfer']),
            'slug' => $this->faker->unique()->slug(),
            'type' => $this->faker->randomElement(['manual', 'automatic']),
            'is_active' => true,
            'is_automatic' => $this->faker->boolean(),
            'display_order' => $this->faker->numberBetween(1, 10),
            'account_holder_name' => $this->faker->name(),
            'account_number' => $this->faker->bankAccountNumber(),
            'bank_name' => $this->faker->company(),
            'branch_name' => $this->faker->city(),
            'swift_code' => $this->faker->swiftBicNumber(),
            'iban' => $this->faker->iban(),
            'instructions' => $this->faker->paragraph(),
            'logo_url' => $this->faker->imageUrl(200, 100, 'business'),
        ];
    }

    /**
     * Indicate that the payment method is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the payment method is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the payment method is automatic.
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_automatic' => true,
            'type' => 'automatic',
        ]);
    }

    /**
     * Indicate that the payment method is manual.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_automatic' => false,
            'type' => 'manual',
        ]);
    }

    /**
     * Create a PayPal payment method.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PayPal',
            'slug' => 'paypal',
            'type' => 'automatic',
            'is_automatic' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Create a bank transfer payment method.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bank Transfer',
            'slug' => 'bank-transfer',
            'type' => 'manual',
            'is_automatic' => false,
            'is_active' => true,
        ]);
    }
}
