<?php

namespace App\Console\Commands;

use App\Services\PerformanceOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AnalyzeServicePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:analyze-performance 
                            {--service= : Analizar servicio específico}
                            {--hours=24 : Horas de datos a analizar}
                            {--export : Exportar resultados a archivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analizar performance de servicios refactorizados';

    public function __construct(
        private PerformanceOptimizationService $performanceService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Analizando performance de servicios...');
        
        $hours = (int) $this->option('hours');
        $specificService = $this->option('service');
        $export = $this->option('export');
        
        // Obtener métricas generales
        $this->info("\n📊 Métricas Generales:");
        $metrics = $this->performanceService->getServiceMetrics();
        $this->displayGeneralMetrics($metrics);
        
        // Analizar métricas de requests
        $this->info("\n🌐 Análisis de Requests:");
        $requestMetrics = $this->analyzeRequestMetrics($hours);
        $this->displayRequestMetrics($requestMetrics);
        
        // Obtener recomendaciones
        $this->info("\n💡 Recomendaciones de Optimización:");
        $optimization = $this->performanceService->optimizeServiceConfiguration();
        $this->displayRecommendations($optimization['recommendations']);
        
        // Analizar servicio específico si se especifica
        if ($specificService) {
            $this->info("\n🎯 Análisis Específico: {$specificService}");
            $this->analyzeSpecificService($specificService);
        }
        
        // Exportar resultados si se solicita
        if ($export) {
            $this->exportResults([
                'general_metrics' => $metrics,
                'request_metrics' => $requestMetrics,
                'recommendations' => $optimization['recommendations']
            ]);
        }
        
        $this->info("\n✅ Análisis completado!");
        return Command::SUCCESS;
    }

    private function displayGeneralMetrics(array $metrics): void
    {
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Cache Hit Rate', number_format($metrics['cache_hit_rate'] * 100, 1) . '%'],
                ['Tiempo Promedio Query', $metrics['average_query_time'] . ' ms'],
                ['Queries Lentas', $metrics['slow_queries_count']],
                ['Memoria Actual', $this->formatBytes($metrics['memory_usage']['current'])],
                ['Memoria Pico', $this->formatBytes($metrics['memory_usage']['peak'])],
            ]
        );
        
        if (!empty($metrics['service_response_times'])) {
            $this->info("\n⏱️  Tiempos de Respuesta por Servicio:");
            $serviceData = [];
            foreach ($metrics['service_response_times'] as $service => $time) {
                $serviceData[] = [class_basename($service), $time . ' ms'];
            }
            $this->table(['Servicio', 'Tiempo Promedio'], $serviceData);
        }
    }

    private function analyzeRequestMetrics(int $hours): array
    {
        $metrics = [];
        $totalRequests = 0;
        $slowRequests = 0;
        $totalExecutionTime = 0;
        
        // Analizar métricas de las últimas horas
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $cacheKey = 'performance_metrics_' . $hour;
            $hourlyMetrics = Cache::get($cacheKey, []);
            
            foreach ($hourlyMetrics as $metric) {
                $totalRequests++;
                $totalExecutionTime += $metric['execution_time_ms'];
                
                if ($metric['execution_time_ms'] > 1000) {
                    $slowRequests++;
                }
            }
        }
        
        return [
            'total_requests' => $totalRequests,
            'slow_requests' => $slowRequests,
            'average_response_time' => $totalRequests > 0 ? $totalExecutionTime / $totalRequests : 0,
            'slow_request_percentage' => $totalRequests > 0 ? ($slowRequests / $totalRequests) * 100 : 0
        ];
    }

    private function displayRequestMetrics(array $metrics): void
    {
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Requests', number_format($metrics['total_requests'])],
                ['Requests Lentos (>1s)', number_format($metrics['slow_requests'])],
                ['% Requests Lentos', number_format($metrics['slow_request_percentage'], 2) . '%'],
                ['Tiempo Promedio', number_format($metrics['average_response_time'], 2) . ' ms'],
            ]
        );
    }

    private function displayRecommendations(array $recommendations): void
    {
        if (empty($recommendations)) {
            $this->info('✅ No se encontraron problemas de performance');
            return;
        }
        
        foreach ($recommendations as $recommendation) {
            $icon = match($recommendation['type']) {
                'memory' => '🧠',
                'queries' => '🗃️',
                'cache' => '⚡',
                default => '💡'
            };
            
            $this->warn("{$icon} {$recommendation['message']}");
            $this->line("   Sugerencia: {$recommendation['suggestion']}");
            $this->line('');
        }
    }

    private function analyzeSpecificService(string $serviceName): void
    {
        try {
            // Intentar instanciar el servicio
            $serviceClass = "App\\Services\\{$serviceName}";
            
            if (!class_exists($serviceClass)) {
                $this->error("❌ Servicio {$serviceName} no encontrado");
                return;
            }
            
            $this->info("Analizando {$serviceName}...");
            
            // Aquí podrías agregar análisis específicos del servicio
            // Por ejemplo, analizar métodos públicos, dependencias, etc.
            
            $reflection = new \ReflectionClass($serviceClass);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            $this->info("📋 Métodos públicos encontrados: " . count($methods));
            
            foreach ($methods as $method) {
                if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $serviceClass) {
                    $this->line("  • {$method->getName()}()");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error analizando servicio: " . $e->getMessage());
        }
    }

    private function exportResults(array $results): void
    {
        $filename = 'performance_analysis_' . now()->format('Y-m-d_H-i-s') . '.json';
        $path = storage_path('app/performance/' . $filename);
        
        // Crear directorio si no existe
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, json_encode($results, JSON_PRETTY_PRINT));
        
        $this->info("📄 Resultados exportados a: {$path}");
    }

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
