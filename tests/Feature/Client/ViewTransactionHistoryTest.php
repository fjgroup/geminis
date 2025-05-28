<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Invoice; // For transactions linked to invoices
use App\Models\Order; // For creating invoices linked to orders
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

class ViewTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createTransaction(User $client, array $attributes = [], ?Invoice $invoice = null): Transaction
    {
        return Transaction::factory()->create(array_merge([
            'client_id' => $client->id,
            'invoice_id' => $invoice ? $invoice->id : null,
            'gateway_slug' => 'manual',
            'type' => 'payment',
            'amount' => 50.00,
            'currency_code' => 'USD',
            'status' => 'completed',
            'description' => 'Test Transaction',
            'transaction_date' => Carbon::now(),
        ], $attributes));
    }
    
    private function createInvoiceForClient(User $client): Invoice
    {
        // Simplified invoice creation for testing purposes
        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
        ]);
        return Invoice::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
        ]);
    }


    /** @test */
    public function client_can_view_their_own_transaction_history()
    {
        $client = $this->createUser();
        $invoice = $this->createInvoiceForClient($client);

        $transaction1 = $this->createTransaction($client, ['transaction_date' => Carbon::now()->subDays(2), 'amount' => 50, 'description' => 'Old Payment']);
        $transaction2 = $this->createTransaction($client, ['transaction_date' => Carbon::now()->subDays(1), 'amount' => 75, 'type' => 'refund', 'description' => 'Recent Refund', 'invoice_id' => $invoice->id]);
        $transaction3 = $this->createTransaction($client, ['transaction_date' => Carbon::now(), 'amount' => 100, 'description' => 'Latest Payment']);
        
        $otherClient = $this->createUser();
        $otherTransaction = $this->createTransaction($otherClient, ['description' => 'Other Client Transaction']);

        $this->actingAs($client);

        $response = $this->get(route('client.transactions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Transactions/Index')
            ->has('transactions')
            ->has('transactions.data', 3)
            ->where('transactions.data.0.id', $transaction3->id) // Most recent first
            ->where('transactions.data.1.id', $transaction2->id)
            ->where('transactions.data.2.id', $transaction1->id)
            ->where('transactions.data.0.description', 'Latest Payment')
            ->where('transactions.data.1.description', 'Recent Refund')
            ->where('transactions.data.1.invoice.invoice_number', $invoice->invoice_number) // Check related invoice detail
            ->missing('transactions.data.*.id', $otherTransaction->id) // Ensure other client's transaction is not present
        );
    }

    /** @test */
    public function client_sees_empty_state_when_no_transactions_exist()
    {
        $client = $this->createUser();

        $this->actingAs($client);

        $response = $this->get(route('client.transactions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Transactions/Index')
            ->has('transactions.data', 0)
        );
        // To assert text like "You have no transactions yet."
        // This requires the text to be part of the Inertia response or check HTML.
        // For now, checking empty data array is the primary goal.
        // If the component explicitly passes a prop for empty state message, that can be asserted.
        // Or, if the text is always in the component, $response->assertSee('You have no transactions yet.'); might work
        // but it's better to rely on prop assertions for data-driven components.
    }

    /** @test */
    public function transaction_history_is_paginated()
    {
        $client = $this->createUser();
        // Default pagination in controller is 15. Create 20 transactions.
        for ($i = 0; $i < 20; $i++) {
            $this->createTransaction($client, ['transaction_date' => Carbon::now()->subMinutes($i)]);
        }

        $this->actingAs($client);

        $response = $this->get(route('client.transactions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Transactions/Index')
            ->has('transactions.data', 15) // Assuming pagination is 15 per page
            ->has('transactions.links')
            ->has('transactions.total')
            ->has('transactions.per_page')
            ->has('transactions.current_page')
            ->where('transactions.total', 20)
            ->where('transactions.per_page', 15)
            ->where('transactions.current_page', 1)
        );
    }

    /** @test */
    public function unauthenticated_user_cannot_view_transaction_history()
    {
        $response = $this->get(route('client.transactions.index'));

        $response->assertRedirect(route('login'));
    }
}
