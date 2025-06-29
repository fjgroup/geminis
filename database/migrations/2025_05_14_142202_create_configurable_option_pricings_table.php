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
            $table->foreignId('billing_cycle_id')->constrained('billing_cycles')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->string('currency_code', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Para configuraciones adicionales
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['configurable_option_id', 'billing_cycle_id'], 'unique_option_billing_cycle');
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
