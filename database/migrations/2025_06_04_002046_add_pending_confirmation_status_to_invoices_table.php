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
        Schema::table('invoices', function (Blueprint $table) {
            // Ensure existing values are included, and new value is added.
            // The order matters for some databases if binary representation is used. Best to append.
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
                'unpaid',
                'paid',
                'overdue',
                'cancelled',
                'refunded',
                'collections',
                'pending_confirmation'
            ) NOT NULL DEFAULT 'unpaid'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revert to the original list, excluding the new value.
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
                'unpaid',
                'paid',
                'overdue',
                'cancelled',
                'refunded',
                'collections'
            ) NOT NULL DEFAULT 'unpaid'");
        });
    }
};
