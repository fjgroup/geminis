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
        Schema::create('configurable_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('configurable_option_groups')->onDelete('cascade');
            $table->string('name'); // Nombre visible de la opción (ej: "CentOS 7")
            $table->string('slug')->unique();
            $table->string('value')->nullable(); // Valor interno para aprovisionamiento (ej: "centos7")
            $table->text('description')->nullable();
            $table->enum('option_type', ['dropdown', 'radio', 'checkbox', 'quantity', 'text'])->default('dropdown');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('min_value', 10, 2)->nullable(); // Para opciones de cantidad
            $table->decimal('max_value', 10, 2)->nullable(); // Para opciones de cantidad
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable(); // Para configuraciones adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurable_options');
    }
};
