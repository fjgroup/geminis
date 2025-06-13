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
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'slug')) {
                $table->string('slug')->unique()->after('name')->nullable(); // Nullable temporalmente para registros existentes
            }
            if (!Schema::hasColumn('payment_methods', 'is_automatic')) {
                $table->boolean('is_automatic')->default(false)->after('is_active');
            }
        });

        // Actualizar registros existentes para que tengan un slug si es posible
        // (Este es un ejemplo, la lógica real puede necesitar ser más robusta o hacerse en un seeder)
        // Se recomienda hacer esto manualmente o con un seeder dedicado después de ejecutar la migración,
        // especialmente si hay muchos datos o se necesita lógica compleja para generar slugs.
        // Por ahora, solo se añade la columna. El seeder se encargará de los valores iniciales.
        // Y luego, hacer que 'slug' no sea nullable si se desea
        // DB::table('payment_methods')->whereNull('slug')->update(['slug' => DB::raw('LOWER(REPLACE(name, " ", "-"))')]);
        // Schema::table('payment_methods', function (Blueprint $table) {
        //     $table->string('slug')->nullable(false)->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('payment_methods', 'is_automatic')) {
                $table->dropColumn('is_automatic');
            }
        });
    }
};
```
