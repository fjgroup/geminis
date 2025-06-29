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
            $table->string('name'); // Ej: "Descuento Hosting ECO", "Descuento Premium"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2); // Porcentaje de descuento (ej: 18.50)
            $table->boolean('is_active')->default(true);
            $table->json('applicable_product_types')->nullable(); // Tipos de productos donde aplica
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
