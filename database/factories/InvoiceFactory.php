<?php

namespace Database\Factories;

use App\Models\Client; // Assuming Client model alias for User
use App\Models\User;   // Or directly User model
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 10, 1000);
        // For simplicity, total_amount is same as subtotal. Add tax/discount logic if needed.
        $total_amount = $subtotal;
        $issue_date = $this->faker->dateTimeBetween('-1 year', 'now');
        $due_date_offset = $this->faker->randomElement([7, 15, 30]); // days

        return [
            'client_id' => User::factory(), // Assumes User model is used for clients
            'order_id' => null, // Can be set specifically in tests
            'invoice_number' => strtoupper(Str::random(4) . '-' . $this->faker->unique()->randomNumber(5)),
            'issue_date' => $issue_date,
            'due_date' => (clone $issue_date)->modify("+{$due_date_offset} days"),
            'paid_date' => null,
            'status' => $this->faker->randomElement(['unpaid', 'paid', 'cancelled', 'refunded']),
            'subtotal' => $subtotal,
            'total_amount' => $total_amount,
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'notes' => $this->faker->optional()->sentence,
            'payment_method_id' => null, // Or associate with a PaymentMethod factory
            'reseller_id' => null, // Or associate with a User factory for resellers
            'paypal_order_id' => null,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Invoice $invoice) {
            // If an order_id is set, ensure the invoice's total amount matches the order's total.
            // This is a common scenario.
            if ($invoice->order_id && $invoice->order) {
                $invoice->total_amount = $invoice->order->total_amount;
                $invoice->subtotal = $invoice->order->total_amount; // Assuming no tax for simplicity
                $invoice->currency_code = $invoice->order->currency_code;
                $invoice->save();
            }
        });
    }

    /**
     * Indicate that the invoice is unpaid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unpaid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'unpaid',
                'paid_date' => null,
            ];
        });
    }

    /**
     * Indicate that the invoice is paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'paid_date' => $this->faker->dateTimeThisMonth(),
            ];
        });
    }
}
