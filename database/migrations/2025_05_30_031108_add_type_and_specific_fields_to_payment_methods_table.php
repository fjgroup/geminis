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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('type')->default('bank')->after('name');
            $table->string('identification_number')->nullable()->after('account_holder_name');
            // Assuming 'platform_name', 'email_address', 'payment_link' should come after identification_number
            // and before bank_name, or perhaps grouped logically.
            // For now, placing them sequentially after identification_number.
            $table->string('platform_name')->nullable()->after('identification_number');
            $table->string('email_address')->nullable()->after('platform_name');
            $table->string('payment_link')->nullable()->after('email_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'identification_number', 'platform_name', 'email_address', 'payment_link']);
        });
    }
};
