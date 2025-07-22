<?php

/**
 * Script para limpiar imports no utilizados después de la refactorización
 * 
 * Este script identifica y reporta imports que ya no se utilizan
 * después de mover lógica de negocio a servicios
 */

require_once __DIR__ . '/../vendor/autoload.php';

class UnusedImportsCleanup
{
    private array $controllersToCheck = [
        'app/Http/Controllers/Admin/AdminClientServiceController.php',
        'app/Http/Controllers/PublicCheckoutController.php',
        'app/Http/Controllers/Client/ClientCartController.php',
        'app/Http/Controllers/Shop/CartController.php',
    ];

    private array $deprecatedControllers = [
        'app/Http/Controllers/Admin/AdminClientServiceController.php',
        'app/Http/Controllers/PublicCheckoutController.php',
    ];

    public function run(): void
    {
        echo "🧹 Iniciando limpieza de imports no utilizados...\n\n";

        foreach ($this->controllersToCheck as $controller) {
            $this->analyzeController($controller);
        }

        echo "\n📋 Resumen de controladores deprecados:\n";
        foreach ($this->deprecatedControllers as $controller) {
            echo "❌ {$controller} - MARCADO PARA ELIMINACIÓN\n";
        }

        echo "\n✅ Análisis completado.\n";
    }

    private function analyzeController(string $filePath): void
    {
        if (!file_exists($filePath)) {
            echo "⚠️  Archivo no encontrado: {$filePath}\n";
            return;
        }

        $content = file_get_contents($filePath);
        $imports = $this->extractImports($content);
        $unusedImports = $this->findUnusedImports($content, $imports);

        echo "📁 Analizando: {$filePath}\n";
        echo "   📦 Total imports: " . count($imports) . "\n";
        echo "   🗑️  Imports no utilizados: " . count($unusedImports) . "\n";

        if (!empty($unusedImports)) {
            echo "   📝 Imports a remover:\n";
            foreach ($unusedImports as $import) {
                echo "      - {$import}\n";
            }
        }

        echo "\n";
    }

    private function extractImports(string $content): array
    {
        $imports = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^use\s+([^;]+);/', $line, $matches)) {
                $fullImport = trim($matches[1]);
                
                // Extraer el nombre de la clase
                if (strpos($fullImport, ' as ') !== false) {
                    // Manejo de alias: use App\Models\User as UserModel;
                    $parts = explode(' as ', $fullImport);
                    $className = trim($parts[1]);
                } else {
                    // Import normal: use App\Models\User;
                    $parts = explode('\\', $fullImport);
                    $className = end($parts);
                }

                $imports[$className] = $fullImport;
            }
        }

        return $imports;
    }

    private function findUnusedImports(string $content, array $imports): array
    {
        $unusedImports = [];

        foreach ($imports as $className => $fullImport) {
            // Buscar uso de la clase en el contenido
            $isUsed = $this->isClassUsed($content, $className);

            if (!$isUsed) {
                $unusedImports[] = $fullImport;
            }
        }

        return $unusedImports;
    }

    private function isClassUsed(string $content, string $className): bool
    {
        // Patrones para buscar uso de la clase
        $patterns = [
            // Uso directo de la clase
            '/\b' . preg_quote($className, '/') . '\b/',
            // Como tipo hint
            '/\b' . preg_quote($className, '/') . '\s+\$/',
            // En instanciación
            '/new\s+' . preg_quote($className, '/') . '\b/',
            // En métodos estáticos
            '/' . preg_quote($className, '/') . '::/',
            // En comentarios de tipo
            '/@var\s+' . preg_quote($className, '/') . '\b/',
            '/@param\s+' . preg_quote($className, '/') . '\b/',
            '/@return\s+' . preg_quote($className, '/') . '\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}

// Ejecutar el script
$cleanup = new UnusedImportsCleanup();
$cleanup->run();
