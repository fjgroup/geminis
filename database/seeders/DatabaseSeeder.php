<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*   User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        $this->call([
            // Primero los descuentos (necesarios para billing cycles)
            DiscountPercentageSeeder::class,

            // Luego los ciclos de facturación (con descuentos)
            BillingCycleSeeder::class,

            // Tipos de productos
            ProductTypeSeeder::class,

            // Métodos de pago
            PaymentMethodSeeder::class,

            // Producto de dominio genérico
            GenericDomainProductSeeder::class,

            // Opciones configurables (después de productos)
            ConfigurableOptionsSeeder::class,

            // Productos de hosting (después de opciones configurables)
            HostingProductsSeeder::class,
        ]);
    }
}
