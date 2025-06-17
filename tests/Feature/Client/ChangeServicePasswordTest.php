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
use Illuminate\Support\Facades\Hash;

class ChangeServicePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function create_basic_service_for_user(User $user = null): ClientService
    {
        $user = $user ?? User::factory()->create(['role' => 'client']);
        // Ensure a Client model is created for the User if your setup requires it
        Client::factory()->create(['user_id' => $user->id, 'company_id' => $user->company_id]);


        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create(['duration_in_months' => 1]);
        $productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => 10.00,
            'currency_code' => 'USD',
        ]);

        return ClientService::factory()->create([
            'client_id' => $user->client->id, // Use client_id from the associated Client model
            'product_id' => $product->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
            'status' => 'Active',
            'billing_amount' => $productPricing->price,
            'config' => ['username' => 'testuser'] // Initial config
        ]);
    }

    /** @test */
    public function client_can_change_service_password_with_valid_data()
    {
        $user = User::factory()->create(['role' => 'client']);
        $service = $this->create_basic_service_for_user($user);

        $newPassword = 'NewSecurePassword123!';

        $response = $this->actingAs($user)
                         ->post(route('client.services.updatePassword', $service), [
                             'new_password' => $newPassword,
                             'new_password_confirmation' => $newPassword,
                         ]);

        $response->assertStatus(200); // Expecting JSON success
        $response->assertJson(['message' => 'Contraseña actualizada con éxito.']);

        $service->refresh();
        $this->assertTrue(Hash::check($newPassword, $service->config['password_hash']));
    }

    /** @test */
    public function client_cannot_change_password_for_others_service()
    {
        $userA = User::factory()->create(['role' => 'client']);
        Client::factory()->create(['user_id' => $userA->id, 'company_id' => $userA->company_id]);

        $userB = User::factory()->create(['role' => 'client']);
        $serviceB = $this->create_basic_service_for_user($userB);

        $newPassword = 'NewSecurePassword123!';

        $response = $this->actingAs($userA)
                         ->post(route('client.services.updatePassword', $serviceB), [
                             'new_password' => $newPassword,
                             'new_password_confirmation' => $newPassword,
                         ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function password_change_fails_with_invalid_password_format()
    {
        $user = User::factory()->create(['role' => 'client']);
        $service = $this->create_basic_service_for_user($user);
        $originalPasswordHash = $service->config['password_hash'] ?? null;

        $this->actingAs($user);

        // Test cases for invalid passwords
        $invalidPasswords = [
            'short' => 'short', // Too short
            'nocase' => 'nouppercase123!', // No uppercase
            'nonumber' => 'NoNumberSymbol!', // No number
            'nosymbol' => 'NoSymbol123', // No symbol
            'mismatch1' => 'ValidPassword123!',
            'mismatch2' => 'DifferentPassword123!',
        ];

        // Test too short
        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $invalidPasswords['short'],
            'new_password_confirmation' => $invalidPasswords['short'],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');
        $service->refresh();
        if ($originalPasswordHash) {
            $this->assertEquals($originalPasswordHash, $service->config['password_hash']);
        } else {
            $this->assertArrayNotHasKey('password_hash', $service->config);
        }


        // Test no uppercase
        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $invalidPasswords['nocase'],
            'new_password_confirmation' => $invalidPasswords['nocase'],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');

        // Test no number
        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $invalidPasswords['nonumber'],
            'new_password_confirmation' => $invalidPasswords['nonumber'],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');

        // Test no symbol
        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $invalidPasswords['nosymbol'],
            'new_password_confirmation' => $invalidPasswords['nosymbol'],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');

        // Test mismatch
        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $invalidPasswords['mismatch1'],
            'new_password_confirmation' => $invalidPasswords['mismatch2'],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password'); // Laravel's 'confirmed' rule
    }

    /** @test */
    public function guest_cannot_change_service_password()
    {
        // User and service are created, but we don't act as the user
        $user = User::factory()->create(['role' => 'client']);
        $service = $this->create_basic_service_for_user($user);
        $newPassword = 'NewSecurePassword123!';

        $response = $this->postJson(route('client.services.updatePassword', $service), [
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        // Expecting redirection to login or 401/403 if it's an API-like route
        // Given it's a JSON response from controller, 401 or 403 is more likely for unauthenticated.
        // Laravel's default for unauthenticated JSON requests is 401.
        $response->assertStatus(401);
    }
}
?>
