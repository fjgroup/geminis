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
        Schema::create('configurable_option_groups', function (Blueprint $table) {
            $table->id();
            // Puede ser global (NULL) o específico de un producto
            // Asegúrate de que la tabla 'products' exista antes de ejecutar esta migración
            // o considera añadir la constraint en una migración posterior si 'products' se crea después.
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(false);
            $table->json('metadata')->nullable(); // Para datos adicionales futuros
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurable_option_groups');
    }
};
