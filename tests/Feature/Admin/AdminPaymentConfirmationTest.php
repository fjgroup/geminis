<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;


class AdminPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $client;
    private Order $orderPendingPayment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create(['role' => 'admin']); // Assuming role is set

        // Create a client user
        $this->client = User::factory()->create(['role' => 'client']);

        // Create an order for the client with status 'pending_payment'
        $this->orderPendingPayment = Order::factory()->for($this->client)->create([
            'status' => 'pending_payment',
            'order_number' => 'ORD-' . Str::uuid(),
            // Add other necessary fields like invoice_id if your logic requires it,
            // though for simple payment confirmation, it might not be strictly needed for this test.
        ]);
    }

    public function test_admin_can_confirm_payment_for_order(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.orders.confirmPayment', ['order' => $this->orderPendingPayment->id]));

        $response->assertRedirect(route('admin.orders.show', $this->orderPendingPayment->id));
        // $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $this->orderPendingPayment->id,
            'status' => 'paid_pending_execution',
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $this->orderPendingPayment->id,
            'user_id' => $this->admin->id, // Admin who performed the action
            'type' => 'admin_confirmed_payment',
        ]);
    }

    public function test_admin_cannot_confirm_payment_for_non_pending_payment_order(): void
    {
        $this->actingAs($this->admin);

        // Change order status to something other than 'pending_payment'
        $this->orderPendingPayment->update(['status' => 'active']);

        $response = $this->post(route('admin.orders.confirmPayment', ['order' => $this->orderPendingPayment->id]));

        $response->assertRedirect(route('admin.orders.show', $this->orderPendingPayment->id));
        // $response->assertSessionHas('info'); // Or 'error' depending on implementation

        $this->assertDatabaseHas('orders', [ // Ensure status did not change
            'id' => $this->orderPendingPayment->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('order_activities', [
            'order_id' => $this->orderPendingPayment->id,
            'type' => 'admin_confirmed_payment',
        ]);
    }

    public function test_non_admin_cannot_confirm_payment(): void
    {
        // Attempt with client user
        $this->actingAs($this->client);
        $response = $this->post(route('admin.orders.confirmPayment', ['order' => $this->orderPendingPayment->id]));
        $response->assertStatus(403); // Expecting Forbidden due to admin middleware/policy

        // Attempt with no authenticated user (guest)
        Auth::logout(); // Ensure no user is authenticated
        $response = $this->post(route('admin.orders.confirmPayment', ['order' => $this->orderPendingPayment->id]));
        $response->assertRedirect(route('login')); // Expect redirect to login
    }
}
