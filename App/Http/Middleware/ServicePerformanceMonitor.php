<?php

namespace App\Http\Middleware;

use App\Services\PerformanceOptimizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para monitorear la performance de servicios en requests HTTP
 */
class ServicePerformanceMonitor
{
    public function __construct(
        private PerformanceOptimizationService $performanceService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Ejecutar el request
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // en milisegundos
        $memoryUsed = $endMemory - $startMemory;
        
        // Solo monitorear requests que tomen más de 100ms o usen más de 5MB
        if ($executionTime > 100 || $memoryUsed > 5 * 1024 * 1024) {
            $this->logPerformanceMetrics($request, $response, $executionTime, $memoryUsed);
        }
        
        // Agregar headers de performance en desarrollo
        if (app()->environment('local', 'development')) {
            $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsed));
            $response->headers->set('X-Peak-Memory', $this->formatBytes(memory_get_peak_usage(true)));
        }
        
        return $response;
    }

    /**
     * Log performance metrics for analysis
     */
    private function logPerformanceMetrics(Request $request, Response $response, float $executionTime, int $memoryUsed): void
    {
        $route = $request->route();
        $controller = null;
        $action = null;
        
        if ($route) {
            $routeAction = $route->getAction();
            if (isset($routeAction['controller'])) {
                [$controller, $action] = explode('@', $routeAction['controller']);
                $controller = class_basename($controller);
            }
        }
        
        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'controller' => $controller,
            'action' => $action,
            'execution_time_ms' => round($executionTime, 2),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'response_status' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ];
        
        // Log como warning si es muy lento
        if ($executionTime > 1000) { // > 1 segundo
            Log::warning('ServicePerformanceMonitor - Request muy lento', $metrics);
        } else {
            Log::info('ServicePerformanceMonitor - Performance metrics', $metrics);
        }
        
        // Almacenar métricas para análisis posterior
        $this->storeMetricsForAnalysis($metrics);
    }

    /**
     * Store metrics for later analysis
     */
    private function storeMetricsForAnalysis(array $metrics): void
    {
        try {
            // Usar cache para almacenar métricas temporalmente
            $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
            $existingMetrics = cache()->get($cacheKey, []);
            $existingMetrics[] = $metrics;
            
            // Mantener solo las últimas 1000 métricas por hora
            if (count($existingMetrics) > 1000) {
                $existingMetrics = array_slice($existingMetrics, -1000);
            }
            
            cache()->put($cacheKey, $existingMetrics, now()->addHours(24));
            
        } catch (\Exception $e) {
            Log::error('ServicePerformanceMonitor - Error almacenando métricas', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
