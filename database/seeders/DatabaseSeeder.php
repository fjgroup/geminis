<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::factory()->create([
            'name'              => 'Admin User',
            'email'             => 'admin@geminis.test',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'admin',
        ]);

        // Crear usuario cliente
        User::factory()->create([
            'name'              => 'Cliente Test',
            'email'             => 'cliente@geminis.test',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'client',
            'company_name'      => 'Empresa Test',
            'phone'             => '+1234567890',
            'address'           => '123 Test Street',
            'city'              => 'Test City',
            'country'           => 'Test Country',
        ]);

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

            // Producto reseller temporal
            ResellerProductSeeder::class,
        ]);
    }
}
