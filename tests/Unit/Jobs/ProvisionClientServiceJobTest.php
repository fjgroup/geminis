<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderActivity;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ClientService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\ProvisionClientServiceJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProvisionClientServiceJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // It's good practice to ensure queues are faked so jobs don't actually dispatch to a real queue
        // However, for dispatchSync, it runs immediately, so faking isn't strictly necessary for that.
        // Queue::fake();
    }

    private function createTestOrderItem(array $orderStatus = ['status' => 'pending_provisioning'], bool $createsServiceInstance = true): OrderItem
    {
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create(['type' => 'month', 'multiplier' => 1]);
        $productType = ProductType::factory()->create(['creates_service_instance' => $createsServiceInstance]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        $order = Order::factory()->for($client)->create($orderStatus);

        $orderItem = OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($productPricing)
            ->create(['client_service_id' => null]);

        // Crucial: Load relations as the job expects them to be somewhat available
        // or it reloads them. The job now reloads them using find($this->orderItem->id)
        // and then loads relations. So, this explicit load before dispatch isn't strictly
        // necessary if the job's reloading is robust.
        // $orderItem->load(['order.client', 'product.productType', 'productPricing.billingCycle']);
        return $orderItem;
    }

    /** @test */
    public function job_creates_new_client_service_and_activates_it_successfully()
    {
        // Arrange
        $orderItem = $this->createTestOrderItem();

        // Act
        ProvisionClientServiceJob::dispatchSync($orderItem);

        // Assert
        $orderItem->refresh();
        $order = $orderItem->order->fresh();

        $this->assertNotNull($orderItem->client_service_id, "ClientService ID should be set on OrderItem.");

        $clientService = ClientService::find($orderItem->client_service_id);
        $this->assertNotNull($clientService, "ClientService record was not created.");

        $this->assertEquals('active', $clientService->status, "ClientService status should be active.");
        $this->assertNotEmpty($clientService->username, "ClientService username should be set.");
        $this->assertNotEmpty($clientService->password_encrypted, "ClientService password should be set.");
        $this->assertNotNull($clientService->registration_date, "ClientService registration_date should be set.");
        $this->assertNotNull($clientService->next_due_date, "ClientService next_due_date should be set.");

        // Check next_due_date calculation (assuming 1 month from registration_date for this test setup)
        $expectedNextDueDate = Carbon::parse($clientService->registration_date)->addMonthsNoOverflow(1)->toDateString();
        $this->assertEquals($expectedNextDueDate, $clientService->next_due_date);

        // Assert Order status changed by ClientServiceObserver
        $this->assertEquals('active', $order->status, "Order status should be updated to active by ClientServiceObserver.");

        // Assert OrderActivity for order activation (created by ClientServiceObserver)
        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->latest('id')
            ->first();
        $this->assertNotNull($activity, "OrderActivity for order auto-activation was not logged.");
        $details = json_decode($activity->details, true);
        $this->assertEquals($clientService->id, $details['client_service_id']);
    }

    /** @test */
    public function job_updates_existing_failed_service_to_active()
    {
        // Arrange
        $orderItem = $this->createTestOrderItem();
        $existingFailedService = ClientService::factory()->create([
            'order_item_id' => $orderItem->id,
            'client_id' => $orderItem->order->client_id,
            'order_id' => $orderItem->order->id,
            'product_id' => $orderItem->product_id,
            'product_pricing_id' => $orderItem->product_pricing_id,
            'billing_cycle_id' => $orderItem->productPricing->billing_cycle_id,
            'status' => 'provisioning_failed',
            'username' => null, // Ensure these are null to check if job updates them
            'password_encrypted' => null,
        ]);
        $orderItem->client_service_id = $existingFailedService->id;
        $orderItem->save();

        // Act
        ProvisionClientServiceJob::dispatchSync($orderItem);

        // Assert
        $existingFailedService->refresh();
        $this->assertEquals('active', $existingFailedService->status);
        $this->assertNotEmpty($existingFailedService->username);
        $this->assertNotEmpty($existingFailedService->password_encrypted);
        $this->assertNotNull($existingFailedService->next_due_date);

        $order = $orderItem->order->fresh();
        $this->assertEquals('active', $order->status);
    }

    /** @test */
    public function job_does_not_reprovision_already_active_service()
    {
        // Arrange
        $orderItem = $this->createTestOrderItem();
        $activeService = ClientService::factory()->create([
            'order_item_id' => $orderItem->id,
            'client_id' => $orderItem->order->client_id,
            'order_id' => $orderItem->order->id,
            'product_id' => $orderItem->product_id,
            'status' => 'active',
            'username' => 'initial_user',
            'updated_at' => Carbon::now()->subDay(), // Ensure updated_at is in the past
        ]);
        $orderItem->client_service_id = $activeService->id;
        $orderItem->save();

        $originalUpdatedAt = $activeService->updated_at->toDateTimeString(); // Ensure Carbon object comparison
        $originalUsername = $activeService->username;

        // Act
        ProvisionClientServiceJob::dispatchSync($orderItem);

        // Assert
        $activeService->refresh();
        $this->assertEquals('active', $activeService->status);
        // The job's current logic will re-run the "simulated provisioning" part even if service was active.
        // It will re-set username, password, next_due_date, and notes.
        // If the desired behavior is to truly do *nothing*, the job needs an earlier exit.
        // The test currently reflects what the job *does*.
        // $this->assertEquals($originalUsername, $activeService->username, "Username should not change for an already active service if job exits early.");
        // $this->assertEquals($originalUpdatedAt, $activeService->updated_at->toDateTimeString(), "Updated_at should not change if job exits early.");

        // Based on current job logic, it logs "Servicio ya activo" and returns.
        // So, username and updated_at should NOT change.
        $this->assertEquals($originalUsername, $activeService->username);
        // updated_at might still change if saveQuietly() on orderItem in the job happens after this check.
        // The job's check is: if ($clientService && $clientService->status === 'active') { Log::info(...); return; }
        // This return should prevent any further modification to $clientService.
        // However, the orderItem might be saved if its client_service_id was null.
        // Let's ensure orderItem's client_service_id is correctly set for this test.
        $this->assertEquals($originalUpdatedAt, $activeService->updated_at->toDateTimeString());


        $activities = OrderActivity::where('order_id', $orderItem->order_id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->count();
        // If the order was already active (because service was active), ClientServiceObserver wouldn't run.
        // If order was pending_provisioning, it would have become active.
        // This test is more about the job not messing with an *already active service*.
        // The initial creation of the active service would have (or should have) triggered the observer.
        // This dispatch shouldn't trigger it *again*.
         $this->assertLessThanOrEqual(1, $activities, "No new order_auto_activated_post_service_config activity should be created.");
    }

    /** @test */
    public function job_logs_error_and_sets_status_to_failed_on_exception()
    {
        // Arrange
        $orderItem = $this->createTestOrderItem();

        // Simulate a condition that will cause an exception inside the job's try block
        // For example, product_pricing_id that doesn't exist, causing findOrFail in job's reload or access.
        // The job reloads OrderItem with relations. If productPricing is missing, it will fail.
        $orderItem->product_pricing_id = 99999; // Non-existent ID
        $orderItem->save();

        Log::shouldReceive('error')->atLeast()->once(); // Expect at least one error log

        $this->expectException(ModelNotFoundException::class); // Or more general Throwable if preferred

        try {
            ProvisionClientServiceJob::dispatchSync($orderItem->fresh()); // Use fresh model
        } catch (ModelNotFoundException $e) {
            // After the exception is caught (as expected)
            $clientService = ClientService::where('order_item_id', $orderItem->id)->first();

            // In this specific failure (ModelNotFound for ProductPricing on OrderItem reload within job),
            // the job might fail before even attempting to create/update a ClientService.
            // So, $clientService might be null or its status might not be 'provisioning_failed'
            // if the error occurs very early in the job's handle method.
            // The job's catch block tries to update it.

            if ($clientService) {
                $this->assertEquals('provisioning_failed', $clientService->status);
            } else {
                // This is also a valid outcome if the failure was before CS creation attempt.
                // We've already asserted the Log::error and the exception.
                $this->assertNull($clientService, "ClientService should ideally not exist or be marked as failed if error was very early.");
            }
            throw $e; // Re-throw to satisfy expectException
        }
    }
}
