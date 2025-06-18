<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod; // Importar el modelo

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::updateOrCreate(
            ['slug' => 'paypal'], // Criterio de búsqueda
            [ // Valores a asegurar/crear
                'name' => 'PayPal',
                'type' => 'gateway', // Un tipo genérico para gateways automáticos
                'is_automatic' => true,
                'is_active' => true,
                // Los campos específicos de banco/info manual pueden dejarse null o con valores por defecto de la BD
                // ya que no aplican directamente a un gateway automático como PayPal de esta forma.
                // Si tu tabla tiene constraints NOT NULL sin default para esos campos,
                // necesitarás proveerles un valor o ajustar la tabla.
                // Por ejemplo, para campos string que no aplican:
                'bank_name' => '', // O un valor placeholder si es NOT NULL sin default
                'account_number' => '', // O un valor placeholder
                'account_holder_name' => '', // O un valor placeholder
            ]
        );

        PaymentMethod::updateOrCreate(
            ['slug' => 'balance'], // Criterio de búsqueda para Saldo de Cuenta
            [
                'name' => 'Saldo de Cuenta',
                'type' => 'internal', // Un tipo para métodos internos
                'is_automatic' => true, // El débito del saldo es automático
                'is_active' => true,
                'bank_name' => '',
                'account_number' => '',
                'account_holder_name' => '',
            ]
        );

        // Ejemplo para un método manual (si quieres añadirlo aquí)
        PaymentMethod::updateOrCreate(
            ['slug' => 'bank_transfer_ve'], // Ejemplo: Transferencia Bancaria Venezuela
            [
                'name' => 'Transferencia Bancaria (Venezuela)',
                'type' => 'bank',
                'is_automatic' => false,
                'is_active' => true,
                'bank_name' => 'Banco Ejemplo C.A.',
                'account_number' => '01020304050607080910',
                'account_holder_name' => 'Tu Empresa S.R.L.',
                'identification_number' => 'J-12345678-9', // RIF de la empresa
                'instructions' => 'Por favor, incluya el número de factura en la referencia de la transferencia.'
            ]
        );

        // Puedes añadir más métodos de pago aquí
    }
}
