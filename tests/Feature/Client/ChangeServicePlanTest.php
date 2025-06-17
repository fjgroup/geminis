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
use App\Models\ProductType; // Import ProductType

class ChangeServicePlanTest extends TestCase
{
    use RefreshDatabase;

    private function setup_test_environment(array $serviceStates = ['status' => 'Active'])
    {
        $user = User::factory()->create(['role' => 'client']);
        Client::factory()->create(['user_id' => $user->id, 'company_id' => $user->company_id]);

        $productTypeWeb = ProductType::factory()->create(['name' => 'Hosting Web']);
        $productTypeVps = ProductType::factory()->create(['name' => 'Servidor VPS']);

        $billingCycleMonthly = BillingCycle::factory()->create(['name' => 'Monthly', 'duration_in_months' => 1]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Yearly', 'duration_in_months' => 12]);

        // Product A (Web Hosting) - Service's initial product
        $productA_web = Product::factory()->create(['name' => 'Web Hosting Basic', 'product_type_id' => $productTypeWeb->id]);
        $pricingA_monthly = ProductPricing::factory()->create([
            'product_id' => $productA_web->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 10.00,
        ]);
        $pricingA_yearly = ProductPricing::factory()->create([ // Same product, different cycle
            'product_id' => $productA_web->id,
            'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 100.00,
        ]);

        // Product B (Web Hosting) - Different product, same type (valid upgrade/downgrade)
        $productB_web = Product::factory()->create(['name' => 'Web Hosting Pro', 'product_type_id' => $productTypeWeb->id]);
        $pricingB_monthly = ProductPricing::factory()->create([
            'product_id' => $productB_web->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 20.00,
        ]);

        // Product C (VPS) - Different product, different type (invalid upgrade/downgrade)
        $productC_vps = Product::factory()->create(['name' => 'VPS Starter', 'product_type_id' => $productTypeVps->id]);
        $pricingC_monthly = ProductPricing::factory()->create([
            'product_id' => $productC_vps->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 30.00,
        ]);

        $service = ClientService::factory()->create(array_merge([
            'client_id' => $user->client->id,
            'product_id' => $productA_web->id,
            'product_pricing_id' => $pricingA_monthly->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'status' => 'Active',
            'billing_amount' => $pricingA_monthly->price,
        ], $serviceStates));

        return compact(
            'user', 'service',
            'productTypeWeb', 'productTypeVps',
            'productA_web', 'pricingA_monthly', 'pricingA_yearly',
            'productB_web', 'pricingB_monthly',
            'productC_vps', 'pricingC_monthly'
        );
    }

    /** @test */
    public function client_can_change_service_plan_and_billing_cycle()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        // Change to yearly cycle of the same product (Product A)
        $newPricingSameProductDifferentCycle = $data['pricingA_yearly'];

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricingSameProductDifferentCycle->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertEquals($newPricingSameProductDifferentCycle->id, $service->product_pricing_id);
        $this->assertEquals($newPricingSameProductDifferentCycle->billing_cycle_id, $service->billing_cycle_id);
        $this->assertEquals($newPricingSameProductDifferentCycle->price, $service->billing_amount);

        // Test changing to a different product of the same type (Product B)
        $newPricingDifferentProductSameType = $data['pricingB_monthly'];
        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricingDifferentProductSameType->id,
                         ]);
        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $service->refresh();
        $this->assertEquals($newPricingDifferentProductSameType->id, $service->product_pricing_id);
        $this->assertEquals($newPricingDifferentProductSameType->product_id, $service->product_id);
    }

    /** @test */
    public function test_only_shows_plans_of_same_product_type_on_upgrade_page()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Service is for Product A (Web Hosting)

        $response = $this->actingAs($user)
                         ->get(route('client.services.showUpgradeDowngradeOptions', $service));

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->component('Client/Services/UpgradeDowngradeOptions')
            ->has('availableOptions')
            ->where('availableOptions', function ($options) use ($data) {
                $productAPricingIds = [$data['pricingA_monthly']->id, $data['pricingA_yearly']->id];
                $productBPricingIds = [$data['pricingB_monthly']->id];
                $productCPricingIds = [$data['pricingC_monthly']->id]; // VPS product pricing

                $optionIds = collect($options)->pluck('id')->all();

                // Check that all pricings for Product A (same type) are present
                foreach ($productAPricingIds as $id) {
                    if (!in_array($id, $optionIds)) return false;
                }
                // Check that all pricings for Product B (same type) are present
                foreach ($productBPricingIds as $id) {
                    if (!in_array($id, $optionIds)) return false;
                }
                // Check that no pricings for Product C (different type) are present
                foreach ($productCPricingIds as $id) {
                    if (in_array($id, $optionIds)) return false;
                }
                return true;
            }, 'Available options do not correctly filter by product type or are missing expected options.')
        );
    }

    /** @test */
    public function client_cannot_change_to_a_plan_of_a_different_product_type_via_post()
    {
        // This test ensures that even if a user tries to POST an ID of a ProductPricing
        // from a different product type (which shouldn't be shown), the backend validation
        // (if any on product_type_id, or indirectly via product_id check) might catch it.
        // Currently, processUpgradeDowngrade only checks if product_id is different, not product_type_id.
        // This test will behave like client_cannot_change_to_a_plan_of_a_different_product
        // if that product has a different product_id.
        // The primary protection is that these options are not shown.

        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Service is for Product A (Web Hosting)
        $pricingForDifferentProductType = $data['pricingC_monthly']; // This pricing is for Product C (VPS)

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $pricingForDifferentProductType->id,
                         ]);

        $response->assertRedirect(); // To previous page
        $response->assertSessionHas('error', 'Invalid plan selected. It does not belong to the same product.');

        $service->refresh();
        $this->assertNotEquals($pricingForDifferentProductType->id, $service->product_pricing_id);
    }


    /** @test */
    public function client_cannot_change_plan_if_service_is_not_active()
    {
        $data = $this->setup_test_environment(['status' => 'Terminated']);
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingA_yearly']; // A valid pricing option if service were active

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
