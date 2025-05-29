<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Invoice; // Optional: if transactions are linked to invoices
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ClientTransactionHistoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user
        $this->client = User::factory()->create(['role' => 'client']);

        // Create a few transactions for this client
        // Optional: Link to an invoice if your Transaction factory or display requires it
        $invoice = Invoice::factory()->for($this->client)->create();

        Transaction::factory()->count(3)->for($this->client)->for($invoice)->create([
            'transaction_date' => now()->subDays(1),
        ]);
        Transaction::factory()->for($this->client)->for($invoice)->create([
            'transaction_date' => now(), // One more recent transaction
        ]);

        // Create a transaction for another client to ensure filtering
        $otherClient = User::factory()->create(['role' => 'client']);
        Transaction::factory()->for($otherClient)->create();
    }

    public function test_client_can_view_their_transaction_history(): void
    {
        $this->actingAs($this->client);

        $response = $this->get(route('client.transactions.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Transactions/Index')
            ->has('transactions')
            ->has('transactions.data', 4) // Expecting 4 transactions for this client
            ->where('transactions.data.0.client_id', $this->client->id) // Basic check on first item
            // Verify that the most recent transaction is listed first
            ->where('transactions.data.0.transaction_date', Transaction::where('client_id', $this->client->id)->latest('transaction_date')->first()->transaction_date->toISOString())
        );
    }

    public function test_client_sees_pagination_for_transactions(): void
    {
        $this->actingAs($this->client);

        // Create more transactions than the pagination limit (e.g., 15 per page)
        Transaction::factory()->count(20)->for($this->client)->create();

        $response = $this->get(route('client.transactions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Transactions/Index')
            ->has('transactions.links') // Check that pagination links are present
            ->has('transactions.data', 15) // Default pagination size
        );
    }
}
