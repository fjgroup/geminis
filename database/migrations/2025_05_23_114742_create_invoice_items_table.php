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
            $table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null')->comment('Para ítems de renovación');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null')->comment('Para ítems originados de una orden');
            $table->string('description'); // Ej: "Web Hosting - Plan Básico (Renovación Mensual)"
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2); // unit_price * quantity
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
