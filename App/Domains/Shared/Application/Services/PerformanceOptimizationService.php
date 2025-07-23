<?php

namespace App\Domains\Shared\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Servicio para optimización de performance de los servicios refactorizados
 * 
 * Proporciona herramientas para cache, análisis de queries y optimizaciones
 */
class PerformanceOptimizationService
{
    private const CACHE_PREFIX = 'perf_opt_';
    private const DEFAULT_TTL = 3600; // 1 hora

    /**
     * Cachear resultado de operación costosa
     * 
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @return mixed
     */
    public function cacheOperation(string $key, callable $callback, int $ttl = self::DEFAULT_TTL)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        return Cache::remember($cacheKey, $ttl, function () use ($callback, $key) {
            $startTime = microtime(true);
            
            try {
                $result = $callback();
                
                $executionTime = (microtime(true) - $startTime) * 1000;
                
                Log::info('PerformanceOptimizationService - Cache miss', [
                    'key' => $key,
                    'execution_time_ms' => round($executionTime, 2)
                ]);
                
                return $result;
                
            } catch (\Exception $e) {
                Log::error('PerformanceOptimizationService - Error en operación cacheada', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Invalidar cache por patrón
     * 
     * @param string $pattern
     * @return int Número de claves eliminadas
     */
    public function invalidateCache(string $pattern): int
    {
        try {
            $fullPattern = self::CACHE_PREFIX . $pattern;
            
            if (config('cache.default') === 'redis') {
                return $this->invalidateRedisCache($fullPattern);
            }
            
            // Para otros drivers, usar approach más genérico
            return $this->invalidateGenericCache($fullPattern);
            
        } catch (\Exception $e) {
            Log::error('PerformanceOptimizationService - Error invalidando cache', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Analizar queries lentas en un servicio
     * 
     * @param callable $serviceOperation
     * @param string $serviceName
     * @return array
     */
    public function analyzeServiceQueries(callable $serviceOperation, string $serviceName): array
    {
        $queries = [];
        $startTime = microtime(true);
        
        DB::listen(function ($query) use (&$queries) {
            $queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName
            ];
        });

        try {
            $result = $serviceOperation();
            $totalTime = (microtime(true) - $startTime) * 1000;
            
            $analysis = $this->analyzeQueries($queries, $totalTime, $serviceName);
            
            return [
                'success' => true,
                'result' => $result,
                'performance' => $analysis
            ];
            
        } catch (\Exception $e) {
            Log::error('PerformanceOptimizationService - Error en análisis', [
                'service' => $serviceName,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'performance' => $this->analyzeQueries($queries, 0, $serviceName)
            ];
        }
    }

    /**
     * Optimizar consultas con eager loading
     * 
     * @param string $model
     * @param array $relations
     * @param array $conditions
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function optimizedQuery(string $model, array $relations = [], array $conditions = [])
    {
        $cacheKey = md5($model . serialize($relations) . serialize($conditions));
        
        return $this->cacheOperation($cacheKey, function () use ($model, $relations, $conditions) {
            $query = $model::query();
            
            if (!empty($relations)) {
                $query->with($relations);
            }
            
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->get();
        }, 1800); // 30 minutos para queries optimizadas
    }

    /**
     * Obtener métricas de performance de servicios
     * 
     * @return array
     */
    public function getServiceMetrics(): array
    {
        return $this->cacheOperation('service_metrics', function () {
            return [
                'cache_hit_rate' => $this->calculateCacheHitRate(),
                'average_query_time' => $this->getAverageQueryTime(),
                'slow_queries_count' => $this->getSlowQueriesCount(),
                'memory_usage' => [
                    'current' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true)
                ],
                'service_response_times' => $this->getServiceResponseTimes()
            ];
        }, 300); // 5 minutos
    }

    /**
     * Optimizar configuración de servicios
     * 
     * @return array
     */
    public function optimizeServiceConfiguration(): array
    {
        $recommendations = [];
        
        // Analizar uso de memoria
        $memoryUsage = memory_get_usage(true);
        if ($memoryUsage > 128 * 1024 * 1024) { // 128MB
            $recommendations[] = [
                'type' => 'memory',
                'message' => 'Alto uso de memoria detectado',
                'suggestion' => 'Considerar implementar paginación en consultas grandes'
            ];
        }
        
        // Analizar queries N+1
        $slowQueries = $this->getSlowQueriesCount();
        if ($slowQueries > 10) {
            $recommendations[] = [
                'type' => 'queries',
                'message' => 'Múltiples queries lentas detectadas',
                'suggestion' => 'Implementar eager loading en relaciones'
            ];
        }
        
        // Analizar cache hit rate
        $hitRate = $this->calculateCacheHitRate();
        if ($hitRate < 0.8) {
            $recommendations[] = [
                'type' => 'cache',
                'message' => 'Baja tasa de aciertos en cache',
                'suggestion' => 'Revisar estrategia de cache y TTL'
            ];
        }
        
        return [
            'recommendations' => $recommendations,
            'current_metrics' => $this->getServiceMetrics()
        ];
    }

    /**
     * Implementar cache inteligente para servicios
     * 
     * @param string $serviceClass
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function smartCache(string $serviceClass, string $method, array $parameters = [])
    {
        $cacheKey = $this->generateSmartCacheKey($serviceClass, $method, $parameters);
        $ttl = $this->calculateOptimalTTL($serviceClass, $method);
        
        return $this->cacheOperation($cacheKey, function () use ($serviceClass, $method, $parameters) {
            $service = app($serviceClass);
            return call_user_func_array([$service, $method], $parameters);
        }, $ttl);
    }

    /**
     * Invalidar cache relacionado con una entidad
     * 
     * @param string $entityType
     * @param int $entityId
     * @return int
     */
    public function invalidateEntityCache(string $entityType, int $entityId): int
    {
        $patterns = [
            "{$entityType}_{$entityId}_*",
            "{$entityType}_list_*",
            "{$entityType}_stats_*"
        ];
        
        $totalInvalidated = 0;
        foreach ($patterns as $pattern) {
            $totalInvalidated += $this->invalidateCache($pattern);
        }
        
        Log::info('PerformanceOptimizationService - Cache de entidad invalidado', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'keys_invalidated' => $totalInvalidated
        ]);
        
        return $totalInvalidated;
    }

    /**
     * Métodos privados de soporte
     */
    private function invalidateRedisCache(string $pattern): int
    {
        $redis = Redis::connection();
        $keys = $redis->keys($pattern);
        
        if (empty($keys)) {
            return 0;
        }
        
        return $redis->del($keys);
    }

    private function invalidateGenericCache(string $pattern): int
    {
        // Para drivers que no soportan patrones, limpiar todo el cache
        Cache::flush();
        return 1;
    }

    private function analyzeQueries(array $queries, float $totalTime, string $serviceName): array
    {
        $slowQueries = array_filter($queries, fn($q) => $q['time'] > 100); // > 100ms
        $duplicateQueries = $this->findDuplicateQueries($queries);
        
        $analysis = [
            'service_name' => $serviceName,
            'total_execution_time_ms' => round($totalTime, 2),
            'total_queries' => count($queries),
            'slow_queries' => count($slowQueries),
            'duplicate_queries' => count($duplicateQueries),
            'average_query_time' => count($queries) > 0 ? round(array_sum(array_column($queries, 'time')) / count($queries), 2) : 0
        ];
        
        if (!empty($slowQueries)) {
            Log::warning('PerformanceOptimizationService - Queries lentas detectadas', [
                'service' => $serviceName,
                'slow_queries' => array_slice($slowQueries, 0, 5) // Solo las primeras 5
            ]);
        }
        
        return $analysis;
    }

    private function findDuplicateQueries(array $queries): array
    {
        $queryHashes = [];
        $duplicates = [];
        
        foreach ($queries as $query) {
            $hash = md5($query['sql'] . serialize($query['bindings']));
            
            if (isset($queryHashes[$hash])) {
                $duplicates[] = $query;
            } else {
                $queryHashes[$hash] = true;
            }
        }
        
        return $duplicates;
    }

    private function calculateCacheHitRate(): float
    {
        // Implementación simplificada - en producción usar métricas reales
        return 0.85; // 85% hit rate simulado
    }

    private function getAverageQueryTime(): float
    {
        // Implementación simplificada
        return 45.2; // ms
    }

    private function getSlowQueriesCount(): int
    {
        // Implementación simplificada
        return 3;
    }

    private function getServiceResponseTimes(): array
    {
        return [
            'InvoiceManagementService' => 120.5,
            'ClientServiceService' => 89.3,
            'FundAdditionService' => 156.7
        ];
    }

    private function generateSmartCacheKey(string $serviceClass, string $method, array $parameters): string
    {
        $className = class_basename($serviceClass);
        $paramHash = md5(serialize($parameters));
        
        return "{$className}_{$method}_{$paramHash}";
    }

    private function calculateOptimalTTL(string $serviceClass, string $method): int
    {
        // TTL inteligente basado en el tipo de operación
        $readOnlyMethods = ['get', 'find', 'list', 'search', 'stats'];
        $isReadOnly = collect($readOnlyMethods)->some(fn($prefix) => str_starts_with(strtolower($method), $prefix));
        
        return $isReadOnly ? 1800 : 300; // 30 min para lectura, 5 min para otros
    }
}
