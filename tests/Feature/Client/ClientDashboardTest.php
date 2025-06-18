<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Order; // For creating order linked to invoice
use App\Models\Product; // For creating product linked to order
use App\Models\ProductPricing; // For creating product pricing linked to order
use App\Models\BillingCycle; // For creating billing cycle linked to product pricing
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createClient(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
            'balance' => 0, // Ensure balance is set for formatted_balance accessor
            'currency_code' => 'USD', // Ensure currency_code is set
        ], $attributes));
    }

    private function createInvoiceForUser(User $user, array $attributes = []): Invoice
    {
        // Basic setup for an invoice, assuming an order and product context
        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
        $order = Order::factory()->create([
            'client_id' => $user->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);

        return Invoice::factory()->create(array_merge([
            'client_id' => $user->id,
            'order_id' => $order->id, // Link to an order
        ], $attributes));
    }

    /** @test */
    public function client_dashboard_loads_without_invoices_method_error()
    {
        $client = $this->createClient();
        
        // Optionally create some invoices to make the count non-zero,
        // though the main test is the absence of the BadMethodCallException.
        $this->createInvoiceForUser($client, ['status' => 'unpaid']);
        $this->createInvoiceForUser($client, ['status' => 'unpaid']);
        $this->createInvoiceForUser($client, ['status' => 'paid']);

        $this->actingAs($client);

        $response = $this->get(route('client.services.index')); // This route maps to ClientDashboardController@index

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/ClientDashboard')
            ->has('clientServices') // Existing prop
            ->has('pendingOrdersCount') // New prop
            ->has('unpaidInvoicesCount', 2) // New prop, should be 2 based on setup
            ->has('accountBalance') // New prop
            ->has('formattedAccountBalance') // New prop
        );
    }

    /** @test */
    public function client_dashboard_shows_correct_counts_and_balance()
    {
        $client = $this->createClient(['balance' => 123.45, 'currency_code' => 'USD']);

        // Create 2 unpaid invoices
        $this->createInvoiceForUser($client, ['status' => 'unpaid']);
        $this->createInvoiceForUser($client, ['status' => 'unpaid']);
        // Create 1 paid invoice
        $this->createInvoiceForUser($client, ['status' => 'paid']);

        // Create 1 order pending execution
        // (Assuming Order factory and its dependencies exist and are set up correctly)
        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
        Order::factory()->create([
            'client_id' => $client->id,
            'status' => 'paid_pending_execution',
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
        // Create 1 active order (should not be counted in pendingOrdersCount)
        Order::factory()->create([
            'client_id' => $client->id,
            'status' => 'active',
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);


        $this->actingAs($client);
        $response = $this->get(route('client.services.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/ClientDashboard')
            ->where('unpaidInvoicesCount', 2)
            ->where('pendingOrdersCount', 1)
            ->where('accountBalance', 123.45)
            // The exact formatted string depends on the NumberFormatter behavior / locale.
            // For simplicity, we check if it's a string. More specific check if format is critical and stable.
            ->where('formattedAccountBalance', fn (string $value) => str_contains($value, '123.45')) 
        );
    }
}
