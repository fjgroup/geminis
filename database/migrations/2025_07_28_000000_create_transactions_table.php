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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PK
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('reseller_id')->nullable()->constrained('users');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->string('gateway_slug');
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->enum('type', ['payment', 'refund', 'chargeback', 'credit_added', 'credit_used'])->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency_code', 3);
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->index();
            $table->decimal('fees_amount', 10, 2)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps(); // Handles created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
