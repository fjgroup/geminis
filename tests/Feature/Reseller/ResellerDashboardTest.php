<?php

namespace Tests\Feature\Reseller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ClientService;
use App\Models\Product; // For creating products for services
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

class ResellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createReseller(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test Reseller',
            'email' => 'reseller@example.com',
            'role' => 'reseller',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createClient(array $attributes = [], ?User $reseller = null): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
            'reseller_id' => $reseller ? $reseller->id : null,
        ], $attributes));
    }

    private function createProduct(): Product
    {
        return Product::factory()->create();
    }

    private function createClientService(User $client, Product $product, array $attributes = []): ClientService
    {
        // Note: ClientService factory might need product_id and potentially product_pricing_id / billing_cycle_id
        // depending on its definition and table structure. Assuming a simple factory for now.
        // The controller logic for ResellerDashboard does not depend on pricing/cycle details of service.
        return ClientService::factory()->create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id, // Assuming product_id is on client_services table
            'status' => 'Active', // Default to Active for simplicity
        ], $attributes));
    }

    /** @test */
    public function reseller_can_access_their_dashboard_and_see_their_clients()
    {
        $reseller = $this->createReseller();
        $product = $this->createProduct(); // A product for services

        // Reseller's clients
        $client1 = $this->createClient(['name' => 'Client One', 'email' => 'client1@example.com'], $reseller);
        $client2 = $this->createClient(['name' => 'Client Two', 'email' => 'client2@example.com'], $reseller);

        // Services for reseller's clients
        $this->createClientService($client1, $product, ['status' => 'Active']);
        $this->createClientService($client1, $product, ['status' => 'Active']);
        $this->createClientService($client2, $product, ['status' => 'Active']);
        $this->createClientService($client2, $product, ['status' => 'Suspended']); // Not active

        // Another reseller and their client (should not be visible)
        $otherReseller = $this->createReseller(['email' => 'otherreseller@example.com']);
        $otherClient = $this->createClient(['name' => 'Other Client'], $otherReseller);
        $this->createClientService($otherClient, $product, ['status' => 'Active']);
        
        // Client not assigned to any reseller
        $unassignedClient = $this->createClient(['name' => 'Unassigned Client', 'email' => 'unassigned@example.com']);
        $this->createClientService($unassignedClient, $product, ['status' => 'Active']);


        $this->actingAs($reseller);

        $response = $this->get(route('reseller.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reseller/ResellerDashboard')
            ->has('clients', 2) // Reseller has 2 clients
            ->where('clients.0.name', $client1->name) // Assuming default order might be by latest, or check specific client
            ->where('clients.1.name', $client2->name)
            ->where('clientCount', 2)
            ->where('activeServicesCount', 3) // 2 for client1, 1 for client2
            // Check that the other client and unassigned client are not present
            // This is implicitly checked by `has('clients', 2)` and specific client name checks.
            // More explicitly, we can ensure their IDs are not in the list:
            ->whereNot('clients.*.id', $otherClient->id)
            ->whereNot('clients.*.id', $unassignedClient->id)
            // Assert reseller's name is displayed (via $page.props.auth.user.name in Vue)
            // This is usually part of the AuthenticatedLayout, but we can check if auth user is correct
            ->where('auth.user.id', $reseller->id)
            ->where('auth.user.name', $reseller->name) 
        );
    }
}
