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
        Schema::create('discount_percentages', function (Blueprint $table) {
            $table->id();
            // Campos para definir qué producto y ciclo reciben el descuento
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('billing_cycle_id')->constrained('billing_cycles')->onDelete('cascade');
            $table->string('name'); // Ej: "Descuento Hosting ECO Anual", "Descuento Pro Trimestral"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2); // Porcentaje de descuento (ej: 18.50)
            $table->boolean('is_active')->default(true);

            // Índice único para evitar duplicados de producto+ciclo
            $table->unique(['product_id', 'billing_cycle_id'], 'unique_product_billing_cycle');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_percentages');
    }
};
