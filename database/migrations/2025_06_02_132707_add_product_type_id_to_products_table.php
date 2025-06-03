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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_type_id')
                  ->nullable()
                  ->after('id') // Placing it near the start for clarity, or after 'type' if preferred
                  ->constrained('product_types') // Assumes the table is named 'product_types'
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Or ->onDelete('restrict') or ->onDelete('cascade') depending on desired behavior
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Convention for dropping foreign keys: tablename_columnname_foreign
            $table->dropForeign(['product_type_id']); // Or $table->dropForeign('products_product_type_id_foreign');
            $table->dropColumn('product_type_id');
        });
    }
};
