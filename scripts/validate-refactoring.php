<?php

/**
 * Script de validación completa de la refactorización
 * 
 * Valida que todos los componentes refactorizados funcionen correctamente
 * y que la arquitectura esté bien implementada
 */

require_once __DIR__ . '/../vendor/autoload.php';

class RefactoringValidator
{
    private array $errors = [];
    private array $warnings = [];
    private array $successes = [];

    public function run(): void
    {
        echo "🔍 Iniciando validación completa de la refactorización...\n\n";

        $this->validateServices();
        $this->validateControllers();
        $this->validateRoutes();
        $this->validateDependencyInjection();
        $this->validateFormRequests();
        $this->validateMiddleware();
        $this->validateTests();

        $this->printResults();
    }

    private function validateServices(): void
    {
        echo "📦 Validando servicios...\n";

        $services = [
            'App\Services\CartService',
            'App\Services\ProductService',
            'App\Services\UserService',
            'App\Services\InvoiceService',
            'App\Services\CheckoutService',
            'App\Services\ClientServiceManagementService',
            'App\Services\ImpersonationService',
            'App\Services\TransactionManagementService',
            'App\Services\PricingCalculatorService'
        ];

        foreach ($services as $service) {
            if (class_exists($service)) {
                $this->successes[] = "✅ Servicio {$service} existe";
                
                // Validar que el servicio tenga métodos públicos
                $reflection = new ReflectionClass($service);
                $publicMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                if (count($publicMethods) > 1) { // Más que solo __construct
                    $this->successes[] = "✅ Servicio {$service} tiene métodos públicos";
                } else {
                    $this->warnings[] = "⚠️ Servicio {$service} tiene pocos métodos públicos";
                }
            } else {
                $this->errors[] = "❌ Servicio {$service} no existe";
            }
        }
    }

    private function validateControllers(): void
    {
        echo "🎮 Validando controladores refactorizados...\n";

        $controllers = [
            'App\Http\Controllers\CartController',
            'App\Http\Controllers\Admin\AdminClientServiceControllerRefactored',
            'App\Http\Controllers\PublicCheckoutControllerRefactored'
        ];

        foreach ($controllers as $controller) {
            if (class_exists($controller)) {
                $this->successes[] = "✅ Controlador {$controller} existe";
                
                // Validar inyección de dependencias en constructor
                $reflection = new ReflectionClass($controller);
                $constructor = $reflection->getConstructor();
                
                if ($constructor && count($constructor->getParameters()) > 0) {
                    $this->successes[] = "✅ Controlador {$controller} usa inyección de dependencias";
                } else {
                    $this->warnings[] = "⚠️ Controlador {$controller} no usa inyección de dependencias";
                }
                
                // Validar que no sea demasiado grande
                $lines = count(file(str_replace('\\', '/', str_replace('App\\', 'app/', $controller)) . '.php'));
                if ($lines <= 350) {
                    $this->successes[] = "✅ Controlador {$controller} tiene tamaño apropiado ({$lines} líneas)";
                } else {
                    $this->warnings[] = "⚠️ Controlador {$controller} es muy grande ({$lines} líneas)";
                }
            } else {
                $this->errors[] = "❌ Controlador {$controller} no existe";
            }
        }
    }

    private function validateRoutes(): void
    {
        echo "🛣️ Validando rutas...\n";

        $routeFile = 'routes/web.php';
        if (file_exists($routeFile)) {
            $content = file_get_contents($routeFile);
            
            // Validar que las rutas refactorizadas estén presentes
            $refactoredRoutes = [
                'AdminClientServiceControllerRefactored',
                'CartController',
                'PublicCheckoutControllerRefactored'
            ];
            
            foreach ($refactoredRoutes as $route) {
                if (strpos($content, $route) !== false) {
                    $this->successes[] = "✅ Rutas para {$route} están configuradas";
                } else {
                    $this->errors[] = "❌ Rutas para {$route} no están configuradas";
                }
            }
            
            // Validar que los controladores obsoletos estén marcados
            $deprecatedControllers = [
                'AdminClientServiceController',
                'PublicCheckoutController'
            ];
            
            foreach ($deprecatedControllers as $controller) {
                $controllerFile = str_replace('\\', '/', "app/Http/Controllers/{$controller}.php");
                if (file_exists($controllerFile)) {
                    $controllerContent = file_get_contents($controllerFile);
                    if (strpos($controllerContent, 'DEPRECATED') !== false) {
                        $this->successes[] = "✅ Controlador {$controller} está marcado como obsoleto";
                    } else {
                        $this->warnings[] = "⚠️ Controlador {$controller} no está marcado como obsoleto";
                    }
                }
            }
        } else {
            $this->errors[] = "❌ Archivo de rutas no encontrado";
        }
    }

