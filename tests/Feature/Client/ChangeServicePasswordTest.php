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

        $initialPassword = 'InitialPassword123!';
        return ClientService::factory()->create([
            'client_id' => $user->client->id,
            'product_id' => $product->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $billingCycle->id,
            'status' => 'Active',
            'billing_amount' => $productPricing->price,
            'password_encrypted' => Hash::make($initialPassword), // Set initial password
            'config' => ['username' => 'testuser']
        ]);
    }

    /** @test */
    public function client_can_change_service_password_with_valid_data()
    {
        $user = User::factory()->create(['role' => 'client']);
        // It's important that create_basic_service_for_user sets an initial password we can use.
        // Or we set it explicitly here. For simplicity, let's assume the factory/helper does.
        // If not, we'd do:
        $initialPassword = 'OldSecurePassword123!';
        $service = $this->create_basic_service_for_user($user);
        $service->password_encrypted = Hash::make($initialPassword);
        $service->save();


        $newPassword = 'NewSecurePassword456!';

        $response = $this->actingAs($user)
                         ->post(route('client.services.updatePassword', $service), [
                             'current_password' => $initialPassword,
                             'new_password' => $newPassword,
                             'new_password_confirmation' => $newPassword,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Contraseña actualizada con éxito.');
        $response->assertSessionHasNoErrors();

        $service->refresh();
        $this->assertTrue(Hash::check($newPassword, $service->password_encrypted));
    }

    /** @test */
    public function client_cannot_change_password_with_incorrect_current_password()
    {
        $user = User::factory()->create(['role' => 'client']);
        $initialPassword = 'CorrectOldPassword123!';
        $service = $this->create_basic_service_for_user($user);
        $service->password_encrypted = Hash::make($initialPassword);
        $service->save();

        $newPassword = 'NewSecurePassword456!';

        $response = $this->actingAs($user)
            ->post(route('client.services.updatePassword', $service), [
                'current_password' => 'WrongOldPassword123!', // Incorrect current password
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['current_password']);

        $service->refresh();
        $this->assertTrue(Hash::check($initialPassword, $service->password_encrypted)); // Password should not have changed
    }

    /** @test */
    public function client_cannot_change_password_if_current_password_is_missing()
    {
        $user = User::factory()->create(['role' => 'client']);
        $service = $this->create_basic_service_for_user($user); // Initial password set by helper

        $newPassword = 'NewSecurePassword456!';

        $response = $this->actingAs($user)
            ->post(route('client.services.updatePassword', $service), [
                // 'current_password' => MISSING
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPassword,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['current_password']);
    }


    /** @test */
    public function client_cannot_change_password_for_others_service()
    {
        $userA = User::factory()->create(['role' => 'client']);
        Client::factory()->create(['user_id' => $userA->id, 'company_id' => $userA->company_id]);

        $userB = User::factory()->create(['role' => 'client']);
        $serviceB = $this->create_basic_service_for_user($userB); // Initial password set by helper

        $newPassword = 'NewSecurePassword123!';

        $response = $this->actingAs($userA)
                         ->post(route('client.services.updatePassword', $serviceB), [
                             'current_password' => 'irrelevant_for_this_test_but_required_for_validation',
                             'new_password' => $newPassword,
                             'new_password_confirmation' => $newPassword,
                         ]);

        $response->assertStatus(403); // Policy should deny access
    }

    /** @test */
    public function password_change_fails_with_invalid_password_format()
    {
        $user = User::factory()->create(['role' => 'client']);
        $initialPassword = 'CorrectOldPassword123!';
        $service = $this->create_basic_service_for_user($user);
        $service->password_encrypted = Hash::make($initialPassword);
        $service->save();

        $this->actingAs($user);

        $testCases = [
            'short' => ['val' => 'short', 'field' => 'new_password'],
            'nocase' => ['val' => 'nouppercase123!', 'field' => 'new_password'],
            'nonumber' => ['val' => 'NoNumberSymbol!', 'field' => 'new_password'],
            'nosymbol' => ['val' => 'NoSymbol123', 'field' => 'new_password'],
            'mismatch' => ['val' => 'ValidPassword123!', 'confirm' => 'DifferentPassword123!', 'field' => 'new_password'],
        ];

        foreach ($testCases as $case => $data) {
            $payload = [
                'current_password' => $initialPassword,
                'new_password' => $data['val'],
                'new_password_confirmation' => $data['confirm'] ?? $data['val'],
            ];

            $response = $this->post(route('client.services.updatePassword', $service), $payload);

            $response->assertRedirect();
            $response->assertSessionHasErrors($data['field']);

            $service->refresh();
            $this->assertTrue(Hash::check($initialPassword, $service->password_encrypted), "Password changed on case: $case");
        }
    }

    /** @test */
    public function guest_cannot_change_service_password()
    {
        $user = User::factory()->create(['role' => 'client']);
        $service = $this->create_basic_service_for_user($user); // Initial password set by helper
        $newPassword = 'NewSecurePassword123!';

        $response = $this->post(route('client.services.updatePassword', $service), [
            'current_password' => 'irrelevant_initial_pw',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect(route('login')); // Expect redirect to login for web routes
    }
}
?>
