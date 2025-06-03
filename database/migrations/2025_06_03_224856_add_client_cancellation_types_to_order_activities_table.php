<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('order_activities', function (Blueprint $table) {
            DB::statement("ALTER TABLE order_activities MODIFY COLUMN type ENUM(
                'order_requested_by_client',
                'invoice_generated_for_order',
                'invoice_paid_by_client',
                'order_ready_for_admin_execution',
                'admin_started_provisioning',
                'service_activated',
                'cancellation_requested_by_client_paid',
                'cancellation_approved_credit_issued',
                'cancellation_request_denied_by_admin',
                'admin_note_added_to_order',
                'order_cancelled_by_client_prepayment',
                'cancellation_requested_by_client'
            ) NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_activities', function (Blueprint $table) {
            DB::statement("ALTER TABLE order_activities MODIFY COLUMN type ENUM(
                'order_requested_by_client',
                'invoice_generated_for_order',
                'invoice_paid_by_client',
                'order_ready_for_admin_execution',
                'admin_started_provisioning',
                'service_activated',
                'cancellation_requested_by_client_paid',
                'cancellation_approved_credit_issued',
                'cancellation_request_denied_by_admin',
                'admin_note_added_to_order'
            ) NOT NULL");
        });
    }
};
