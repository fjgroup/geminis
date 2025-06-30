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
        Schema::create('order_configurable_options', function (Blueprint $table) {
            $table->id();

            // Relación con la orden/factura (cuando se implemente)
            $table->string('order_id')->nullable()->comment('ID de la orden/factura');
            $table->string('cart_item_id')->nullable()->comment('ID del item en el carrito');

            // Información del producto y cliente
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('client_email')->nullable()->comment('Email del cliente');

            // Detalles de la opción configurada
            $table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
            $table->foreignId('configurable_option_group_id')->constrained('configurable_option_groups')->onDelete('cascade');

            // Valores configurados por el cliente
            $table->string('option_name')->comment('Nombre de la opción al momento de la compra');
            $table->string('group_name')->comment('Nombre del grupo al momento de la compra');
            $table->decimal('quantity', 10, 2)->default(1)->comment('Cantidad seleccionada por el cliente');
            $table->json('option_value')->nullable()->comment('Valor configurado (para opciones complejas)');

            // Precios al momento de la compra
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario al momento de la compra');
            $table->decimal('total_price', 10, 2)->comment('Precio total (unit_price * quantity)');
            $table->string('currency_code', 3)->default('USD');
            $table->foreignId('billing_cycle_id')->constrained('billing_cycles')->onDelete('cascade');

            // Metadatos
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable()->comment('Información adicional');

            $table->timestamps();

            // Índices para consultas eficientes
            $table->index(['product_id', 'client_email']);
            $table->index(['cart_item_id']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_configurable_options');
    }
};
