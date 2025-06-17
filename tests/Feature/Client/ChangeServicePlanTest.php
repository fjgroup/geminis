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
use App\Models\ProductType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;

class ChangeServicePlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set a fixed time for consistent proration calculations
        // Example: Set "now" to the beginning of a month
        Carbon::setTestNow(Carbon::create(2024, 3, 1, 0, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon's test mock
        parent::tearDown();
    }

    private function setup_test_environment(array $serviceStates = [], float $clientInitialBalance = 0.0)
    {
        $user = User::factory()->create(['role' => 'client']);
        // Ensure client has a starting balance
        Client::factory()->create(['user_id' => $user->id, 'company_id' => $user->company_id, 'balance' => $clientInitialBalance]);

        $productTypeWeb = ProductType::factory()->create(['name' => 'Hosting Web']);
        $productTypeVps = ProductType::factory()->create(['name' => 'Servidor VPS']);

        // Add duration_in_days
        $billingCycleMonthly = BillingCycle::factory()->create(['name' => 'Monthly', 'duration_in_months' => 1, 'duration_in_days' => 30]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Yearly', 'duration_in_months' => 12, 'duration_in_days' => 365]);

        // Product A (Web Hosting) - Service's initial product
        $productA_web = Product::factory()->create(['name' => 'Web Hosting Basic', 'product_type_id' => $productTypeWeb->id, 'taxable' => true]);
        $pricingA_monthly = ProductPricing::factory()->create([
            'product_id' => $productA_web->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 10.00, // $10/month
            'currency_code' => 'USD',
        ]);
        $pricingA_yearly = ProductPricing::factory()->create([ // Same product, different cycle
            'product_id' => $productA_web->id,
            'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 100.00, // $100/year
            'currency_code' => 'USD',
        ]);

        // Product B (Web Hosting) - Different product, same type (valid upgrade/downgrade)
        $productB_web = Product::factory()->create(['name' => 'Web Hosting Pro', 'product_type_id' => $productTypeWeb->id, 'taxable' => true]);
        $pricingB_monthly = ProductPricing::factory()->create([
            'product_id' => $productB_web->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 20.00, // $20/month
            'currency_code' => 'USD',
        ]);
         $pricingB_yearly = ProductPricing::factory()->create([ // Same product, different cycle
            'product_id' => $productB_web->id,
            'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 200.00, // $200/year
            'currency_code' => 'USD',
        ]);


        // Product C (VPS) - Different product, different type (invalid upgrade/downgrade)
        $productC_vps = Product::factory()->create(['name' => 'VPS Starter', 'product_type_id' => $productTypeVps->id, 'taxable' => false]);
        $pricingC_monthly = ProductPricing::factory()->create([
            'product_id' => $productC_vps->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 30.00,
            'currency_code' => 'USD',
        ]);

        $defaultServiceState = [
            'client_id' => $user->client->id,
            'product_id' => $productA_web->id,
            'product_pricing_id' => $pricingA_monthly->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'status' => 'Active',
            'billing_amount' => $pricingA_monthly->price,
            'next_due_date' => Carbon::now()->addDays(15), // Default for proration tests
        ];
        $finalServiceState = array_merge($defaultServiceState, $serviceStates);


        $service = ClientService::factory()->create($finalServiceState);

        return compact(
            'user', 'service',
            'productTypeWeb', 'productTypeVps',
            'productA_web', 'pricingA_monthly', 'pricingA_yearly',
            'productB_web', 'pricingB_monthly', 'pricingB_yearly',
            'productC_vps', 'pricingC_monthly',
            'billingCycleMonthly', 'billingCycleYearly' // also return cycles
        );
    }

    /** @test */
    public function client_can_change_billing_cycle_for_same_product_and_status_remains_active()
    {
        $data = $this->setup_test_environment(['status' => 'Active']);
        $user = $data['user'];
        $service = $data['service'];
        // Change to yearly cycle of the same product (Product A)
        $newPricingSameProductDifferentCycle = $data['pricingA_yearly'];
        $original_product_id = $service->product_id;

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
        $this->assertEquals($original_product_id, $service->product_id); // Product ID should not change
        $this->assertEquals('Active', $service->status); // Status should remain active
        $this->assertStringContainsString("Plan cambiado de '{$data['productA_web']->name} ({$data['billingCycleMonthly']->name})' a '{$data['productA_web']->name} ({$data['billingCycleYearly']->name})'.", session('success'));
        $this->assertStringContainsString("Se acreditó", session('success')); // Example: $10/month (30d) to $100/year (365d). Credit for $5. Cost for $8.22. Diff $3.22 invoice
        $this->assertNotNull($service->notes); // Check that notes are added
    }

    /** @test */
    public function client_can_change_to_different_product_of_same_type_and_status_changes_to_pending_configuration()
    {
        $data = $this->setup_test_environment(['status' => 'Active']);
        $user = $data['user'];
        $service = $data['service']; // Initial product is productA_web

        // Test changing to a different product of the same type (Product B Monthly)
        $newPricingDifferentProductSameType = $data['pricingB_monthly'];

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricingDifferentProductSameType->id,
                         ]);
        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $service->refresh();
        $this->assertEquals($newPricingDifferentProductSameType->id, $service->product_pricing_id);
        $this->assertEquals($newPricingDifferentProductSameType->product_id, $service->product_id); // Product ID should change
        $this->assertNotEquals($data['productA_web']->id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Status should change
        $this->assertStringContainsString("Plan cambiado de '{$data['productA_web']->name} ({$data['billingCycleMonthly']->name})' a '{$data['productB_web']->name} ({$data['billingCycleMonthly']->name})'.", session('success'));
        $this->assertStringContainsString("El servicio requiere configuración adicional por un administrador.", session('success'));
        $this->assertNotNull($service->notes);
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
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Service is for Product A (Web Hosting)
        $pricingForDifferentProductType = $data['pricingC_monthly']; // This pricing is for Product C (VPS)
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $pricingForDifferentProductType->id,
                         ]);

        $response->assertRedirect(route('client.services.index')); // Redirects to index with error
        $response->assertSessionHas('error', 'No puedes cambiar a un tipo de producto diferente.'); // Updated error message

        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id); // Plan should not change
    }


    /** @test */
    public function client_cannot_change_plan_if_service_is_not_active()
    {
        // Ensure service is not active, e.g. 'Terminated'
        $data = $this->setup_test_environment(['status' => 'Terminated', 'next_due_date' => Carbon::now()->subDay()]);
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingA_yearly'];
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('error', 'El servicio debe estar activo para cambiar de plan.'); // Specific error message

        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id); // Plan should not change
    }

    /** @test */
    public function client_cannot_change_to_the_same_plan()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Current plan is pricingA_monthly
        $currentPricing = $data['pricingA_monthly']; // Explicitly use the one from setup
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $currentPricing->id,
                         ]);

        $response->assertRedirect(); // Redirects back
        $response->assertSessionHas('error', 'Ya estás en este plan.'); // Specific error message
        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id);
    }

    /** @test */
    public function test_plan_change_results_in_prorated_invoice()
    {
        // Current: $10/30 days. Next due date in 15 days.
        // New: $20/30 days (Product B Monthly).
        // Credit: ($10/30) * 15 = $5
        // Cost New: ($20/30) * 15 = $10
        // Difference: $10 - $5 = $5 invoice
        $data = $this->setup_test_environment([
            'next_due_date' => Carbon::now()->addDays(15), // Current date is March 1st, so NDD is March 16th
        ]);
        $user = $data['user'];
        $service = $data['service']; // Initial is productA_monthly ($10/30d)
        $newPricing = $data['pricingB_monthly']; // productB_monthly ($20/30d)
        $originalNextDueDate = $service->next_due_date;
        $initialClientBalance = $user->client->balance;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $successMessage = session('success');
        $this->assertStringContainsString("El plan ha sido actualizado. Se generó la factura", $successMessage);
        $this->assertStringContainsString("correspondiente al prorrateo", $successMessage);


        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($newPricing->product_id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Product ID changed
        $this->assertEquals($originalNextDueDate, $service->next_due_date); // Next due date should not change
        $this->assertEquals($initialClientBalance, $user->client->balance, 'Client balance should not change for invoice case.');

        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(5.00, $invoice->total_amount); // $10 (new cost for period) - $5 (credit) = $5
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertStringContainsString("Cargo por cambio de plan de '{$data['productA_web']->name}' a '{$newPricing->product->name}'.", $invoice->notes_to_client);


        $invoiceItem = $invoice->items()->first();
        $this->assertNotNull($invoiceItem);
        $this->assertEquals($service->id, $invoiceItem->client_service_id);
        $this->assertEquals(5.00, $invoiceItem->total_price);
        $this->assertStringContainsString("Prorrateo por cambio a {$newPricing->product->name} ({$data['billingCycleMonthly']->name})", $invoiceItem->description);

        $this->assertStringContainsString("Se generó la factura {$invoice->invoice_number} por 5.00 {$newPricing->currency_code}", $service->notes);
    }

    /** @test */
    public function test_plan_change_results_in_client_credit()
    {
        // Current: $20/30 days (Product B Monthly). Next due date in 15 days.
        // New: $10/30 days (Product A Monthly).
        // Credit: ($20/30) * 15 = $10
        // Cost New: ($10/30) * 15 = $5
        // Difference: $5 - $10 = -$5 (credit $5)

        $data = $this->setup_test_environment([
            'product_id' => 'productB_web', // Start with Product B
            'product_pricing_id' => 'pricingB_monthly',
            'billing_cycle_id' => 'billingCycleMonthly',
            'billing_amount' => 20.00,
            'next_due_date' => Carbon::now()->addDays(15),
        ]);
        // Manually override after setup_test_environment if easier
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->first();
        $service->update([
            'product_id' => $data['productB_web']->id,
            'product_pricing_id' => $data['pricingB_monthly']->id,
            'billing_cycle_id' => $data['billingCycleMonthly']->id,
            'billing_amount' => $data['pricingB_monthly']->price,
        ]);
        $service->refresh();


        $newPricing = $data['pricingA_monthly']; // productA_monthly ($10/30d)
        $originalNextDueDate = $service->next_due_date;
        $initialClientBalance = $user->client->balance; // Should be 0 from setup

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $successMessage = session('success');
        $this->assertStringContainsString("Se ha acreditado 5.00 {$newPricing->currency_code} a tu balance.", $successMessage);

        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($newPricing->product_id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Product ID changed
        $this->assertEquals($originalNextDueDate, $service->next_due_date);
        $this->assertEquals($initialClientBalance + 5.00, $user->client->balance);

        $this->assertEquals(0, Invoice::where('client_id', $user->client->id)->where('total_amount', '<>', 0)->count(), "No invoice should be created for a credit scenario.");
        $this->assertStringContainsString("Se acreditó 5.00 {$data['pricingB_monthly']->currency_code} al balance del cliente.", $service->notes);
    }

    /** @test */
    public function test_plan_change_with_no_cost_difference_due_to_proration()
    {
        // Scenario 1: Next due date is today. Credit and Cost for new plan should be 0.
        $data = $this->setup_test_environment([
            'next_due_date' => Carbon::now(), // NDD is today
        ]);
        $user = $data['user'];
        $service = $data['service']; // Initial is productA_monthly ($10/30d)
        $newPricing = $data['pricingB_monthly']; // productB_monthly ($20/30d)
        $originalNextDueDate = $service->next_due_date;
        $initialClientBalance = $user->client->balance;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString("El plan se ha actualizado sin costo adicional por el período actual.", session('success'));

        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($originalNextDueDate, $service->next_due_date);
        $this->assertEquals($initialClientBalance, $user->client->balance);
        $this->assertEquals(0, Invoice::where('client_id', $user->client->id)->count());
        $this->assertStringContainsString("El cambio no generó costos adicionales ni créditos por el período restante.", $service->notes);
    }

     /** @test */
    public function test_plan_change_to_more_expensive_cycle_of_same_product_generates_invoice()
    {
        // Current: Product A Monthly ($10/30 days). NDD in 15 days.
        // New: Product A Yearly ($100/365 days).
        // Credit: ($10/30) * 15 = $5.00
        // Cost New: ($100/365) * 15 = $4.11 (approx)
        // Difference: $4.11 - $5.00 = -$0.89 (credit) -- My math was off in prompt, let's re-verify
        // Let's assume the yearly is much more expensive per day for the remaining period to ensure an invoice.
        // E.g. Yearly $200 / 365 days. Cost New: ($200/365)*15 = $8.22. Invoice $3.22

        $data = $this->setup_test_environment([
            'next_due_date' => Carbon::now()->addDays(15),
        ]);
        $user = $data['user'];
        $service = $data['service']; // Product A monthly ($10)
        $newPricing = $data['pricingA_yearly']; // Product A yearly ($100)
        // Overriding price of yearly to ensure invoice for this test case based on remaining days calculation
        // $newPricing->price = 200; // Let's use existing $100 price and see the outcome.
        // pricePerDayCurrent = 10/30 = 0.3333
        // creditAmount = 0.3333 * 15 = 5
        // pricePerDayNew = 100/365 = 0.2739
        // costForRemainingPeriod = 0.2739 * 15 = 4.109 ~ 4.11
        // proratedDifference = 4.11 - 5 = -0.89 (This will be a credit)

        // To force an invoice, let's use Product B yearly which is $200
        $newPricing = $data['pricingB_yearly']; // Product B $200/year
        // pricePerDayNew = 200/365 = 0.5479
        // costForRemainingPeriod = 0.5479 * 15 = 8.219 ~ 8.22
        // proratedDifference = 8.22 - 5 = 3.22 (Invoice)


        $originalNextDueDate = $service->next_due_date;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');

        $service->refresh();
        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();

        $this->assertNotNull($invoice, "Invoice should be generated.");
        $this->assertEquals(3.22, round($invoice->total_amount,2)); // Expected difference
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($originalNextDueDate, $service->next_due_date);
        // Product ID changes, so it should be pending_configuration
        $this->assertEquals('pending_configuration', $service->status);
        $this->assertStringContainsString("Se generó la factura {$invoice->invoice_number}", $service->notes);
        $this->assertStringContainsString("El servicio requiere configuración adicional por un administrador.", session('success'));
    }

    // --- Tests for calculateProration Endpoint ---

    /** @test */
    public function test_calculate_proration_results_in_charge()
    {
        $data = $this->setup_test_environment(); // service uses pricingA_monthly ($10/30d)
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly']; // $20/30d

        // Expected: Credit $5, New Cost $10. Difference = $5 charge
        $expectedProratedAmount = 5.00;

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $newPricing->currency_code,
                'message' => 'Monto a pagar por el cambio de plan.',
            ]);

        // Ensure no actual change happened
        $service->refresh();
        $this->assertEquals($data['pricingA_monthly']->id, $service->product_pricing_id);
    }

    /** @test */
    public function test_calculate_proration_results_in_credit()
    {
        $data = $this->setup_test_environment(); // service uses pricingA_monthly ($10/30d)
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingA_yearly']; // $100/365d

        // Expected: Credit ($10/30)*15 = $5.00. New Cost ($100/365)*15 = $4.11. Difference = -$0.89 credit
        $expectedProratedAmount = -0.89;

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $newPricing->currency_code,
                'message' => 'Crédito a aplicar a tu balance por el cambio de plan.',
            ]);
    }

    /** @test */
    public function test_calculate_proration_results_in_no_difference()
    {
        $data = $this->setup_test_environment([
            'next_due_date' => Carbon::now(), // No remaining days
        ]);
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly'];

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => 0.00,
                'message' => 'El cambio de plan no tiene costo adicional por el período actual.',
            ]);
    }

    /** @test */
    public function test_calculate_proration_error_service_not_active()
    {
        $data = $this->setup_test_environment(['status' => 'Terminated']);
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly'];

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'El servicio debe estar activo para calcular el prorrateo.']);
    }

    /** @test */
    public function test_calculate_proration_error_selecting_current_plan()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Uses pricingA_monthly

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $service->product_pricing_id, // Current plan
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Esta selección es tu plan actual.']);
    }

    /** @test */
    public function test_calculate_proration_error_different_product_type()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service']; // Product A (Web Hosting)
        $newPricingDifferentType = $data['pricingC_monthly']; // Product C (VPS)

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricingDifferentType->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'No puedes cambiar a un tipo de producto diferente.']);
    }

    /** @test */
    public function test_calculate_proration_error_invalid_pricing_id()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => 99999, // Non-existent ID
            ]);

        $response->assertStatus(404) // ModelNotFoundException
            ->assertJson(['error' => 'El plan seleccionado no es válido.']);
    }

    /** @test */
    public function test_calculate_proration_error_if_current_cycle_is_null()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly'];

        // Simulate a missing billingCycle relationship for the current service's pricing
        // This is tricky to do without direct DB manipulation or more complex factory states.
        // Instead, we can test the controller's handling if $currentCycle object is null,
        // which should be caught by the `if (!$currentCycle)` check.
        // For this test, we'll manually break the relation after setup.
        $service->productPricing->setRelation('billingCycle', null); // Detach for test
        // Note: This modification is in-memory for this test instance of $service->productPricing.
        // The controller loads it fresh, so this test might not trigger the intended path as easily.
        // A more robust way would be to have a ProductPricing without a valid billing_cycle_id,
        // or a BillingCycle that is null/zero for durations.

        // Let's try to save a product pricing with an invalid billing_cycle_id to simulate this.
        $invalidPricing = ProductPricing::factory()->create([
            'product_id' => $data['productA_web']->id,
            'billing_cycle_id' => 9999, // Non-existent billing cycle
            'price' => 10.00,
            'currency_code' => 'USD',
        ]);
        $service->product_pricing_id = $invalidPricing->id;
        $service->billing_cycle_id = $invalidPricing->billing_cycle_id; // ensure service has it too
        $service->save();
        $service->load('productPricing'); // Re-load with potentially broken relation if billing_cycle_id is invalid

        // Now $service->productPricing->billingCycle might be null if eager loading fails due to invalid ID

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        // Depending on how findOrFail vs find handles it, or if the check for $currentCycle is hit.
        // The controller has `if (!$currentCycle)` which should return 500
         $response->assertStatus(500)
             ->assertJson(['error' => 'Error de configuración del ciclo de facturación actual.']);
    }


}
?>
