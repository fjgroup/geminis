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
        // Existing values: 'pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration'
        // Add 'provisioning_failed'
        DB::statement("ALTER TABLE client_services MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration', 'provisioning_failed') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to the ENUM list without 'provisioning_failed'
        // This could be lossy if 'provisioning_failed' was used.
        // Data with 'provisioning_failed' would need to be handled (e.g., converted to 'pending' or another error state) before this migration is run down.
        DB::statement("ALTER TABLE client_services MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration') NOT NULL DEFAULT 'pending'");
    }
};
