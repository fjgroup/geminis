<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'reseller_id' => null,
            'invoice_id' => null,
            'payment_method_id' => PaymentMethod::factory(),
            'gateway_slug' => $this->faker->randomElement(['paypal', 'stripe', 'manual', 'balance']),
            'gateway_transaction_id' => $this->faker->uuid(),
            'type' => $this->faker->randomElement(['payment', 'credit_added', 'refund']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency_code' => 'USD',
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'cancelled']),
            'description' => $this->faker->sentence(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'fees_amount' => $this->faker->randomFloat(2, 0, 50),
        ];
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the transaction is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the transaction is a payment.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
            'invoice_id' => Invoice::factory(),
        ]);
    }

    /**
     * Indicate that the transaction is a credit addition.
     */
    public function creditAddition(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit_added',
            'invoice_id' => null,
        ]);
    }

    /**
     * Indicate that the transaction is a refund.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'refund',
            'amount' => -abs($attributes['amount'] ?? $this->faker->randomFloat(2, 10, 1000)),
        ]);
    }

    /**
     * Create a PayPal transaction.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway_slug' => 'paypal',
            'gateway_transaction_id' => 'PAYPAL-' . $this->faker->uuid(),
        ]);
    }

    /**
     * Create a balance transaction.
     */
    public function balance(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway_slug' => 'balance',
            'gateway_transaction_id' => 'BAL-' . strtoupper($this->faker->lexify('??????????')),
        ]);
    }

    /**
     * Create a manual transaction.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway_slug' => 'manual',
            'gateway_transaction_id' => 'MAN-' . $this->faker->uuid(),
        ]);
    }
}
