<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::updateOrCreate(
            ['email' => 'admin@fjgroupca.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@fjgroupca.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'company_name' => 'Fj Group CA',
                'phone' => '+58 412 8172337',
                'country' => 'VE',
                'reseller_id' => null,
                'status' => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario cliente de prueba
        User::updateOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name' => 'Cliente Prueba',
                'email' => 'cliente@test.com',
                'password' => Hash::make('cliente123'),
                'role' => 'client',
                'company_name' => 'Empresa Test',
                'phone' => '+58 412 1234567',
                'country' => 'VE',
                'reseller_id' => null,
                'status' => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario reseller de prueba
        User::updateOrCreate(
            ['email' => 'reseller@test.com'],
            [
                'name' => 'Reseller Prueba',
                'email' => 'reseller@test.com',
                'password' => Hash::make('reseller123'),
                'role' => 'reseller',
                'company_name' => 'Reseller Company',
                'phone' => '+58 412 7654321',
                'country' => 'VE',
                'reseller_id' => null,
                'status' => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
                'email_verified_at' => now(),
            ]
        );

        echo "✅ Usuarios creados exitosamente:\n";
        echo "👤 Admin: admin@fjgroupca.com / admin123\n";
        echo "👤 Cliente: cliente@test.com / cliente123\n";
        echo "👤 Reseller: reseller@test.com / reseller123\n";
    }
}
