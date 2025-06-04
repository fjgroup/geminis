<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_services', function (Blueprint $table) {
            // Add order_id if it wasn't successfully added/uncommented previously
            // Ensure it's placed appropriately, e.g., after reseller_id
            if (!Schema::hasColumn('client_services', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('reseller_id')->constrained('orders')->onDelete('set null')->comment('FK a la orden que originó este servicio');
            }

            // Add order_item_id
            if (!Schema::hasColumn('client_services', 'order_item_id')) {
                $table->foreignId('order_item_id')->nullable()->after('order_id')->constrained('order_items')->onDelete('set null')->comment('FK al ítem de orden específico que generó este servicio');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_services', function (Blueprint $table) {
            if (Schema::hasColumn('client_services', 'order_item_id')) {
                // Laravel's convention for foreign key names is table_column_foreign
                // Need to ensure the foreign key name is correct if not following convention or if specific name was used.
                // Default convention: client_services_order_item_id_foreign
                $table->dropForeign(['order_item_id']);
                $table->dropColumn('order_item_id');
            }
            if (Schema::hasColumn('client_services', 'order_id')) {
                // Default convention: client_services_order_id_foreign
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
