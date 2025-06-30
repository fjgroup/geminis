<?php

require_once 'vendor/autoload.php';

use App\Models\ClientService;
use App\Services\PricingCalculatorService;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTUALIZAR BILLING AMOUNTS DE SERVICIOS (INCLUYENDO OPCIONES CONFIGURABLES) ===\n\n";

$services = ClientService::with(['product', 'productPricing.billingCycle'])
    ->whereHas('product')
    ->whereHas('productPricing')
    ->get();

echo "📊 Servicios encontrados: {$services->count()}\n\n";

$pricingCalculator = app(PricingCalculatorService::class);
$updated           = 0;
$errors            = 0;

foreach ($services as $service) {
    try {
        // Calcular precio correcto usando PricingCalculatorService
        $priceCalculation = $pricingCalculator->calculateProductPrice(
            $service->product_id,
            $service->billing_cycle_id,
            []// Sin opciones configurables para servicios existentes
        );

        $newBillingAmount = $priceCalculation['total'];
        $oldBillingAmount = $service->billing_amount;

        echo "📦 Servicio ID {$service->id} ({$service->product->name}):\n";
        echo "   Anterior: \${$oldBillingAmount}\n";
        echo "   Nuevo: \${$newBillingAmount}\n";

        if (abs($newBillingAmount - $oldBillingAmount) > 0.01) {
            echo "   ✅ ACTUALIZANDO...\n";

            $service->billing_amount = $newBillingAmount;
            $service->save();

            $updated++;
        } else {
            echo "   ✅ Ya tiene el precio correcto\n";
        }

        echo "\n";

    } catch (\Exception $e) {
        echo "❌ Error en servicio ID {$service->id}: " . $e->getMessage() . "\n\n";
        $errors++;
    }
}

echo "=== RESUMEN ===\n";
echo "✅ Servicios actualizados: {$updated}\n";
echo "❌ Errores: {$errors}\n";
echo "✅ Proceso completado!\n";
