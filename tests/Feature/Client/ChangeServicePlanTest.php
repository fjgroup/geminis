<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientService;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;

class ChangeServicePlanTest extends TestCase
{
    use RefreshDatabase;

    private function setup_test_environment(array $serviceStates = ['status' => 'Active'])
    {
        $user = User::factory()->create(['role' => 'client']);
        Client::factory()->create(['user_id' => $user->id, 'company_id' => $user->company_id]);

        $product = Product::factory()->create(['name' => 'Test Product A']);

        $billingCycleMonthly = BillingCycle::factory()->create(['name' => 'Monthly', 'duration_in_months' => 1]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Yearly', 'duration_in_months' => 12]);

        $pricingMonthly = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 10.00,
            'currency_code' => 'USD',
        ]);

        $pricingYearly = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 100.00, // Assuming yearly is cheaper per month equivalent
            'currency_code' => 'USD',
        ]);

        $productB = Product::factory()->create(['name' => 'Test Product B']);
        $pricingProductB = ProductPricing::factory()->create([
            'product_id' => $productB->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 15.00,
            'currency_code' => 'USD',
        ]);


        $service = ClientService::factory()->create(array_merge([
            'client_id' => $user->client->id,
            'product_id' => $product->id,
            'product_pricing_id' => $pricingMonthly->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'status' => 'Active', // Default, can be overridden by $serviceStates
            'billing_amount' => $pricingMonthly->price,
        ], $serviceStates));

        return compact('user', 'product', 'pricingMonthly', 'pricingYearly', 'service', 'pricingProductB');
    }

    /** @test */
    public function client_can_change_service_plan_and_billing_cycle()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingYearly'];

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->billing_cycle_id, $service->billing_cycle_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
    }

    /** @test */
    public function client_cannot_change_to_a_plan_of_a_different_product()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $pricingForDifferentProduct = $data['pricingProductB']; // This pricing is for Product B

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $pricingForDifferentProduct->id,
                         ]);

        // Expect a redirect back with an error, or a 422 if validation stops it earlier
        // Based on current controller, it's a redirect back with error.
        $response->assertRedirect(); // To previous page
        $response->assertSessionHas('error');

        $service->refresh();
        $this->assertNotEquals($pricingForDifferentProduct->id, $service->product_pricing_id);
    }

    /** @test */
    public function client_cannot_change_plan_if_service_is_not_active()
    {
        $data = $this->setup_test_environment(['status' => 'Terminated']); // Service is Terminated
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingYearly'];

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $service->refresh();
        $this->assertNotEquals($newPricing->id, $service->product_pricing_id);
    }

    /** @test */
    public function client_cannot_change_to_the_same_plan()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $currentPricing = $data['pricingMonthly']; // This is the service's current plan

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $currentPricing->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('error'); // Controller should prevent this
        // Add specific message check if desired: ->assertSessionHas('error', 'This is already your current plan.');
    }
}
?>
