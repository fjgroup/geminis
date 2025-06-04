<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'pending_configuration' to the ENUM list for the status column
        // Existing values: 'pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud'
        // It's important to list all existing values plus the new one.
        // The default value can remain 'pending' or be explicitly set if needed.
        DB::statement("ALTER TABLE client_services MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert the ENUM list to its original state.
        // This could be lossy if 'pending_configuration' was used.
        // Data with 'pending_configuration' would need to be handled (e.g., converted to 'pending') before this migration is run down.
        DB::statement("ALTER TABLE client_services MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud') NOT NULL DEFAULT 'pending'");
    }
};
