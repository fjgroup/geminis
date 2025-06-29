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
        Schema::create('product_configurable_option_groups', function (Blueprint $table) {
            $table->id();
            // Constraint para product_id
            $table->foreignId('product_id')
                ->constrained('products', indexName: 'prod_conf_opt_group_product_id_fk') // Nombre corto para la FK
                ->onDelete('cascade');
            // Constraint para configurable_option_group_id
            $table->foreignId('configurable_option_group_id')
                ->constrained('configurable_option_groups', indexName: 'prod_conf_opt_group_group_id_fk') // Nombre corto para la FK
                ->onDelete('cascade');
            $table->integer('display_order')->default(0);        // Orden de este grupo para este producto específico
            $table->decimal('base_quantity', 10, 2)->default(0); // Cantidad base incluida en el producto
            $table->timestamps();
            $table->unique(['product_id', 'configurable_option_group_id'], 'product_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_configurable_option_groups');
    }
};
