<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'role'              => 'admin',
            'status'            => 'active',
            'email_verified_at' => now(),
            'company_name'      => 'Test Company',
            'phone'             => '+1234567890',
            'address'           => 'Test Address',
            'city'              => 'Test City',
            'state'             => 'Test State',
            'postal_code'       => '12345',
            'country'           => 'Test Country',
        ]);

        $this->call([
            // Primero los ciclos de facturación
            BillingCycleSeeder::class,

            // Tipos de productos
            ProductTypeSeeder::class,

            // Métodos de pago
            PaymentMethodSeeder::class,

            // Productos (primero todos los productos)
            GenericDomainProductSeeder::class,
            HostingProductsSeeder::class,
            //ResellerProductSeeder::class,

            // Descuentos (después de que existan los productos)
            DiscountPercentageSeeder::class,

            // Opciones configurables (después de que TODOS los productos existan)
            ConfigurableOptionsSeeder::class,
        ]);
    }
}
