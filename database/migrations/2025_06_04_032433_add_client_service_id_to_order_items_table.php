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
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'client_service_id')) {
                // Attempt to place after 'registration_period_years'.
                // If 'registration_period_years' does not exist, Laravel will append it.
                // This requires checking the existing order_items migration to confirm 'registration_period_years' presence.
                // For now, assuming it exists or appending is acceptable.
                $table->foreignId('client_service_id')
                      ->nullable()
                      // ->after('registration_period_years') // This might cause issues if the column isn't there.
                                                            // It's safer to check the original migration or omit after() if not critical.
                                                            // Let's assume 'product_pricing_id' is a safe bet to place it after,
                                                            // or simply let Laravel append it. For this task, I'll try after 'product_pricing_id'.
                                                            // If the column 'registration_period_years' is confirmed, it can be used.
                                                            // Based on previous files, 'registration_period_years' might not be on order_items but on client_services.
                                                            // A common column on order_items is 'product_pricing_id' or 'domain_name'.
                                                            // Let's try after 'domain_name' as it's often present.
                                                            // If 'domain_name' is also not guaranteed, it's best to omit after() or check the table structure first.
                                                            // For now, I will omit after() to ensure it runs without knowing the exact previous column.
                                                            // The user can adjust placement if needed by editing the migration manually.
                      ->constrained('client_services')
                      ->onDelete('set null')
                      ->comment('FK al servicio de cliente generado por este ítem de orden');
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
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'client_service_id')) {
                // Default convention for foreign key name: order_items_client_service_id_foreign
                $table->dropForeign(['client_service_id']);
                $table->dropColumn('client_service_id');
            }
        });
    }
};
