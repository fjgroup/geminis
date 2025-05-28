<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_activities', function (Blueprint $table) {
            $table->id(); // bigIncrements
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // User performing the action

            $table->enum('type', [
                'order_requested_by_client',
                'invoice_generated_for_order', // Could be part of order_requested
                'invoice_paid_by_client',
                'order_ready_for_admin_execution',
                'admin_started_provisioning',
                'service_activated',
                'cancellation_requested_by_client_paid',
                'cancellation_approved_credit_issued',
                'cancellation_request_denied_by_admin',
                'admin_note_added_to_order',
                // Add other types as needed in future migrations
            ])->index();

            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at, as these are immutable logs
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_activities');
    }
};
