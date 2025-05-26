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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('reseller_id')->nullable()->constrained('users');
            $table->string('order_number')->unique();
            $table->foreignId('invoice_id')->nullable()->unique()->constrained('invoices')->onDelete('set null'); // Se llenará después de generar la factura
            $table->timestamp('order_date');
            $table->enum('status', ['pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled'])->default('pending_payment')->index();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency_code', 3);
            $table->string('payment_gateway_slug')->nullable()->index();
            $table->ipAddress('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
