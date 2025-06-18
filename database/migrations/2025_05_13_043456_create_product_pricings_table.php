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
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('billing_cycle_id')->constrained('billing_cycles')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->string('currency_code', 3)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'billing_cycle_id', 'currency_code'], 'product_cycle_currency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pricings');
    }
};
