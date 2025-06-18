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

        // Use 'days' as the canonical field for cycle duration
        $billingCycleMonthly = BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Yearly', 'slug' => 'yearly', 'days' => 365]);
        // Example for a 15-day cycle if needed for specific tests, though not strictly necessary for current ones
        // $billingCycle15Days = BillingCycle::factory()->create(['name' => '15 Days', 'slug' => '15-days', 'days' => 15]);


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
        $this->assertEquals($newPricingSameProductDifferentCycle->price, $service->billing_amount); // Full new price
        $this->assertEquals($original_product_id, $service->product_id); // Product ID should not change
        $this->assertEquals('Active', $service->status); // Status should remain active

        $expectedNewDueDateString = Carbon::getTestNow()->addDays($data['billingCycleYearly']->days)->format('Y-m-d');
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());

        // Recalculate MontoFinal for assertion
        // Initial: pricingA_monthly ($10/30d). 15 days used. CreditoNoUtilizado = $5.
        // New: pricingA_yearly ($100/365d). PrecioTotalNuevoPlan = $100.
        // MontoFinal = $100 - $5 = $95 (Invoice).
        $expectedMontoFinal = 95.00;
        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($expectedMontoFinal, $invoice->total_amount);
        $this->assertStringContainsString("Plan actualizado de '{$data['productA_web']->name} ({$data['billingCycleMonthly']->name})' a '{$data['productA_web']->name} ({$data['billingCycleYearly']->name})'.", session('success'));
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", session('success'));
        $this->assertStringContainsString("Se generó la factura {$invoice->invoice_number}", session('success'));
        $this->assertNotNull($service->notes);
    }

    /** @test */
    public function client_can_change_to_different_product_of_same_type_and_status_changes_to_pending_configuration()
    {
        $data = $this->setup_test_environment(['status' => 'Active']);
        $user = $data['user'];
        $service = $data['service']; // Initial product is productA_web ($10/30d)
        $newPricing = $data['pricingB_monthly']; // Product B Monthly ($20/30d)
        $newCycle = $data['billingCycleMonthly']; // 30 days

        // Calculation for MontoFinal:
        // Initial: pricingA_monthly ($10/30d). 15 days used. CreditoNoUtilizado = $5.
        // New: pricingB_monthly ($20/30d). PrecioTotalNuevoPlan = $20.
        // MontoFinal = $20 - $5 = $15 (Invoice).
        $expectedMontoFinal = 15.00;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);
        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $service->refresh();
        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($newPricing->product_id, $service->product_id); // Product ID should change
        $this->assertNotEquals($data['productA_web']->id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Status should change

        $expectedNewDueDateString = Carbon::getTestNow()->addDays($newCycle->days)->format('Y-m-d');
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());

        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($expectedMontoFinal, $invoice->total_amount);

        $this->assertStringContainsString("Plan actualizado de '{$data['productA_web']->name} ({$data['billingCycleMonthly']->name})' a '{$data['productB_web']->name} ({$newCycle->name})'.", session('success'));
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", session('success'));
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
        // New Logic: Current plan: $10/30d. 15 days used. CreditoNoUtilizado = $5.
        // New plan (pricingB_monthly): $20/30d. PrecioTotalNuevoPlan = $20.
        // MontoFinal = $20 - $5 = $15 (Invoice).
        // New next_due_date = today + 30 days.
        $data = $this->setup_test_environment(); // next_due_date is +15 days from Carbon::now()
        $user = $data['user'];
        $service = $data['service']; // Initial is productA_monthly ($10/30d)
        $newPricing = $data['pricingB_monthly']; // productB_monthly ($20/30d)
        $newCycle = $data['billingCycleMonthly']; // 30 days
        $initialClientBalance = $user->client->balance;
        $expectedInvoiceAmount = 15.00;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $successMessage = session('success');
        // Check for new next due date in message
        $expectedNewDueDateString = Carbon::getTestNow()->addDays($newCycle->days)->format('Y-m-d');
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", $successMessage);
        $this->assertStringContainsString("Se generó la factura", $successMessage);
        $this->assertStringContainsString("correspondiente a la actualización", $successMessage);


        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount); // Billing amount is full new price
        $this->assertEquals($newPricing->product_id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Product ID changed
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());
        $this->assertEquals($initialClientBalance, $user->client->balance, 'Client balance should not change for invoice case.');

        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($expectedInvoiceAmount, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertStringContainsString("Cargo por actualización de plan de '{$data['productA_web']->name}' a '{$newPricing->product->name}'.", $invoice->notes_to_client);


        $invoiceItem = $invoice->items()->first();
        $this->assertNotNull($invoiceItem);
        $this->assertEquals($service->id, $invoiceItem->client_service_id);
        $this->assertEquals($expectedInvoiceAmount, $invoiceItem->total_price);
        $this->assertStringContainsString("Cargo por actualización a {$newPricing->product->name} ({$newCycle->name})", $invoiceItem->description);

        $this->assertStringContainsString("Se generó la factura {$invoice->invoice_number} por {$expectedInvoiceAmount}", $service->notes); // Check notes on service
        $this->assertStringContainsString("Antigua fecha de vencimiento: " . Carbon::now()->addDays(15)->format('Y-m-d'), $service->notes);
        $this->assertStringContainsString("Crédito por tiempo no utilizado: 5.00", $service->notes); // 5.00 is the credit
    }

    /** @test */
    public function test_plan_change_results_in_client_credit()
    {
        // Current: pricingB_monthly ($20/30d). 15 days used. CreditoNoUtilizado = $10.
        // New: pricingA_custom_cheap ($1/30d) to ensure credit. PrecioTotalNuevoPlan = $1.
        // MontoFinal = $1 - $10 = -$9 (Credit).
        // New next_due_date = today + 30 days.

        $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $productCheap = Product::factory()->create(['name' => 'Web Hosting SuperCheapo', 'product_type_id' => ProductType::firstWhere('name', 'Hosting Web')->id]);
        $customCheapPricing = ProductPricing::factory()->create([
            'product_id' => $productCheap->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 1.00,
            'currency_code' => 'USD',
        ]);

        $data = $this->setup_test_environment([
            'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id, // productB_web
            'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id, // pricingB_monthly
            'billing_cycle_id' => $billingCycleMonthly->id,
            'billing_amount' => 20.00, // Corresponds to pricingB_monthly
        ]);
        $user = $data['user'];
        // Fetch the service created by setup_test_environment
        $service = ClientService::where('client_id', $user->client->id)->firstOrFail();

        $newPricing = $customCheapPricing;
        $newCycle = $billingCycleMonthly; // 30 days
        $initialClientBalance = $user->client->balance;
        $expectedCreditAmount = 9.00; // abs($montoFinal)

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $successMessage = session('success');
        $expectedNewDueDateString = Carbon::getTestNow()->addDays($newCycle->days)->format('Y-m-d');
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", $successMessage);
        $this->assertStringContainsString("Se acreditó {$expectedCreditAmount}", $successMessage);


        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($newPricing->product_id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status); // Product ID changed
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());
        $this->assertEquals($initialClientBalance + $expectedCreditAmount, $user->client->balance);

        $this->assertEquals(0, Invoice::where('client_id', $user->client->id)->where('total_amount', '<>', 0)->count(), "No invoice should be created for a credit scenario.");
        $this->assertStringContainsString("Se acreditó {$expectedCreditAmount} {$newPricing->currency_code} al balance del cliente.", $service->notes);
    }

    /** @test */
    public function test_plan_change_with_no_cost_difference() // Renamed for clarity
    {
        // Current: pricingB_monthly ($20/30d). 15 days used. CreditoNoUtilizado = $10.
        // New plan: pricingA_monthly ($10/30d). PrecioTotalNuevoPlan = $10.
        // MontoFinal = $10 (new) - $10 (credit) = $0.
        // New next_due_date = today + 30 days.
        $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $data = $this->setup_test_environment([
            'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id, // productB_web
            'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id, // pricingB_monthly
            'billing_cycle_id' => $billingCycleMonthly->id,
            'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->firstOrFail();
        $newPricing = $data['pricingA_monthly']; // $10/30d
        $newCycle = $billingCycleMonthly;
        $initialClientBalance = $user->client->balance;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $expectedNewDueDateString = Carbon::getTestNow()->addDays($newCycle->days)->format('Y-m-d');
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", session('success'));
        $this->assertStringContainsString("La actualización no tuvo costo adicional inmediato.", session('success'));


        $service->refresh();
        $user->client->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());
        $this->assertEquals($initialClientBalance, $user->client->balance); // Balance should not change
        $this->assertEquals(0, Invoice::where('client_id', $user->client->id)->count()); // No invoice created
        $this->assertStringContainsString("La actualización no generó costos adicionales ni créditos inmediatos.", $service->notes);
    }

     /** @test */
    public function test_plan_change_to_different_cycle_same_product_updates_next_due_date()
    {
        // Current: Product A Monthly ($10/30 days). NDD in 15 days. CreditoNoUtilizado = $5.
        // New: Product A Yearly ($100/365 days). PrecioTotalNuevoPlan = $100.
        // MontoFinal = $100 - $5 = $95 (Invoice).
        // New next_due_date = today + 365 days.

        $data = $this->setup_test_environment(); // Service is productA_monthly
        $user = $data['user'];
        $service = $data['service'];
        $originalProductId = $service->product_id;
        $newPricing = $data['pricingA_yearly']; // Product A yearly ($100/365d)
        $newCycle = $data['billingCycleYearly']; // 365 days
        $expectedInvoiceAmount = 95.00;

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $expectedNewDueDateString = Carbon::getTestNow()->addDays($newCycle->days)->format('Y-m-d');
        $this->assertStringContainsString("Próximo vencimiento: {$expectedNewDueDateString}", session('success'));
        $this->assertStringContainsString("Se generó la factura", session('success'));


        $service->refresh();
        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();

        $this->assertNotNull($invoice, "Invoice should be generated.");
        $this->assertEquals($expectedInvoiceAmount, round($invoice->total_amount,2));
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($originalProductId, $service->product_id); // Product ID should NOT change
        $this->assertEquals('Active', $service->status); // Status should remain Active
        $this->assertEquals($expectedNewDueDateString, $service->next_due_date->toDateString());
        $this->assertStringContainsString("Se generó la factura {$invoice->invoice_number}", $service->notes);
        $this->assertStringNotContainsString("El servicio requiere configuración adicional", session('success')); // Status should not change
    }

    // --- Tests for calculateProration Endpoint ---

    /** @test */
    public function test_calculate_proration_results_in_charge()
    {
        $data = $this->setup_test_environment(); // service uses pricingA_monthly ($10/30d)
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly']; // $20/30d

        // Calculation:
        // Current plan: $10/30 days. Service's next_due_date is March 16th. Carbon::now() is March 1st.
        // FechaInicioCicloActual = March 16th - 30 days = Feb 15th.
        // DiasUtilizadosPlanActual = March 1st - Feb 15th = 15 days.
        // TarifaDiariaPlanActual = $10 / 30.
        // CreditoNoUtilizado = $10 - (($10/30) * 15) = $10 - $5 = $5.
        // New plan (pricingB_monthly): $20.
        // MontoFinal = $20 (PrecioTotalNuevoPlan) - $5 (CreditoNoUtilizado) = $15.
        $expectedProratedAmount = 15.00;

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $newPricing->currency_code,
                'message' => 'Monto a pagar para la actualización completa.',
            ]);

        // Ensure no actual change happened
        $service->refresh();
        $this->assertEquals($data['pricingA_monthly']->id, $service->product_pricing_id);
    }

    /** @test */
    public function test_calculate_proration_results_in_credit()
    {
        // Current: pricingB_monthly ($20/30d). DiasUtilizados = 15. CreditoNoUtilizado = $10.
        // New: pricingA_monthly ($10/30d). PrecioTotalNuevoPlan = $10.
        // MontoFinal = $10 - $10 = $0.  This scenario is now a no-difference.
        // Let's adjust for a real credit.
        // Current: pricingB_monthly ($20/30d). CreditoNoUtilizado = $10.
        // New: a cheaper plan, e.g. $5/30d if it existed.
        // For this test, let's make pricingA_yearly very cheap to force a credit against pricingB_monthly.

        $productA_web = Product::factory()->create(['name' => 'Web Hosting Basic', 'product_type_id' => ProductType::firstWhere('name', 'Hosting Web')->id]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Super Economico Yearly', 'slug' => 'super-eco-yearly', 'days' => 365]);
        $veryCheapYearlyPricing = ProductPricing::factory()->create([
            'product_id' => $productA_web->id,
            'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 1.00, // Super cheap
            'currency_code' => 'USD',
        ]);

        // Service starts with pricingB_monthly ($20/30d)
        $data = $this->setup_test_environment([
             'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id, // productB_web
             'product_pricing_id' => ProductPricing::firstWhere('price', 20.00)->id, // pricingB_monthly
             'billing_cycle_id' => BillingCycle::firstWhere('days', 30)->id, // Monthly
             'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->first(); // Get the actual service instance

        // DiasUtilizadosPlanActual = 15 (default setup)
        // CreditoNoUtilizado for $20/30d plan = $20 - (($20/30)*15) = $10.
        // New Plan (veryCheapYearlyPricing): $1.00
        // MontoFinal = $1.00 - $10.00 = -$9.00 (Credit)
        $expectedProratedAmount = -9.00;

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $veryCheapYearlyPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $veryCheapYearlyPricing->currency_code,
                'message' => 'Crédito a tu balance por la actualización.',
            ]);
    }

    /** @test */
    public function test_calculate_proration_results_in_no_difference()
    {
        // Current: pricingB_monthly ($20/30d). DiasUtilizados = 15. CreditoNoUtilizado = $10.
        // New plan: pricingA_monthly ($10/30d). PrecioTotalNuevoPlan = $10.
        // MontoFinal = $10 (new) - $10 (credit) = $0.
         $data = $this->setup_test_environment([
             'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id, // productB_web
             'product_pricing_id' => ProductPricing::firstWhere('price', 20.00)->id, // pricingB_monthly
             'billing_cycle_id' => BillingCycle::firstWhere('days', 30)->id, // Monthly
             'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->first();
        $newPricing = $data['pricingA_monthly']; // $10/30d


        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => 0.00,
                'message' => 'Actualización completa sin costo adicional inmediato.',
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
