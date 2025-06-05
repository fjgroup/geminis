<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderItem;
use App\Models\OrderActivity;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Observers\InvoiceObserver; // For direct testing if needed, though usually through model events
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event; // If needing to assert events explicitly

class InvoiceObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Manually register the observer if not relying on auto-discovery in tests
        // or if testing a specific instance. For model event testing, this is usually not needed.
        // Event::observe(InvoiceObserver::class); // Not needed if AppServiceProvider registers it
    }

    /** @test */
    public function order_status_updates_to_pending_provisioning_when_invoice_is_paid()
    {
        // 1. Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create([
            'status' => 'pending_payment',
        ]);
        $invoice = Invoice::factory()->for($client)->for($order)->create([
            'status' => 'unpaid',
            'total_amount' => $order->total_amount, // Ensure amounts match
            'currency_code' => $order->currency_code,
        ]);

        // Ensure the relationship is set if not done by factories
        $order->invoice_id = $invoice->id;
        $order->save();
        $invoice->order_id = $order->id; // Ensure this is also set for invoice->order access in observer
        $invoice->save();


        // 2. Act
        $invoice->status = 'paid';
        $invoice->save(); // This should trigger the InvoiceObserver's updating method

        // 3. Assert
        $order->refresh();
        $this->assertEquals('pending_provisioning', $order->status);

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_status_auto_updated_to_pending_provisioning')
            ->latest('id') // Get the latest one in case of multiple activities
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($order->id, $activity->order_id);
        $details = json_decode($activity->details, true);
        $this->assertEquals($invoice->id, $details['invoice_id']);
        $this->assertEquals($invoice->invoice_number, $details['invoice_number']);
        $this->assertEquals('pending_payment', $details['previous_order_status']);
        $this->assertEquals('pending_provisioning', $details['new_order_status']);
    }

    /** @test */
    public function order_status_does_not_change_if_order_is_already_active_when_invoice_is_paid()
    {
        // 1. Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'active']);
        $invoice = Invoice::factory()->for($client)->for($order)->create(['status' => 'unpaid']);

        $order->invoice_id = $invoice->id;
        $order->save();
        $invoice->order_id = $order->id;
        $invoice->save();

        // 2. Act
        $invoice->status = 'paid';
        $invoice->save();

        // 3. Assert
        $order->refresh();
        $this->assertEquals('active', $order->status); // Status should remain active

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_status_auto_updated_to_pending_provisioning')
            ->first();
        $this->assertNull($activity); // No new activity should be logged for this specific type
    }

    /** @test */
    public function order_status_does_not_change_if_order_is_paid_pending_execution_when_invoice_is_paid()
    {
        // This tests the scenario where the observer might be too aggressive.
        // Based on the current InvoiceObserver, if order is 'paid_pending_execution', it *should* change to 'pending_provisioning'.
        // So this test name might be misleading if the expectation is for it *not* to change.
        // Let's assume the observer logic is: 'pending_payment' OR 'paid_pending_execution' -> 'pending_provisioning'

        // 1. Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'paid_pending_execution']);
        $invoice = Invoice::factory()->for($client)->for($order)->create(['status' => 'unpaid']);

        $order->invoice_id = $invoice->id;
        $order->save();
        $invoice->order_id = $order->id;
        $invoice->save();

        // 2. Act
        $invoice->status = 'paid';
        $invoice->save();

        // 3. Assert
        $order->refresh();
        $this->assertEquals('pending_provisioning', $order->status); // Status should change

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_status_auto_updated_to_pending_provisioning')
            ->latest('id')
            ->first();
        $this->assertNotNull($activity);
        $details = json_decode($activity->details, true);
        $this->assertEquals('paid_pending_execution', $details['previous_order_status']);
        $this->assertEquals('pending_provisioning', $details['new_order_status']);
    }


    /** @test */
    public function no_order_update_if_invoice_is_paid_but_has_no_associated_order()
    {
        // 1. Arrange
        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create([
            'status' => 'unpaid',
            'order_id' => null, // No associated order
        ]);

        // 2. Act
        $invoice->status = 'paid';
        $invoice->save(); // This should trigger the observer

        // 3. Assert
        // Primarily, we assert that no error occurs and no OrderActivity is mistakenly created for a non-existent order.
        $activities = OrderActivity::where('type', 'order_status_auto_updated_to_pending_provisioning')
                                    ->whereJsonContains('details->invoice_id', $invoice->id)
                                    ->get();
        $this->assertCount(0, $activities, "No order activity should be logged for an invoice without an order.");
    }

    /** @test */
    public function invoice_observer_does_not_change_order_status_if_invoice_status_changes_but_not_to_paid()
    {
        // 1. Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);
        $invoice = Invoice::factory()->for($client)->for($order)->create(['status' => 'unpaid']);

        $order->invoice_id = $invoice->id;
        $order->save();
        $invoice->order_id = $order->id;
        $invoice->save();

        // 2. Act
        $invoice->status = 'cancelled'; // Changing to something other than 'paid'
        $invoice->save();

        // 3. Assert
        $order->refresh();
        $this->assertEquals('pending_payment', $order->status); // Status should NOT change

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_status_auto_updated_to_pending_provisioning')
            ->first();
        $this->assertNull($activity);
    }

     /** @test */
    public function invoice_observer_does_not_change_order_status_if_invoice_status_is_already_paid_and_resaved()
    {
        // 1. Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']); // Initial order status
        $invoice = Invoice::factory()->for($client)->for($order)->create(['status' => 'unpaid']);

        $order->invoice_id = $invoice->id;
        $order->save();
        $invoice->order_id = $order->id;
        $invoice->save();

        // First payment
        $invoice->status = 'paid';
        $invoice->save();
        $order->refresh();
        $this->assertEquals('pending_provisioning', $order->status); // Verify first change

        // 2. Act
        // Now, save the invoice again without changing its 'paid' status
        // For example, an unrelated field on the invoice is updated
        $invoice->notes = 'Some updated notes';
        $invoice->save(); // This should trigger the observer, but the condition for status change shouldn't pass

        // 3. Assert
        $order->refresh();
        $this->assertEquals('pending_provisioning', $order->status); // Status should remain 'pending_provisioning'

        // Check that a second activity for status change was NOT logged
        $activities = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_status_auto_updated_to_pending_provisioning')
            ->get();
        $this->assertCount(1, $activities, "Only one activity for status change should exist.");
    }
}
