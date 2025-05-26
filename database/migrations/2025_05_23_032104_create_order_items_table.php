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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_pricing_id')->constrained('product_pricings'); // Ciclo de facturación elegido
            $table->enum('item_type', ['product', 'addon', 'domain_registration', 'domain_renewal', 'domain_transfer', 'configurable_option'])->index();
            $table->string('description'); // Ej: "Web Hosting - Plan Básico (Mensual)"
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2); // (unit_price * quantity) + setup_fee
            $table->string('domain_name')->nullable(); // Si el ítem es un dominio
            $table->integer('registration_period_years')->nullable(); // Para dominios
            $table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null'); // Se llenará después de aprovisionar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
