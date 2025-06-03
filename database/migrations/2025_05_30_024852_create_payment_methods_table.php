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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('bank');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->string('identification_number')->nullable();
            $table->string('platform_name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('payment_link')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('iban')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('logo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
