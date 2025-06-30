<?php

// Script de prueba para verificar el parsing de configuraciones adicionales

// Simular datos de un servicio con configuraciones adicionales
$serviceNotes = "5 GB de espacio web adicional\n2 vCPU adicionales\n1 GB de RAM adicional";

// Simular el método parseIndividualOption del controlador
function parseIndividualOption($noteText, $billingCycleId = 1) {
    // Patrones para extraer información de las notas
    $patterns = [
        // "X GB de espacio web adicional"
        '/(\d+(?:\.\d+)?)\s+GB\s+de\s+espacio\s+web\s+adicional/i' => [
            'slug' => 'espacio-en-disco',
            'name' => 'Espacio en Disco',
            'unit' => 'GB',
        ],
        // "X vCPU adicionales"
        '/(\d+(?:\.\d+)?)\s+vCPU\s+adicionales/i' => [
            'slug' => 'vcpu',
            'name' => 'vCPU',
            'unit' => 'vCPU',
        ],
        // "X GB de RAM adicional"
        '/(\d+(?:\.\d+)?)\s+GB\s+de\s+RAM\s+adicional/i' => [
            'slug' => 'vram',
            'name' => 'vRAM',
            'unit' => 'GB',
        ],
    ];

    foreach ($patterns as $pattern => $config) {
        if (preg_match($pattern, $noteText, $matches)) {
            $quantity = isset($matches[1]) ? (float) $matches[1] : 1;

            // Precios de fallback para prueba
            $fallbackPrices = [
                'espacio-en-disco' => 0.50,
                'vcpu' => 5.00,
                'vram' => 1.00,
            ];

            $unitPrice = $fallbackPrices[$config['slug']] ?? 0;
            return [
                'name' => $config['name'],
                'quantity' => $quantity,
                'unit' => $config['unit'],
                'unit_price' => $unitPrice,
                'price' => $quantity * $unitPrice,
                'description' => $noteText,
            ];
        }
    }

    return null;
}

// Simular el método parseConfigurableOptionsWithPrices
function parseConfigurableOptionsWithPrices($notes) {
    $options = [];
    $totalPrice = 0;

    if (empty($notes)) {
        return ['options' => $options, 'total_price' => $totalPrice];
    }

    $noteLines = explode("\n", $notes);

    foreach ($noteLines as $note) {
        $note = trim($note);
        if (empty($note)) {
            continue;
        }

        $optionData = parseIndividualOption($note, 1);
        if ($optionData) {
            $options[] = $optionData;
            $totalPrice += $optionData['price'];
        }
    }

    return ['options' => $options, 'total_price' => $totalPrice];
}

// Probar el parsing
echo "=== PRUEBA DE PARSING DE CONFIGURACIONES ADICIONALES ===\n\n";
echo "Notas del servicio:\n";
echo $serviceNotes . "\n\n";

$result = parseConfigurableOptionsWithPrices($serviceNotes);

echo "Opciones parseadas:\n";
foreach ($result['options'] as $option) {
    echo "- {$option['quantity']} {$option['unit']} de {$option['name']}: \${$option['unit_price']} por {$option['unit']} = \${$option['price']}\n";
}

echo "\nTotal de configuraciones adicionales: \${$result['total_price']}\n";

echo "\n=== ESTRUCTURA DE DATOS PARA EL FRONTEND ===\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
