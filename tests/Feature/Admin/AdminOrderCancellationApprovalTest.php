<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\OrderActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;


class AdminOrderCancellationApprovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $client;
    private Order $orderCancellationRequested;
    private Invoice $invoicePaid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create([
            'role' => 'client',
            'balance' => 50.00 // Initial balance
        ]);

        $orderTotal = 100.00;

        $this->orderCancellationRequested = Order::factory()->for($this->client)->create([
            'status' => 'cancellation_requested_by_client',
            'total_amount' => $orderTotal,
            'order_number' => 'ORD-' . Str::uuid(),
        ]);

        $this->invoicePaid = Invoice::factory()->for($this->client)->create([
            'status' => 'paid', // Invoice was paid before cancellation request
            'total_amount' => $orderTotal,
            'invoice_number' => 'INV-' . Str::uuid(),
        ]);

        $this->orderCancellationRequested->update(['invoice_id' => $this->invoicePaid->id]);

        // Create order items and corresponding invoice items
        $orderItem = OrderItem::factory()->for($this->orderCancellationRequested)->create([
            'quantity' => 1,
            'unit_price' => $orderTotal,
            'total_price' => $orderTotal,
        ]);
        InvoiceItem::factory()->for($this->invoicePaid)->create([
            'order_item_id' => $orderItem->id,
            'quantity' => $orderItem->quantity,
            'unit_price' => $orderItem->unit_price,
            'total_price' => $orderItem->total_price,
        ]);
    }

    public function test_admin_can_approve_cancellation_request_and_refund_is_processed(): void
    {
        $this->actingAs($this->admin);

        $initialClientBalance = $this->client->balance;
        $orderTotal = $this->orderCancellationRequested->total_amount;

        $response = $this->post(route('admin.orders.approveCancellation', ['order' => $this->orderCancellationRequested->id]));

        $response->assertRedirect(route('admin.orders.show', $this->orderCancellationRequested->id));
        // $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $this->orderCancellationRequested->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $this->invoicePaid->id,
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $this->invoicePaid->id,
            'client_id' => $this->client->id,
            'type' => 'credit_added',
            'amount' => $orderTotal,
            'status' => 'completed',
        ]);

        $this->client->refresh(); // Refresh client model to get updated balance
        $this->assertEquals($initialClientBalance + $orderTotal, $this->client->balance);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $this->orderCancellationRequested->id,
            'user_id' => $this->admin->id,
            'type' => 'cancellation_approved_credit_issued',
        ]);
    }

    public function test_admin_cannot_approve_cancellation_for_order_not_requesting_it(): void
    {
        $this->actingAs($this->admin);
        $this->orderCancellationRequested->update(['status' => 'active']); // Change status

        $response = $this->post(route('admin.orders.approveCancellation', ['order' => $this->orderCancellationRequested->id]));
        
        $response->assertRedirect(route('admin.orders.show', $this->orderCancellationRequested->id));
        // $response->assertSessionHas('error'); // Or 'info'

        $this->assertDatabaseHas('orders', [
            'id' => $this->orderCancellationRequested->id,
            'status' => 'active', // Status should not change
        ]);
    }
    
    public function test_non_admin_cannot_approve_cancellation_request(): void
    {
        $this->actingAs($this->client); // Authenticate as client

        $response = $this->post(route('admin.orders.approveCancellation', ['order' => $this->orderCancellationRequested->id]));
        $response->assertStatus(403); // Expect Forbidden
    }
}