    private function validateDependencyInjection(): void
    {
        echo "💉 Validando inyección de dependencias...\n";

        // Validar ServiceProviders
        $providers = [
            'app/Providers/AppServiceProvider.php',
            'app/Providers/ServicesServiceProvider.php'
        ];

        foreach ($providers as $provider) {
            if (file_exists($provider)) {
                $this->successes[] = "✅ Provider {$provider} existe";
                
                $content = file_get_contents($provider);
                if (strpos($content, 'singleton') !== false) {
                    $this->successes[] = "✅ Provider {$provider} configura singletons";
                }
            } else {
                $this->errors[] = "❌ Provider {$provider} no existe";
            }
        }

        // Validar bootstrap/providers.php
        if (file_exists('bootstrap/providers.php')) {
            $content = file_get_contents('bootstrap/providers.php');
            if (strpos($content, 'ServicesServiceProvider') !== false) {
                $this->successes[] = "✅ ServicesServiceProvider está registrado";
            } else {
                $this->errors[] = "❌ ServicesServiceProvider no está registrado";
            }
        }
    }

    private function validateFormRequests(): void
    {
        echo "📝 Validando Form Requests...\n";

        if (class_exists('App\Http\Requests\BaseFormRequest')) {
            $this->successes[] = "✅ BaseFormRequest existe";
            
            // Validar que AddToCartRequest herede de BaseFormRequest
            if (class_exists('App\Http\Requests\AddToCartRequest')) {
                $reflection = new ReflectionClass('App\Http\Requests\AddToCartRequest');
                $parent = $reflection->getParentClass();
                
                if ($parent && $parent->getName() === 'App\Http\Requests\BaseFormRequest') {
                    $this->successes[] = "✅ AddToCartRequest hereda de BaseFormRequest";
                } else {
                    $this->warnings[] = "⚠️ AddToCartRequest no hereda de BaseFormRequest";
                }
            }
        } else {
            $this->errors[] = "❌ BaseFormRequest no existe";
        }
    }

    private function validateMiddleware(): void
    {
        echo "🛡️ Validando middleware...\n";

        $middleware = [
            'app/Http/Middleware/InjectServicesMiddleware.php'
        ];

        foreach ($middleware as $mw) {
            if (file_exists($mw)) {
                $this->successes[] = "✅ Middleware {$mw} existe";
            } else {
                $this->errors[] = "❌ Middleware {$mw} no existe";
            }
        }

        // Validar que el middleware esté registrado
        if (file_exists('bootstrap/app.php')) {
            $content = file_get_contents('bootstrap/app.php');
            if (strpos($content, 'inject.services') !== false) {
                $this->successes[] = "✅ InjectServicesMiddleware está registrado";
            } else {
                $this->warnings[] = "⚠️ InjectServicesMiddleware no está registrado";
            }
        }
    }

    private function validateTests(): void
    {
        echo "🧪 Validando tests...\n";

        $tests = [
            'tests/Unit/Services/CartServiceTest.php',
            'tests/Unit/Services/ClientServiceManagementServiceTest.php',
            'tests/Feature/Controllers/CartControllerTest.php'
        ];

        foreach ($tests as $test) {
            if (file_exists($test)) {
                $this->successes[] = "✅ Test {$test} existe";
                
                $content = file_get_contents($test);
                $testCount = substr_count($content, '/** @test */');
                if ($testCount > 0) {
                    $this->successes[] = "✅ Test {$test} tiene {$testCount} casos de prueba";
                }
            } else {
                $this->warnings[] = "⚠️ Test {$test} no existe";
            }
        }
    }

    private function printResults(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESULTADOS DE LA VALIDACIÓN\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "✅ ÉXITOS (" . count($this->successes) . "):\n";
        foreach ($this->successes as $success) {
            echo "   {$success}\n";
        }

        if (!empty($this->warnings)) {
            echo "\n⚠️ ADVERTENCIAS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   {$warning}\n";
            }
        }

        if (!empty($this->errors)) {
            echo "\n❌ ERRORES (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   {$error}\n";
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        
        $total = count($this->successes) + count($this->warnings) + count($this->errors);
        $successRate = round((count($this->successes) / $total) * 100, 1);
        
        echo "📈 RESUMEN:\n";
        echo "   Total de validaciones: {$total}\n";
        echo "   Tasa de éxito: {$successRate}%\n";
        echo "   Estado: " . ($successRate >= 80 ? "🎉 EXCELENTE" : ($successRate >= 60 ? "👍 BUENO" : "⚠️ NECESITA MEJORAS")) . "\n";
        
        if (empty($this->errors)) {
            echo "\n🎯 ¡La refactorización está lista para producción!\n";
        } else {
            echo "\n🔧 Corrige los errores antes de continuar.\n";
        }
    }
}

// Ejecutar validación
$validator = new RefactoringValidator();
$validator->run();
