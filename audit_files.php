<?php

/**
 * Script de auditoría para comparar archivos entre referencia y hexagonal
 * 
 * Genera inventario completo para identificar archivos faltantes
 */

function scanDirectory($dir, $basePath = '') {
    $files = [];
    if (!is_dir($dir)) {
        return $files;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        $relativePath = $basePath ? $basePath . '/' . $item : $item;
        
        if (is_dir($fullPath)) {
            $files = array_merge($files, scanDirectory($fullPath, $relativePath));
        } else {
            $files[] = $relativePath;
        }
    }
    
    return $files;
}

echo "🔍 AUDITORÍA DE ARCHIVOS - MIGRACIÓN HEXAGONAL\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Inventario de referencia
echo "📋 Inventariando archivos de REFERENCIA...\n";
$referenceFiles = scanDirectory('fjgroupca_NO_SOLID_NO_HEXAGONAL_NO_DDD/app');
echo "✅ Encontrados " . count($referenceFiles) . " archivos en referencia\n\n";

// Inventario hexagonal
echo "📋 Inventariando archivos HEXAGONALES...\n";
$hexagonalFiles = scanDirectory('app');
echo "✅ Encontrados " . count($hexagonalFiles) . " archivos en hexagonal\n\n";

// Análisis por categorías
$categories = [
    'Controllers' => [],
    'Models' => [],
    'Requests' => [],
    'Services' => [],
    'Actions' => [],
    'Policies' => [],
    'Repositories' => [],
    'Others' => []
];

// Categorizar archivos de referencia
foreach ($referenceFiles as $file) {
    if (strpos($file, 'Controllers') !== false) {
        $categories['Controllers'][] = $file;
    } elseif (strpos($file, 'Models') !== false) {
        $categories['Models'][] = $file;
    } elseif (strpos($file, 'Requests') !== false) {
        $categories['Requests'][] = $file;
    } elseif (strpos($file, 'Services') !== false) {
        $categories['Services'][] = $file;
    } elseif (strpos($file, 'Actions') !== false) {
        $categories['Actions'][] = $file;
    } elseif (strpos($file, 'Policies') !== false) {
        $categories['Policies'][] = $file;
    } elseif (strpos($file, 'Repositories') !== false) {
        $categories['Repositories'][] = $file;
    } else {
        $categories['Others'][] = $file;
    }
}

echo "📊 ANÁLISIS POR CATEGORÍAS:\n";
echo "-" . str_repeat("-", 30) . "\n";
foreach ($categories as $category => $files) {
    echo sprintf("%-15s: %3d archivos\n", $category, count($files));
}
echo "\n";

// Identificar archivos críticos faltantes
echo "🚨 ARCHIVOS CRÍTICOS A REVISAR:\n";
echo "-" . str_repeat("-", 40) . "\n";

$criticalFiles = [
    'Controllers' => $categories['Controllers'],
    'Services' => $categories['Services'],
    'Actions' => $categories['Actions']
];

foreach ($criticalFiles as $category => $files) {
    echo "\n📁 {$category}:\n";
    foreach ($files as $file) {
        echo "   - {$file}\n";
    }
}

echo "\n";
echo "💾 Guardando inventario completo...\n";

// Guardar inventario completo
$inventory = [
    'timestamp' => date('Y-m-d H:i:s'),
    'reference_files' => $referenceFiles,
    'hexagonal_files' => $hexagonalFiles,
    'categories' => $categories,
    'stats' => [
        'total_reference' => count($referenceFiles),
        'total_hexagonal' => count($hexagonalFiles),
        'by_category' => array_map('count', $categories)
    ]
];

file_put_contents('file_inventory.json', json_encode($inventory, JSON_PRETTY_PRINT));
echo "✅ Inventario guardado en file_inventory.json\n\n";

echo "🎯 PRÓXIMOS PASOS:\n";
echo "1. Revisar Controllers faltantes\n";
echo "2. Migrar Services críticos\n";
echo "3. Verificar Actions necesarias\n";
echo "4. Completar cada dominio al 100%\n";
