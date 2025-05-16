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
        Schema::create('configurable_option_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
            $table->foreignId('product_pricing_id')->constrained('product_pricings')->onDelete('cascade'); // Asumiendo que tienes una tabla product_pricings para los ciclos
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurable_option_pricings');
    }
};
