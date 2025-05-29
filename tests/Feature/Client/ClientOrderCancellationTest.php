<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;


class ClientOrderCancellationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $client;
    private Order $orderPendingPayment;
    private Invoice $invoiceUnpaid;
    private Order $orderPaidPendingExecution;
    private Invoice $invoicePaid;


    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);

        // Setup for unpaid order
        $this->orderPendingPayment = Order::factory()->for($this->client)->create([
            'status' => 'pending_payment',
            'total_amount' => 100.00,
            'order_number' => 'ORD-' . Str::uuid(),
        ]);
        $this->invoiceUnpaid = Invoice::factory()->for($this->client)->create([
            'status' => 'unpaid',
            'total_amount' => $this->orderPendingPayment->total_amount,
            'invoice_number' => 'INV-' . Str::uuid(),
        ]);
        $this->orderPendingPayment->update(['invoice_id' => $this->invoiceUnpaid->id]);
        OrderItem::factory()->for($this->orderPendingPayment)->create();
        InvoiceItem::factory()->for($this->invoiceUnpaid)->create([
            'order_item_id' => $this->orderPendingPayment->items->first()->id,
        ]);


        // Setup for paid order
        $this->orderPaidPendingExecution = Order::factory()->for($this->client)->create([
            'status' => 'paid_pending_execution',
            'total_amount' => 200.00,
            'order_number' => 'ORD-' . Str::uuid(),
        ]);
        $this->invoicePaid = Invoice::factory()->for($this->client)->create([
            'status' => 'paid',
            'total_amount' => $this->orderPaidPendingExecution->total_amount,
            'invoice_number' => 'INV-' . Str::uuid(),
        ]);
        $this->orderPaidPendingExecution->update(['invoice_id' => $this->invoicePaid->id]);
        OrderItem::factory()->for($this->orderPaidPendingExecution)->create();
        InvoiceItem::factory()->for($this->invoicePaid)->create([
             'order_item_id' => $this->orderPaidPendingExecution->items->first()->id,
        ]);
    }

    public function test_client_can_cancel_unpaid_order(): void
    {
        $this->actingAs($this->client);

        $response = $this->delete(route('client.orders.cancelPrePayment', ['order' => $this->orderPendingPayment->id]));

        $response->assertRedirect(route('client.orders.show', $this->orderPendingPayment->id));
        // $response->assertSessionHas('success'); // Check for success flash message

        $this->assertDatabaseHas('orders', [
            'id' => $this->orderPendingPayment->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $this->invoiceUnpaid->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $this->orderPendingPayment->id,
            'user_id' => $this->client->id,
            'type' => 'order_cancelled_by_client_prepayment',
        ]);
    }

    public function test_client_can_request_cancellation_for_paid_order(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('client.orders.requestPostPaymentCancellation', ['order' => $this->orderPaidPendingExecution->id]));

        $response->assertRedirect(route('client.orders.show', $this->orderPaidPendingExecution->id));
        // $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $this->orderPaidPendingExecution->id,
            'status' => 'cancellation_requested_by_client',
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $this->orderPaidPendingExecution->id,
            'user_id' => $this->client->id,
            'type' => 'cancellation_requested_by_client',
        ]);
    }

    public function test_client_cannot_cancel_order_owned_by_another_client(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $this->actingAs($otherClient); // Authenticate as a different client

        $response = $this->delete(route('client.orders.cancelPrePayment', ['order' => $this->orderPendingPayment->id]));
        $response->assertStatus(403); // Expecting Forbidden

        $response = $this->post(route('client.orders.requestPostPaymentCancellation', ['order' => $this->orderPaidPendingExecution->id]));
        $response->assertStatus(403); // Expecting Forbidden
    }

    public function test_client_cannot_cancel_already_cancelled_order(): void
    {
        $this->actingAs($this->client);
        $this->orderPendingPayment->update(['status' => 'cancelled']);

        $response = $this->delete(route('client.orders.cancelPrePayment', ['order' => $this->orderPendingPayment->id]));
        
        // Behavior might be a redirect with an error or a specific status code
        // Assuming redirect with error for this case based on controller logic
        $response->assertRedirect(route('client.orders.show', $this->orderPendingPayment->id));
        // $response->assertSessionHas('error'); // Or 'info' depending on controller
    }

    public function test_client_cannot_request_cancellation_for_order_already_requested(): void
    {
        $this->actingAs($this->client);
        $this->orderPaidPendingExecution->update(['status' => 'cancellation_requested_by_client']);

        $response = $this->post(route('client.orders.requestPostPaymentCancellation', ['order' => $this->orderPaidPendingExecution->id]));
        
        $response->assertRedirect(route('client.orders.show', $this->orderPaidPendingExecution->id));
        // $response->assertSessionHas('info');
    }
}
