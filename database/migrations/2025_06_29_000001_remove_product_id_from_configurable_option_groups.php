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
        Schema::table('configurable_option_groups', function (Blueprint $table) {
            // Eliminar la foreign key constraint primero
            $table->dropForeign(['product_id']);
            // Luego eliminar la columna
            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurable_option_groups', function (Blueprint $table) {
            // Restaurar la columna y la foreign key
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
        });
    }
};
