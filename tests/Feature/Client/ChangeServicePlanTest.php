<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker; // Not used
use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientService;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ProductType;
use App\Models\Invoice;
// use App\Models\InvoiceItem; // Not directly asserted here but good for completeness
use Carbon\Carbon;

class ChangeServicePlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 3, 1, 0, 0, 0)); // Example: March 1st, 2024
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function setup_test_environment(array $serviceStates = [], float $clientInitialBalance = 0.0)
    {
        $user = User::factory()->create(['role' => 'client']);
        Client::factory()->create(['user_id' => $user->id, 'company_id' => $user->company_id, 'balance' => $clientInitialBalance]);

        $productTypeWeb = ProductType::factory()->create(['name' => 'Hosting Web', 'slug' => 'hosting-web']);
        $productTypeVps = ProductType::factory()->create(['name' => 'Servidor VPS', 'slug' => 'servidor-vps']);

        $billingCycleMonthly = BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $billingCycleYearly = BillingCycle::factory()->create(['name' => 'Yearly', 'slug' => 'yearly', 'days' => 365]);

        $productA_web = Product::factory()->create(['name' => 'Web Hosting Basic', 'product_type_id' => $productTypeWeb->id, 'taxable' => true]);
        $pricingA_monthly = ProductPricing::factory()->create([
            'product_id' => $productA_web->id, 'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 10.00, 'currency_code' => 'USD',
        ]);
        $pricingA_yearly = ProductPricing::factory()->create([
            'product_id' => $productA_web->id, 'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 100.00, 'currency_code' => 'USD',
        ]);

        $productB_web = Product::factory()->create(['name' => 'Web Hosting Pro', 'product_type_id' => $productTypeWeb->id, 'taxable' => true]);
        $pricingB_monthly = ProductPricing::factory()->create([
            'product_id' => $productB_web->id, 'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 20.00, 'currency_code' => 'USD',
        ]);
        $pricingB_yearly = ProductPricing::factory()->create([
            'product_id' => $productB_web->id, 'billing_cycle_id' => $billingCycleYearly->id,
            'price' => 200.00, 'currency_code' => 'USD',
        ]);

        $productC_vps = Product::factory()->create(['name' => 'VPS Starter', 'product_type_id' => $productTypeVps->id, 'taxable' => false]);
        $pricingC_monthly = ProductPricing::factory()->create([
            'product_id' => $productC_vps->id, 'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 30.00, 'currency_code' => 'USD',
        ]);

        // Default service for most tests: Product A Monthly
        $defaultServiceState = [
            'client_id' => $user->client->id,
            'product_id' => $productA_web->id,
            'product_pricing_id' => $pricingA_monthly->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'status' => 'Active',
            'billing_amount' => $pricingA_monthly->price,
            'next_due_date' => Carbon::getTestNow()->copy()->addDays(15), // March 16th, 2024
        ];
        $finalServiceState = array_merge($defaultServiceState, $serviceStates);
        $service = ClientService::factory()->create($finalServiceState);

        return compact(
            'user', 'service', 'productA_web', 'pricingA_monthly', 'pricingA_yearly',
            'productB_web', 'pricingB_monthly', 'pricingB_yearly',
            'productC_vps', 'pricingC_monthly',
            'billingCycleMonthly', 'billingCycleYearly'
        );
    }

    private function calculateExpectedMontoFinal(ClientService $service, ProductPricing $newPricing, Carbon $originalNextDueDateForCalc, BillingCycle $originalCycleForCalc, $originalBillingAmount)
    {
        $now = Carbon::getTestNow();
        // Use passed original values for calculation consistency
        $currentCycleDays = (int) $originalCycleForCalc->days;

        $fechaInicioCicloActual = $originalNextDueDateForCalc->copy()->subDays($currentCycleDays);
        $diasUtilizadosPlanActual = $now->diffInDaysFiltered(fn(Carbon $date) => true, $fechaInicioCicloActual, false);
        $diasUtilizadosPlanActual = max(1, $diasUtilizadosPlanActual);
        $diasUtilizadosPlanActual = min($diasUtilizadosPlanActual, $currentCycleDays);

        $tarifaDiariaPlanActual = ($currentCycleDays > 0 && $originalBillingAmount > 0) ? ($originalBillingAmount / $currentCycleDays) : 0;
        $costoUtilizadoPlanActual = $tarifaDiariaPlanActual * $diasUtilizadosPlanActual;
        $creditoNoUtilizado = $originalBillingAmount - $costoUtilizadoPlanActual;
        $creditoNoUtilizado = max(0, round($creditoNoUtilizado, 2));

        $precioTotalNuevoPlan = $newPricing->price;
        return round($precioTotalNuevoPlan - $creditoNoUtilizado, 2);
    }

    /** @test */
    public function client_can_change_billing_cycle_for_same_product_and_status_remains_active()
    {
        $data = $this->setup_test_environment(['status' => 'Active']);
        $user = $data['user'];
        $service = $data['service'];
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle; // Capture before potential change
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingA_yearly'];
        $originalProductId = $service->product_id;

        $montoFinalEsperado = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $service->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertEquals($originalProductId, $service->product_id);
        $this->assertEquals('Active', $service->status);
        $this->assertEquals($originalNextDueDate->toDateString(), $service->next_due_date->toDateString()); // NDD remains original

        $this->assertStringContainsString("Tu próxima fecha de vencimiento sigue siendo el " . $originalNextDueDate->format('Y-m-d'), session('success'));

        if ($montoFinalEsperado > 0) {
            $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
            $this->assertNotNull($invoice);
            $this->assertEquals($montoFinalEsperado, $invoice->total_amount);
        }
    }

    /** @test */
    public function client_can_change_to_different_product_of_same_type_and_status_changes_to_pending_configuration()
    {
        $data = $this->setup_test_environment(['status' => 'Active']);
        $user = $data['user'];
        $service = $data['service'];
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingB_monthly'];

        $montoFinalEsperado = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);
        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $service->refresh();

        $this->assertEquals($newPricing->id, $service->product_pricing_id);
        $this->assertEquals($newPricing->price, $service->billing_amount);
        $this->assertNotEquals($data['productA_web']->id, $service->product_id);
        $this->assertEquals('pending_configuration', $service->status);
        $this->assertEquals($originalNextDueDate->toDateString(), $service->next_due_date->toDateString()); // NDD remains original

        $this->assertStringContainsString("Tu próxima fecha de vencimiento sigue siendo el " . $originalNextDueDate->format('Y-m-d'), session('success'));
        $this->assertStringContainsString("El servicio requiere configuración adicional por un administrador.", session('success'));

        if ($montoFinalEsperado > 0) {
            $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
            $this->assertNotNull($invoice);
            $this->assertEquals($montoFinalEsperado, $invoice->total_amount);
        }
    }

    /** @test */
    public function test_plan_change_results_in_invoice_and_maintains_original_next_due_date() // Name updated
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingB_monthly'];

        $montoFinalEsperado = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertTrue($montoFinalEsperado >= 0);

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), ['new_product_pricing_id' => $newPricing->id]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString("Tu próxima fecha de vencimiento sigue siendo el " . $originalNextDueDate->format('Y-m-d'), session('success'));
        $this->assertStringContainsString("Se generó la factura", session('success'));

        $service->refresh();
        $this->assertEquals($originalNextDueDate->toDateString(), $service->next_due_date->toDateString());
        $invoice = Invoice::where('client_id', $user->client->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($montoFinalEsperado, $invoice->total_amount);
    }

    /** @test */
    public function test_plan_change_results_in_credit_and_maintains_original_next_due_date() // Name updated
    {
        $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $productCheap = Product::factory()->create(['name' => 'Web Hosting SuperCheapo', 'product_type_id' => ProductType::firstWhere('slug', 'hosting-web')->id]);
        $customCheapPricing = ProductPricing::factory()->create([
            'product_id' => $productCheap->id, 'billing_cycle_id' => $billingCycleMonthly->id,
            'price' => 1.00, 'currency_code' => 'USD',
        ]);

        $data = $this->setup_test_environment([
            'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id,
            'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id,
            'billing_cycle_id' => $billingCycleMonthly->id,
            'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->firstOrFail();
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle; // This is $billingCycleMonthly
        $originalBillingAmount = $service->billing_amount; // This is 20.00
        $initialClientBalance = $user->client->balance;
        $newPricing = $customCheapPricing;

        $montoFinalEsperado = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertTrue($montoFinalEsperado < 0);
        $expectedCreditAmount = abs($montoFinalEsperado);

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), ['new_product_pricing_id' => $newPricing->id]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString("Tu próxima fecha de vencimiento sigue siendo el " . $originalNextDueDate->format('Y-m-d'), session('success'));
        $this->assertStringContainsString("Se acreditó " . $expectedCreditAmount, session('success'));

        $service->refresh();
        $user->client->refresh();
        $this->assertEquals($originalNextDueDate->toDateString(), $service->next_due_date->toDateString()); // NDD is original
        $this->assertEquals($initialClientBalance + $expectedCreditAmount, $user->client->balance);
    }

    /** @test */
    public function test_plan_change_with_no_cost_difference_maintains_original_next_due_date()
    {
        $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $data = $this->setup_test_environment([
             'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id,
             'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id,
             'billing_cycle_id' => $billingCycleMonthly->id,
             'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->firstOrFail();
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingA_monthly'];

        $montoFinalEsperado = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertEquals(0.00, $montoFinalEsperado);

        $response = $this->actingAs($user)
            ->post(route('client.services.processUpgradeDowngrade', $service), ['new_product_pricing_id' => $newPricing->id]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString("Tu próxima fecha de vencimiento sigue siendo el " . $originalNextDueDate->format('Y-m-d'), session('success'));
        $this->assertStringContainsString("La actualización no tuvo costo adicional inmediato.", session('success')); // Message changed slightly

        $service->refresh();
        $this->assertEquals($originalNextDueDate->toDateString(), $service->next_due_date->toDateString());
    }

    // --- Tests for calculateProration Endpoint ---
    /** @test */
    public function test_calculate_proration_results_in_charge()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingB_monthly'];

        $expectedProratedAmount = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertTrue($expectedProratedAmount >= 0);

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $newPricing->currency_code,
                'message' => 'Monto a pagar para la actualización completa.',
                'new_next_due_date_preview' => $originalNextDueDate->toDateString(), // Always original NDD
            ]);
    }

    /** @test */
    public function test_calculate_proration_results_in_credit()
    {
        $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
        $productA_web = Product::firstWhere('name', 'Web Hosting Basic') ?? Product::factory()->create(['name' => 'Web Hosting Basic', 'product_type_id' => ProductType::firstWhere('slug', 'hosting-web')->id]);
        $billingCycleCustom = BillingCycle::factory()->create(['name' => 'Tiny Cycle', 'slug' => 'tiny-cycle', 'days' => 10]); // For the new plan, not used in NDD preview calc
        $veryCheapPricing = ProductPricing::factory()->create([
            'product_id' => $productA_web->id, 'billing_cycle_id' => $billingCycleCustom->id,
            'price' => 1.00, 'currency_code' => 'USD',
        ]);
        $data = $this->setup_test_environment([
             'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id,
             'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id,
             'billing_cycle_id' => $billingCycleMonthly->id,
             'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->first();
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;

        $expectedProratedAmount = $this->calculateExpectedMontoFinal($service, $veryCheapPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertTrue($expectedProratedAmount < 0);

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $veryCheapPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => $expectedProratedAmount,
                'currency_code' => $veryCheapPricing->currency_code,
                'message' => 'Crédito a tu balance por la actualización.',
                'new_next_due_date_preview' => $originalNextDueDate->toDateString(), // Always original NDD
            ]);
    }

    /** @test */
    public function test_calculate_proration_results_in_no_difference()
    {
         $billingCycleMonthly = BillingCycle::firstWhere('slug', 'monthly') ?? BillingCycle::factory()->create(['name' => 'Monthly', 'slug' => 'monthly', 'days' => 30]);
         $data = $this->setup_test_environment([
             'product_id' => Product::firstWhere('name', 'Web Hosting Pro')->id,
             'product_pricing_id' => ProductPricing::where('price', 20.00)->where('billing_cycle_id', $billingCycleMonthly->id)->first()->id,
             'billing_cycle_id' => $billingCycleMonthly->id,
             'billing_amount' => 20.00,
        ]);
        $user = $data['user'];
        $service = ClientService::where('client_id', $user->client->id)->first();
        $originalNextDueDate = Carbon::parse($service->next_due_date);
        $originalCycle = $service->billingCycle;
        $originalBillingAmount = $service->billing_amount;
        $newPricing = $data['pricingA_monthly'];

        $expectedProratedAmount = $this->calculateExpectedMontoFinal($service, $newPricing, $originalNextDueDate, $originalCycle, $originalBillingAmount);
        $this->assertEquals(0.00, $expectedProratedAmount);

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'prorated_amount' => 0.00,
                'message' => 'Actualización completa sin costo adicional inmediato.',
                'new_next_due_date_preview' => $originalNextDueDate->toDateString(), // Always original NDD
            ]);
    }

    // --- Error case tests remain unchanged as they don't depend on NDD extension logic ---

    /** @test */
    public function test_only_shows_plans_of_same_product_type_on_upgrade_page()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];

        $response = $this->actingAs($user)
                         ->get(route('client.services.showUpgradeDowngradeOptions', $service));

        $response->assertOk();
        $response->assertInertia(fn ($assert) => $assert
            ->component('Client/Services/UpgradeDowngradeOptions')
            ->has('availableOptions')
            ->where('availableOptions', function ($options) use ($data) {
                $productAPricingIds = [$data['pricingA_monthly']->id, $data['pricingA_yearly']->id];
                $productBPricingIds = [$data['pricingB_monthly']->id, $data['pricingB_yearly']->id];
                $productCPricingIds = [$data['pricingC_monthly']->id];

                $optionIds = collect($options)->pluck('id')->all();

                foreach ($productAPricingIds as $id) {
                    if (!in_array($id, $optionIds)) return false;
                }
                foreach ($productBPricingIds as $id) {
                    if (!in_array($id, $optionIds)) return false;
                }
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
        $service = $data['service'];
        $pricingForDifferentProductType = $data['pricingC_monthly'];
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $pricingForDifferentProductType->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('error', 'No puedes cambiar a un tipo de producto diferente.');

        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id);
    }


    /** @test */
    public function client_cannot_change_plan_if_service_is_not_active()
    {
        $data = $this->setup_test_environment(['status' => 'Terminated', 'next_due_date' => Carbon::getTestNow()->subDay()]);
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingA_yearly'];
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $newPricing->id,
                         ]);

        $response->assertRedirect(route('client.services.index'));
        $response->assertSessionHas('error', 'El servicio debe estar activo para cambiar de plan.');

        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id);
    }

    /** @test */
    public function client_cannot_change_to_the_same_plan()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $currentPricing = $data['pricingA_monthly'];
        $original_pricing_id = $service->product_pricing_id;

        $response = $this->actingAs($user)
                         ->post(route('client.services.processUpgradeDowngrade', $service), [
                             'new_product_pricing_id' => $currentPricing->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ya estás en este plan.');
        $service->refresh();
        $this->assertEquals($original_pricing_id, $service->product_pricing_id);
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
        $service = $data['service'];

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $service->product_pricing_id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Esta selección es tu plan actual.']);
    }

    /** @test */
    public function test_calculate_proration_error_different_product_type()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $newPricingDifferentType = $data['pricingC_monthly'];

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
                'new_product_pricing_id' => 99999,
            ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'El plan seleccionado no es válido.']);
    }

    /** @test */
    public function test_calculate_proration_error_if_current_cycle_is_null()
    {
        $data = $this->setup_test_environment();
        $user = $data['user'];
        $service = $data['service'];
        $newPricing = $data['pricingB_monthly'];

        $invalidPricing = ProductPricing::factory()->create([
            'product_id' => $data['productA_web']->id,
            'billing_cycle_id' => 9999,
            'price' => 10.00,
            'currency_code' => 'USD',
        ]);
        $service->product_pricing_id = $invalidPricing->id;
        $service->billing_cycle_id = $invalidPricing->billing_cycle_id;
        $service->save();
        $service->load('productPricing');

        $response = $this->actingAs($user)
            ->postJson(route('client.services.calculateProration', ['service' => $service->id]), [
                'new_product_pricing_id' => $newPricing->id,
            ]);

         $response->assertStatus(500)
             ->assertJson(['error' => 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.']);
    }
}
?>
