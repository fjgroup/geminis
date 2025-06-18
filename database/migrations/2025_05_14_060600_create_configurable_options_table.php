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
            $table->string('value')->nullable(); // Valor interno para aprovisionamiento (ej: "centos7")
            $table->integer('display_order')->default(0);
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
