<?php

namespace Database\Factories;

use App\Models\Client; // Assuming Client model alias for User
use App\Models\User;   // Or directly User model
use App\Models\Invoice;
// use App\Models\Order; // Removed
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
            // 'order_id' => null, // Removed
            'invoice_number' => strtoupper(Str::random(4) . '-' . $this->faker->unique()->randomNumber(5)),
            'issue_date' => $issue_date,
            'due_date' => (clone $issue_date)->modify("+{$due_date_offset} days"),
            'paid_date' => null,
            'status' => $this->faker->randomElement(['draft', 'unpaid', 'pending_confirmation', 'paid', 'pending_activation', 'active_service', 'overdue', 'cancelled', 'refunded', 'collections', 'failed_payment']),
            'subtotal' => $subtotal,
            'total_amount' => $total_amount,
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'notes_to_client' => $this->faker->optional()->sentence,
            'admin_notes' => $this->faker->optional()->paragraph,
            'payment_method_id' => null, // Or associate with a PaymentMethod factory
            'reseller_id' => null, // Or associate with a User factory for resellers
            'paypal_order_id' => null,
            'requested_date' => $this->faker->dateTimeThisYear(),
            'ip_address' => $this->faker->optional()->ipv4,
            'payment_gateway_slug' => $this->faker->optional()->randomElement(['paypal', 'stripe', 'manual_transfer']),
        ];
    }

    // configure() method removed as its only logic was Order-related.

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
