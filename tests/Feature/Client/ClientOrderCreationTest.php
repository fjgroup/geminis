<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderActivity;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionPricing;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

class ClientOrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private function createClient(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'active', // Ensure product is active for listing
        ], $attributes));
    }

    private function createBillingCycle(array $attributes = []): BillingCycle
    {
        return BillingCycle::factory()->create($attributes);
    }

    private function createProductPricing(Product $product, BillingCycle $billingCycle, array $attributes = []): ProductPricing
    {
        return ProductPricing::factory()->create(array_merge([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => 100.00,
            'currency_code' => 'USD',
        ], $attributes));
    }

    private function createConfigurableOptionGroup(Product $product, array $attributes = []): ConfigurableOptionGroup
    {
        $group = ConfigurableOptionGroup::factory()->create($attributes);
        $product->configurableOptionGroups()->attach($group->id);
        return $group;
    }

    private function createConfigurableOption(ConfigurableOptionGroup $group, array $attributes = []): ConfigurableOption
    {
        return ConfigurableOption::factory()->create(array_merge([
            'configurable_option_group_id' => $group->id,
        ], $attributes));
    }

    private function createConfigurableOptionPricing(ConfigurableOption $option, BillingCycle $billingCycle, array $attributes = []): ConfigurableOptionPricing
    {
        return ConfigurableOptionPricing::factory()->create(array_merge([
            'configurable_option_id' => $option->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => 10.00, // Default option price
            'currency_code' => 'USD',
        ], $attributes));
    }

    /** @test */
    public function client_can_successfully_create_an_order_without_configurable_options()
    {
        $client = $this->createClient();
        $product = $this->createProduct(['name' => 'Basic Hosting']);
        $monthlyCycle = $this->createBillingCycle(['name' => 'Monthly']);
        $productPricing = $this->createProductPricing($product, $monthlyCycle, ['price' => 10.00]);

        $this->actingAs($client);

        // 1. Visit Product Listing
        $response = $this->get(route('client.products.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
        );

        // 2. Visit Order Form
        $response = $this->get(route('client.order.showOrderForm', ['product' => $product->id]));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/Create')
            ->where('product.id', $product->id)
        );

        // 3. Submit Order Form
        $orderFormData = [
            'product_pricing_id' => $productPricing->id,
            'notes' => 'Test order notes for basic product.',
        ];

        $response = $this->post(route('client.order.placeOrder', ['product' => $product->id]), $orderFormData);
        
        // Assertions
        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'product_pricing_id' => $productPricing->id,
            'status' => 'pending_payment',
            'total_amount' => 10.00,
            'notes' => 'Test order notes for basic product.',
        ]);

        $order = Order::where('client_id', $client->id)->latest()->first();
        $this->assertNotNull($order);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 10.00,
        ]);
        $this->assertEquals($order->invoice_id, Invoice::where('order_id', $order->id)->first()->id);


        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_pricing_id' => $productPricing->id,
            'item_type' => 'product',
            'total_price' => 10.00,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice_id,
            'description' => $product->name . ' - ' . $monthlyCycle->name, // Based on OrderController logic
            'total_price' => 10.00,
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'user_id' => $client->id,
            'type' => 'order_requested_by_client',
        ]);
        
        $response->assertRedirect(route('client.invoices.show', ['invoice' => $order->invoice_id]));
        $response->assertSessionHas('success', 'Order placed successfully! Please complete payment.');
    }

    /** @test */
    public function client_can_successfully_create_an_order_with_configurable_options()
    {
        $client = $this->createClient();
        $product = $this->createProduct(['name' => 'Advanced Server']);
        $monthlyCycle = $this->createBillingCycle(['name' => 'Monthly']);
        $productPricing = $this->createProductPricing($product, $monthlyCycle, ['price' => 50.00]);

        // Configurable Options
        $groupRam = $this->createConfigurableOptionGroup($product, ['name' => 'RAM']);
        $option8gb = $this->createConfigurableOption($groupRam, ['name' => '8GB RAM']);
        $option16gb = $this->createConfigurableOption($groupRam, ['name' => '16GB RAM']);
        $pricing8gb = $this->createConfigurableOptionPricing($option8gb, $monthlyCycle, ['price' => 5.00]);
        $pricing16gb = $this->createConfigurableOptionPricing($option16gb, $monthlyCycle, ['price' => 10.00]); // Client will select this

        $groupDisk = $this->createConfigurableOptionGroup($product, ['name' => 'Disk Space']);
        $option100gb = $this->createConfigurableOption($groupDisk, ['name' => '100GB SSD']);
        $option200gb = $this->createConfigurableOption($groupDisk, ['name' => '200GB SSD']);
        $pricing100gb = $this->createConfigurableOptionPricing($option100gb, $monthlyCycle, ['price' => 8.00]); // Client will select this
        $pricing200gb = $this->createConfigurableOptionPricing($option200gb, $monthlyCycle, ['price' => 15.00]);

        $this->actingAs($client);

        // 1. Visit Product Listing & Order Form (simplified for brevity, focus on POST)
        $response = $this->get(route('client.order.showOrderForm', ['product' => $product->id]));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/Create')
            ->where('product.id', $product->id)
            ->has('product.configurable_option_groups', 2)
        );
        
        // 3. Submit Order Form
        // The 'configurable_options' key in StoreClientOrderRequest expects an array of option_pricing_ids
        // So it should be [configurable_option_id => option_pricing_id]
        // However, the controller logic iterates over ConfigurableOption::whereIn('id', array_keys($configurableOptionInput))
        // and then finds the pricing. The form likely submits option_id => value, where value might be option_pricing_id or just an indicator.
        // Let's assume the form is structured to send `configurable_options[$configurable_option_id] = $option_pricing_id`
        // For simplicity and to align with how typical forms might submit this for selection,
        // we will structure it as `configurable_options[configurable_option_id] = option_pricing_id`.
        // The controller logic `ConfigurableOption::whereIn('id', array_keys($configurableOptionInput))`
        // will then fetch these ConfigurableOptions based on their IDs.
        // The OrderController's current logic for configurable options seems to take array_keys of the input.
        // It then loads `ConfigurableOption::whereIn('id', $selectedOptionIds)`
        // $selectedOptionIds are keys from `configurable_options` in the request.
        // This means the request should send `configurable_options` as an array where keys are `configurable_option_id`
        // and values are relevant if needed (e.g., selected value ID for radio/select, or just '1' for checkbox).
        // The controller then finds the *first* option pricing for that option and billing cycle.
        // This is a bit ambiguous. For the test to work with current OrderController:
        // We need to ensure the `configurable_options` in the request are structured
        // such that `array_keys($validatedData['configurable_options'])` gives the IDs of the *ConfigurableOption* models,
        // and then the controller finds the *first* OptionPricing for that option matching the billing cycle.
        // This is fragile if an option has multiple pricings for the same cycle (which it shouldn't).
        // The test will assume the form sends selected ConfigurableOption IDs as keys.
        // Let's refine the structure based on the controller's expectation.
        // The controller uses `array_keys($validatedData['configurable_options'])` to get selected ConfigurableOption IDs.
        // So, the payload should be `['configurable_options' => [$option16gb->id => 'selected', $option100gb->id => 'selected']]`
        // The value ('selected' or '1') does not matter as much as the key.
        // The `StoreClientOrderRequest` validates `configurable_options` as an array and `configurable_options.*` as existing `option_pricings.id`.
        // This implies the form should submit an array of selected option_pricing_ids.
        // `configurable_options: ['array'], 'configurable_options.*': ['exists:configurable_option_pricings,id']`
        // This means the form should submit `configurable_options = [$pricing16gb->id, $pricing100gb->id]`
        // Let's adjust OrderController to match this validation more directly.
        // The controller logic `ConfigurableOption::whereIn('id', $selectedOptionIds)` needs to change if request sends option_pricing_ids.
        // Given the current controller, let's assume the request sends configurable_option_id as keys.
        // But the validation `configurable_options.*': ['exists:configurable_option_pricings,id']` contradicts this.
        //
        // Re-evaluating: The `StoreClientOrderRequest` validates `configurable_options.*` as existing `option_pricings.id`.
        // This means the `configurable_options` array in the request should be a list of `configurable_option_pricing_id`s.
        // The OrderController's logic `ConfigurableOption::whereIn('id', $selectedOptionIds)` is therefore problematic
        // if `$selectedOptionIds` are derived from `configurable_options` which are `option_pricing_id`s.
        //
        // For this test, I will assume the `StoreClientOrderRequest` is the source of truth for the expected format,
        // meaning `configurable_options` is an array of `configurable_option_pricing_id`s.
        // This means `OrderController` needs to be adjusted to correctly process this.
        // However, the task is to TEST the flow. I will construct the request as per `StoreClientOrderRequest`
        // and the test might fail if `OrderController` logic is not aligned, which is a valid test outcome.
        
        $orderFormData = [
            'product_pricing_id' => $productPricing->id,
            'configurable_options' => [ // Array of selected configurable_option_pricing_ids
                $pricing16gb->id,
                $pricing100gb->id,
            ],
            'notes' => 'Test order with options.',
        ];
        
        // The OrderController's current logic for processing configurable options:
        // It takes `array_keys($validatedData['configurable_options'])` if it's an associative array,
        // or just `$validatedData['configurable_options']` if it's a simple array of IDs.
        // Then it does `ConfigurableOption::whereIn('id', $selectedOptionIds)`.
        // This is expecting IDs of ConfigurableOption, not ConfigurableOptionPricing.
        //
        // To make the test pass with current controller, I would need to send data like:
        // 'configurable_options' => [$option16gb->id => $pricing16gb->id, $option100gb->id => $pricing100gb->id]
        // Then $selectedOptionIds = [$option16gb->id, $option100gb->id]
        // The controller would then find ConfigurableOption for these, and then find *their* pricings.
        // This structure `$option->optionPricings->first()` in controller is problematic.
        //
        // Let's stick to the `StoreClientOrderRequest` validation and assume `OrderController` will be fixed or this test will highlight the issue.
        // If `StoreClientOrderRequest` expects `configurable_options.*` to be `configurable_option_pricings.id`,
        // then the `OrderController` should be updated to process `ConfigurableOptionPricing::whereIn('id', $validatedData['configurable_options'])`.

        // For now, to test the existing controller logic as best as possible,
        // I will send data that might work with its current interpretation, even if the validation is more specific.
        // The controller iterates `ConfigurableOption::whereIn('id', array_keys($configurableOptionInput))`
        // and then `optionPricings->first()`. This suggests the form should submit `configurable_option_id` as keys.
        // The validation rule `configurable_options.*': ['exists:configurable_option_pricings,id']` is for the *values* in that case.
        // So the request should look like: `configurable_options[$option_id] = $option_pricing_id`
        $orderFormData = [
            'product_pricing_id' => $productPricing->id,
            'configurable_options' => [ 
                $option16gb->id => $pricing16gb->id, // configurable_option_id => configurable_option_pricing_id
                $option100gb->id => $pricing100gb->id,
            ],
            'notes' => 'Test order with options.',
        ];


        $response = $this->post(route('client.order.placeOrder', ['product' => $product->id]), $orderFormData);

        $expectedTotal = $productPricing->price + $pricing16gb->price + $pricing100gb->price; // 50 + 10 + 8 = 68

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'product_pricing_id' => $productPricing->id,
            'status' => 'pending_payment',
            'total_amount' => $expectedTotal,
            'notes' => 'Test order with options.',
        ]);

        $order = Order::where('client_id', $client->id)->latest()->first();
        $this->assertNotNull($order);

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => $expectedTotal,
        ]);
        $this->assertEquals($order->invoice_id, Invoice::where('order_id', $order->id)->first()->id);

        // Base Product Item
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_type' => 'product',
            'total_price' => $productPricing->price,
        ]);
        // Configurable Option Items
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'configurable_option_id' => $option16gb->id,
            'option_pricing_id' => $pricing16gb->id,
            'item_type' => 'configurable_option',
            'total_price' => $pricing16gb->price,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'configurable_option_id' => $option100gb->id,
            'option_pricing_id' => $pricing100gb->id,
            'item_type' => 'configurable_option',
            'total_price' => $pricing100gb->price,
        ]);
        $this->assertEquals(3, $order->items()->count()); // 1 base product + 2 options

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice_id,
            // Description for base product
        ]);
         $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice_id,
            'description' => $option16gb->name, 
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $order->invoice_id,
            'description' => $option100gb->name,
        ]);


        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'user_id' => $client->id,
            'type' => 'order_requested_by_client',
        ]);
        
        $response->assertRedirect(route('client.invoices.show', ['invoice' => $order->invoice_id]));
        $response->assertSessionHas('success', 'Order placed successfully! Please complete payment.');
    }
}
