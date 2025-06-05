<?php

namespace Database\Factories;

use App\Models\User; // Assuming User model for clients
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $total_amount = $this->faker->randomFloat(2, 10, 1000);

        return [
            'client_id' => User::factory(),
            'invoice_id' => null, // Can be associated later or via a state
            'order_number' => strtoupper(Str::random(3) . '-' . $this->faker->unique()->numerify('######')),
            'order_date' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['pending_payment', 'paid_pending_execution', 'pending_provisioning', 'active', 'completed', 'cancelled', 'fraud']),
            'total_amount' => $total_amount,
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'notes' => $this->faker->optional()->paragraph,
            'ip_address' => $this->faker->optional()->ipv4,
            'reseller_id' => null, // Or associate with a User factory for resellers
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            // If an invoice_id is set, ensure the order's total amount matches the invoice's total.
            if ($order->invoice_id && $order->invoice) {
                $order->total_amount = $order->invoice->total_amount;
                $order->currency_code = $order->invoice->currency_code;
                $order->save();
            }
        });
    }

    /**
     * Indicate that the order is pending payment.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pendingPayment()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending_payment',
            ];
        });
    }

    /**
     * Indicate that the order is paid and pending execution.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paidPendingExecution()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid_pending_execution',
            ];
        });
    }

    /**
     * Indicate that the order is pending provisioning.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pendingProvisioning()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending_provisioning',
            ];
        });
    }

    /**
     * Create an invoice for the order.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withInvoice()
    {
        return $this->afterCreating(function (Order $order) {
            if (!$order->invoice_id) {
                $invoice = Invoice::factory()->create([
                    'client_id' => $order->client_id,
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'subtotal' => $order->total_amount,
                    'currency_code' => $order->currency_code,
                    'status' => 'unpaid', // Default for a new order's invoice
                ]);
                $order->invoice_id = $invoice->id;
                $order->save();
            }
        });
    }
}
