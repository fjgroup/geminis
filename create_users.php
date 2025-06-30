<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Creando usuarios de prueba...\n\n";

try {
    // Crear usuario administrador
    $admin = User::updateOrCreate(
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

    echo "✅ Usuario Admin creado: admin@fjgroupca.com / admin123\n";

    // Crear usuario cliente de prueba
    $client = User::updateOrCreate(
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

    echo "✅ Usuario Cliente creado: cliente@test.com / cliente123\n";

    // Crear usuario reseller de prueba
    $reseller = User::updateOrCreate(
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

    echo "✅ Usuario Reseller creado: reseller@test.com / reseller123\n";

    echo "\n🎉 ¡Todos los usuarios creados exitosamente!\n";
    echo "\n📋 CREDENCIALES DE ACCESO:\n";
    echo "👤 Admin Panel: https://geminis.test/admin\n";
    echo "   Email: admin@fjgroupca.com\n";
    echo "   Password: admin123\n\n";
    echo "👤 Cliente Panel: https://geminis.test/client\n";
    echo "   Email: cliente@test.com\n";
    echo "   Password: cliente123\n\n";
    echo "👤 Reseller Panel: https://geminis.test/reseller\n";
    echo "   Email: reseller@test.com\n";
    echo "   Password: reseller123\n\n";
    echo "🔗 Login General: https://geminis.test/login\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
