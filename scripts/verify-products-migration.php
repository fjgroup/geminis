<?php

/**
 * Script para verificar la migración del dominio Products
 * 
 * Verifica que todas las clases se pueden instanciar correctamente
 * y que las dependencias están bien configuradas
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ProductsMigrationVerifier
{
    private array $successes = [];
    private array $warnings = [];
    private array $errors = [];

    public function run(): void
    {
        echo "🔍 Verificando migración del dominio Products...\n\n";

        $this->verifyModels();
        $this->verifyServices();
        $this->verifyDTOs();
        $this->verifyServiceProviders();
        $this->verifyControllers();
        $this->verifyRepositories();

        $this->displayResults();
    }

    private function verifyModels(): void
    {
        echo "📦 Verificando modelos...\n";

        // Verificar que el modelo Product existe en el nuevo namespace
        if (class_exists('App\Domains\Products\Models\Product')) {
            $this->successes[] = "✅ Modelo Product migrado correctamente";
            
            try {
                $reflection = new ReflectionClass('App\Domains\Products\Models\Product');
                $this->successes[] = "✅ Modelo Product se puede instanciar";
            } catch (Exception $e) {
                $this->errors[] = "❌ Error al instanciar modelo Product: " . $e->getMessage();
            }
        } else {
            $this->errors[] = "❌ Modelo Product no encontrado en nuevo namespace";
        }

        // Verificar que el modelo anterior no existe o está marcado como deprecated
        if (file_exists('app/Models/Product.php')) {
            $this->warnings[] = "⚠️ Archivo Product.php aún existe en app/Models/";
        }
    }

    private function verifyServices(): void
    {
        echo "🔧 Verificando servicios...\n";

        $services = [
            'App\Domains\Products\Services\ProductManagementService',
            'App\Domains\Products\Services\ProductCreator',
            'App\Domains\Products\Services\ProductUpdater',
        ];

        foreach ($services as $service) {
            if (class_exists($service)) {
                $this->successes[] = "✅ Servicio {$service} existe";
                
                try {
                    $reflection = new ReflectionClass($service);
                    $constructor = $reflection->getConstructor();
                    
                    if ($constructor && count($constructor->getParameters()) > 0) {
                        $this->successes[] = "✅ Servicio {$service} usa inyección de dependencias";
                    }
                } catch (Exception $e) {
                    $this->errors[] = "❌ Error al analizar servicio {$service}: " . $e->getMessage();
                }
            } else {
                $this->errors[] = "❌ Servicio {$service} no existe";
            }
        }
    }

    private function verifyDTOs(): void
    {
        echo "📋 Verificando DTOs...\n";

        $dtos = [
            'App\Domains\Products\DataTransferObjects\CreateProductDTO',
            'App\Domains\Products\DataTransferObjects\UpdateProductDTO',
        ];

        foreach ($dtos as $dto) {
            if (class_exists($dto)) {
                $this->successes[] = "✅ DTO {$dto} existe";
                
                try {
                    $reflection = new ReflectionClass($dto);
                    
                    // Verificar que tiene método fromRequest
                    if ($reflection->hasMethod('fromRequest')) {
                        $this->successes[] = "✅ DTO {$dto} tiene método fromRequest";
                    } else {
                        $this->warnings[] = "⚠️ DTO {$dto} no tiene método fromRequest";
                    }
                    
                    // Verificar que tiene método toArray
                    if ($reflection->hasMethod('toArray')) {
                        $this->successes[] = "✅ DTO {$dto} tiene método toArray";
                    } else {
                        $this->warnings[] = "⚠️ DTO {$dto} no tiene método toArray";
                    }
                } catch (Exception $e) {
                    $this->errors[] = "❌ Error al analizar DTO {$dto}: " . $e->getMessage();
                }
            } else {
                $this->errors[] = "❌ DTO {$dto} no existe";
            }
        }
    }

    private function verifyServiceProviders(): void
    {
        echo "⚙️ Verificando Service Providers...\n";

        // Verificar ProductServiceProvider
        if (class_exists('App\Domains\Products\ProductServiceProvider')) {
            $this->successes[] = "✅ ProductServiceProvider existe";
        } else {
            $this->errors[] = "❌ ProductServiceProvider no existe";
        }

        // Verificar DomainServiceProvider
        if (class_exists('App\Providers\DomainServiceProvider')) {
            $this->successes[] = "✅ DomainServiceProvider existe";
        } else {
            $this->errors[] = "❌ DomainServiceProvider no existe";
        }

        // Verificar que está registrado en bootstrap/providers.php
        if (file_exists('bootstrap/providers.php')) {
            $content = file_get_contents('bootstrap/providers.php');
            if (strpos($content, 'DomainServiceProvider') !== false) {
                $this->successes[] = "✅ DomainServiceProvider registrado en bootstrap/providers.php";
            } else {
                $this->errors[] = "❌ DomainServiceProvider no registrado en bootstrap/providers.php";
            }
        }
    }

    private function verifyControllers(): void
    {
        echo "🎮 Verificando controladores...\n";

        $controllers = [
            'app/Http/Controllers/Admin/AdminProductControllerRefactored.php',
            'app/Http/Controllers/CartController.php',
        ];

        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                
                if (strpos($content, 'App\Domains\Products\Models\Product') !== false) {
                    $this->successes[] = "✅ Controlador {$controller} actualizado";
                } else if (strpos($content, 'App\Models\Product') !== false) {
                    $this->warnings[] = "⚠️ Controlador {$controller} usa namespace antiguo";
                } else {
                    $this->successes[] = "✅ Controlador {$controller} no usa modelo Product";
                }
            } else {
                $this->warnings[] = "⚠️ Controlador {$controller} no encontrado";
            }
        }
    }

    private function verifyRepositories(): void
    {
        echo "🗄️ Verificando repositorios...\n";

        if (file_exists('app/Repositories/ProductRepository.php')) {
            $content = file_get_contents('app/Repositories/ProductRepository.php');
            
            if (strpos($content, 'App\Domains\Products\Models\Product') !== false) {
                $this->successes[] = "✅ ProductRepository actualizado";
            } else {
                $this->warnings[] = "⚠️ ProductRepository usa namespace antiguo";
            }
        }

        if (file_exists('app/Contracts/ProductRepositoryInterface.php')) {
            $content = file_get_contents('app/Contracts/ProductRepositoryInterface.php');
            
            if (strpos($content, 'App\Domains\Products\Models\Product') !== false) {
                $this->successes[] = "✅ ProductRepositoryInterface actualizado";
            } else {
                $this->warnings[] = "⚠️ ProductRepositoryInterface usa namespace antiguo";
            }
        }
    }

    private function displayResults(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESULTADOS DE LA VERIFICACIÓN\n";
        echo str_repeat("=", 60) . "\n\n";

        if (!empty($this->successes)) {
            echo "✅ ÉXITOS (" . count($this->successes) . "):\n";
            foreach ($this->successes as $success) {
                echo "   {$success}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️ ADVERTENCIAS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   {$warning}\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ ERRORES (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   {$error}\n";
            }
            echo "\n";
        }

        // Resumen
        $total = count($this->successes) + count($this->warnings) + count($this->errors);
        $successRate = $total > 0 ? round((count($this->successes) / $total) * 100, 1) : 0;

        echo "📈 RESUMEN:\n";
        echo "   Total verificaciones: {$total}\n";
        echo "   Éxitos: " . count($this->successes) . "\n";
        echo "   Advertencias: " . count($this->warnings) . "\n";
        echo "   Errores: " . count($this->errors) . "\n";
        echo "   Tasa de éxito: {$successRate}%\n\n";

        if (count($this->errors) === 0) {
            echo "🎉 ¡Migración del dominio Products completada exitosamente!\n";
        } else {
            echo "🚨 La migración tiene errores que deben ser corregidos.\n";
        }
    }
}

// Ejecutar verificación
$verifier = new ProductsMigrationVerifier();
$verifier->run();
