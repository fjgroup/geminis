<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResellerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario reseller de prueba
        $reseller = User::updateOrCreate(
            ['email' => 'reseller@test.com'],
            [
                'name' => 'Reseller Test',
                'email' => 'reseller@test.com',
                'password' => Hash::make('password'),
                'role' => 'reseller',
                'company_name' => 'Diseños Web Pro',
                'phone' => '+58 412 1234567',
                'country' => 'VE',
                'reseller_id' => null, // Los resellers no tienen reseller_id
                'status' => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
                'email_verified_at' => now(),
            ]
        );

        // Crear algunos clientes para este reseller
        $clients = [
            [
                'name' => 'Cliente Reseller 1',
                'email' => 'cliente1@reseller.com',
                'company_name' => 'Empresa Cliente 1',
            ],
            [
                'name' => 'Cliente Reseller 2', 
                'email' => 'cliente2@reseller.com',
                'company_name' => 'Empresa Cliente 2',
            ],
        ];

        foreach ($clients as $clientData) {
            User::updateOrCreate(
                ['email' => $clientData['email']],
                [
                    'name' => $clientData['name'],
                    'email' => $clientData['email'],
                    'password' => Hash::make('password'),
                    'role' => 'client',
                    'company_name' => $clientData['company_name'],
                    'phone' => '+58 412 7654321',
                    'country' => 'VE',
                    'reseller_id' => $reseller->id, // Asignar al reseller
                    'status' => 'active',
                    'language_code' => 'es',
                    'currency_code' => 'USD',
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Usuario reseller y clientes creados:');
        $this->command->info('   Reseller: reseller@test.com / password');
        $this->command->info('   Clientes: cliente1@reseller.com, cliente2@reseller.com / password');
    }
}
