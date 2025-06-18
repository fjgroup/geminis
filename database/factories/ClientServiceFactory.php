<?php

namespace Database\Factories;

use App\Models\ClientService;
use App\Models\User; // Assuming client is a User
// use App\Models\Order; // Removed
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientService>
 */
class ClientServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientService::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $registrationDate = Carbon::instance($this->faker->dateTimeThisYear());

        // Create product pricing, which also creates product and billing cycle
        $productPricing = ProductPricing::factory()->create();
        $billingCycle = $productPricing->billingCycle; // Get the associated billing cycle

        $nextDueDate = $registrationDate->copy();
        if ($billingCycle) {
            switch ($billingCycle->type) {
                case 'day':
                    $nextDueDate->addDays($billingCycle->multiplier);
                    break;
                case 'month':
                    $nextDueDate->addMonthsNoOverflow($billingCycle->multiplier);
                    break;
                case 'year':
                    $nextDueDate->addYearsNoOverflow($billingCycle->multiplier);
                    break;
                default:
                    $nextDueDate->addMonth(); // Default fallback
            }
        } else {
            $nextDueDate->addMonth(); // Default if no billing cycle somehow
        }

        return [
            'client_id' => User::factory(),
            'reseller_id' => null, // Or User::factory()->reseller() if you have a reseller state
            // 'order_id' => Order::factory(), // Removed
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration', 'provisioning_failed']),
            'registration_date' => $registrationDate->toDateString(),
            'next_due_date' => $nextDueDate->toDateString(),
            'termination_date' => null,
            'billing_amount' => $productPricing->price,
            'currency_code' => $productPricing->currency_code,
            'domain_name' => null, // Set specifically if product requires domain
            'username' => $this->faker->optional()->userName,
            'password_encrypted' => null, // Not setting by default
            'notes' => $this->faker->optional()->sentence,
            'server_id' => null, // If using servers
            'cancellation_reason' => null,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (ClientService $service) {
            // If the associated product requires a domain, generate one.
            if ($service->product && $service->product->productType && $service->product->productType->requires_domain && !$service->domain_name) {
                $service->domain_name = $this->faker->unique()->domainName;
                $service->save();
            }
            // Removed order consistency check block
        });
    }
}
