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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // Campos de OrderItem fusionados
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('product_pricing_id')->nullable()->constrained('product_pricings')->onDelete('set null');
            $table->decimal('setup_fee', 10, 2)->nullable()->default(0.00);
            $table->string('domain_name')->nullable();
            $table->integer('registration_period_years')->nullable();
            $table->string('item_type')->nullable()->default('manual_item')->comment('Ej: new_service, renewal, upgrade, addon, manual_item');
            // Fin de campos fusionados

            $table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null')->comment('Para ítems de renovación o servicios existentes');
            // $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null')->comment('Para ítems originados de una orden'); // Eliminado
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->boolean('taxable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
