<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_configurable_option_groups', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('base_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_configurable_option_groups', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
};
