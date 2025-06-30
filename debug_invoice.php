<?php

require_once 'vendor/autoload.php';

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderConfigurableOption;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANÁLISIS DE LA ÚLTIMA FACTURA ===\n\n";

// Obtener la última factura
$lastInvoice = Invoice::with(['items', 'client'])->latest()->first();

if (!$lastInvoice) {
    echo "❌ No se encontraron facturas.\n";
    exit;
}

echo "📄 FACTURA ID: {$lastInvoice->id}\n";
echo "👤 CLIENTE: {$lastInvoice->client->name} ({$lastInvoice->client->email})\n";
echo "💰 SUBTOTAL: {$lastInvoice->subtotal} {$lastInvoice->currency_code}\n";
echo "💰 TOTAL: {$lastInvoice->total_amount} {$lastInvoice->currency_code}\n";
echo "📅 CREADA: {$lastInvoice->created_at}\n\n";

echo "=== ITEMS DE LA FACTURA ===\n";
$totalCalculado = 0;

foreach ($lastInvoice->items as $item) {
    echo "🔸 ITEM ID: {$item->id}\n";
    echo "   📦 Producto: {$item->product_id}\n";
    echo "   📝 Descripción: {$item->description}\n";
    echo "   🔢 Cantidad: {$item->quantity}\n";
    echo "   💵 Precio Unitario: {$item->unit_price}\n";
    echo "   💵 Setup Fee: {$item->setup_fee}\n";
    echo "   💰 Total Item: {$item->total_price}\n";
    echo "   🏷️ Tipo: {$item->item_type}\n";
    echo "   🌐 Dominio: " . ($item->domain_name ?: 'N/A') . "\n";
    echo "\n";
    
    $totalCalculado += $item->total_price;
}

echo "=== RESUMEN ===\n";
echo "💰 Total calculado (suma items): {$totalCalculado}\n";
echo "💰 Subtotal en factura: {$lastInvoice->subtotal}\n";
echo "💰 Total en factura: {$lastInvoice->total_amount}\n";

if ($totalCalculado != $lastInvoice->subtotal) {
    echo "⚠️  DISCREPANCIA DETECTADA!\n";
    echo "   Diferencia: " . ($lastInvoice->subtotal - $totalCalculado) . "\n";
}

// Buscar opciones configurables relacionadas
echo "\n=== OPCIONES CONFIGURABLES ===\n";
$configurableOptions = OrderConfigurableOption::where('is_active', true)
    ->whereIn('cart_item_id', function($query) use ($lastInvoice) {
        // Buscar cart_item_ids que podrían estar relacionados con esta factura
        // Esto es una aproximación ya que no tenemos relación directa
        $query->select('cart_item_id')
              ->from('order_configurable_options')
              ->where('created_at', '>=', $lastInvoice->created_at->subMinutes(10))
              ->where('created_at', '<=', $lastInvoice->created_at->addMinutes(10));
    })
    ->get();

if ($configurableOptions->count() > 0) {
    echo "📋 Opciones configurables encontradas:\n";
    foreach ($configurableOptions as $option) {
        echo "   🔧 {$option->group_name}: {$option->option_name}\n";
        echo "      Cantidad: {$option->quantity}\n";
        echo "      Precio Total: {$option->total_price}\n";
        echo "      Cart Item ID: {$option->cart_item_id}\n\n";
    }
} else {
    echo "❌ No se encontraron opciones configurables para esta factura.\n";
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";
