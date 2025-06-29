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
            // Agregar campo JSON para recursos base dinámicos
            $table->json('base_resources')->nullable()->after('display_order');

            // Eliminar campos estáticos de recursos base
            $table->dropColumn([
                'base_disk_space_gb',
                'base_vcpu_cores',
                'base_ram_gb',
                'base_bandwidth_gb',
                'base_email_accounts',
                'base_databases',
                'base_domains',
                'base_subdomains',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restaurar campos estáticos
            $table->decimal('base_disk_space_gb', 8, 2)->default(0)->after('display_order');
            $table->integer('base_vcpu_cores')->default(0)->after('base_disk_space_gb');
            $table->decimal('base_ram_gb', 8, 2)->default(0)->after('base_vcpu_cores');
            $table->integer('base_bandwidth_gb')->default(0)->after('base_ram_gb');
            $table->integer('base_email_accounts')->default(0)->after('base_bandwidth_gb');
            $table->integer('base_databases')->default(0)->after('base_email_accounts');
            $table->integer('base_domains')->default(0)->after('base_databases');
            $table->integer('base_subdomains')->default(0)->after('base_domains');

            // Eliminar campo dinámico
            $table->dropColumn('base_resources');
        });
    }
};
