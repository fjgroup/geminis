# Guía de Optimización de Performance

## Resumen

Esta guía documenta las optimizaciones de performance implementadas en los servicios refactorizados y proporciona mejores prácticas para mantener un rendimiento óptimo.

## Herramientas de Optimización

### PerformanceOptimizationService

Servicio centralizado que proporciona:

- **Cache inteligente** con TTL dinámico
- **Análisis de queries** y detección de N+1
- **Métricas de performance** en tiempo real
- **Invalidación de cache** por patrones
- **Recomendaciones automáticas** de optimización

#### Uso Básico

```php
// Cache automático con TTL inteligente
$result = $performanceService->cacheOperation(
    'expensive_operation_key',
    function () {
        return $this->expensiveOperation();
    },
    1800 // TTL en segundos (opcional)
);

// Análisis de queries en servicios
$analysis = $performanceService->analyzeServiceQueries(
    function () {
        return $this->serviceMethod();
    },
    'ServiceName'
);
```

### ServicePerformanceMonitor (Middleware)

Middleware que monitorea automáticamente:

- **Tiempo de ejecución** de requests
- **Uso de memoria** por request
- **Queries ejecutadas** por endpoint
- **Headers de debug** en desarrollo

#### Configuración

```php
// En app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\ServicePerformanceMonitor::class,
];
```

### Comando de Análisis

```bash
# Analizar performance general
php artisan services:analyze-performance

# Analizar servicio específico
php artisan services:analyze-performance --service=InvoiceManagementService

# Analizar últimas 48 horas
php artisan services:analyze-performance --hours=48

# Exportar resultados
php artisan services:analyze-performance --export
```

## Optimizaciones Implementadas

### 1. Cache Inteligente

#### Estrategia de Cache por Tipo de Operación

```php
// Operaciones de lectura: 30 minutos
$this->cacheOperation('read_operation', $callback, 1800);

// Operaciones de escritura: 5 minutos
$this->cacheOperation('write_operation', $callback, 300);

// Estadísticas: 15 minutos
$this->cacheOperation('stats_operation', $callback, 900);
```

#### Cache Keys Inteligentes

```php
// Cache por usuario y filtros
$cacheKey = "client_services_{$clientId}_" . md5(serialize($filters));

// Cache por entidad
$cacheKey = "invoice_{$invoiceId}_details";

// Cache global con timestamp
$cacheKey = "global_stats_" . now()->format('Y-m-d-H');
```

### 2. Optimización de Queries

#### Eager Loading Selectivo

```php
// ❌ Antes: Cargar todo
$query->with(['client', 'items', 'transactions']);

// ✅ Después: Solo campos necesarios
$query->with([
    'client:id,name,email',
    'items:id,invoice_id,description,amount',
    'transactions' => function ($q) {
        $q->select('id', 'invoice_id', 'status', 'amount')
          ->where('type', 'payment')
          ->latest()
          ->limit(1);
    }
]);
```

#### Selección de Campos Específicos

```php
// ❌ Antes: SELECT *
$services = $query->get();

// ✅ Después: Solo campos necesarios
$services = $query
    ->select(['id', 'client_id', 'product_id', 'status', 'next_due_date'])
    ->get();
```

#### Optimización de Filtros

```php
// ❌ Antes: Múltiples WHERE
if (!empty($filters['date_from'])) {
    $query->where('issue_date', '>=', $filters['date_from']);
}
if (!empty($filters['date_to'])) {
    $query->where('issue_date', '<=', $filters['date_to']);
}

// ✅ Después: BETWEEN optimizado
if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
    $query->whereBetween('issue_date', [
        $filters['date_from'] ?? '1900-01-01',
        $filters['date_to'] ?? '2099-12-31'
    ]);
}
```

### 3. Invalidación de Cache

#### Por Entidad

```php
// Invalidar cache relacionado con una factura
$performanceService->invalidateEntityCache('invoice', $invoiceId);

// Invalidar cache de servicios de un cliente
$performanceService->invalidateEntityCache('client_services', $clientId);
```

#### Por Patrón

```php
// Invalidar todas las estadísticas
$performanceService->invalidateCache('stats_*');

// Invalidar cache de un cliente específico
$performanceService->invalidateCache("client_{$clientId}_*");
```

## Métricas y Monitoreo

### Métricas Clave

1. **Cache Hit Rate**: > 80%
2. **Tiempo Promedio de Query**: < 50ms
3. **Tiempo de Respuesta**: < 200ms
4. **Uso de Memoria**: < 128MB por request
5. **Queries por Request**: < 10

### Dashboard de Métricas

```php
$metrics = $performanceService->getServiceMetrics();

// Resultado:
[
    'cache_hit_rate' => 0.85,
    'average_query_time' => 45.2,
    'slow_queries_count' => 3,
    'memory_usage' => [
        'current' => 67108864,  // 64MB
        'peak' => 134217728     // 128MB
    ],
    'service_response_times' => [
        'InvoiceManagementService' => 120.5,
        'ClientServiceService' => 89.3
    ]
]
```

### Alertas Automáticas

El sistema genera alertas cuando:

- **Cache hit rate < 70%**
- **Query time > 100ms**
- **Memory usage > 256MB**
- **Response time > 1000ms**

## Mejores Prácticas

### 1. Diseño de Cache

```php
// ✅ Usar TTL apropiado según frecuencia de cambio
$ttl = match($operationType) {
    'user_data' => 3600,      // 1 hora
    'statistics' => 900,       // 15 minutos
    'real_time' => 60,        // 1 minuto
    default => 1800           // 30 minutos
};

// ✅ Incluir versión en cache key para invalidación fácil
$cacheKey = "v2_user_{$userId}_services";

// ✅ Cache condicional basado en tamaño de datos
if ($resultCount > 100) {
    return $this->cacheOperation($key, $callback, $ttl);
}
return $callback();
```

### 2. Optimización de Queries

```php
// ✅ Usar índices compuestos
Schema::table('invoices', function (Blueprint $table) {
    $table->index(['client_id', 'status', 'issue_date']);
});

// ✅ Limitar resultados en relaciones
$query->with(['items' => function ($q) {
    $q->limit(10)->latest();
}]);

// ✅ Usar chunk para grandes datasets
Invoice::chunk(1000, function ($invoices) {
    foreach ($invoices as $invoice) {
        // Procesar
    }
});
```

### 3. Monitoreo Proactivo

```php
// ✅ Log operaciones costosas
if ($executionTime > 1000) {
    Log::warning('Slow operation detected', [
        'operation' => $operationName,
        'time_ms' => $executionTime,
        'memory_mb' => memory_get_usage(true) / 1024 / 1024
    ]);
}

// ✅ Métricas en tiempo real
Cache::increment('operation_count');
Cache::put('last_operation_time', $executionTime, 3600);
```

## Configuración de Producción

### Redis Cache

```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'prefix' => env('CACHE_PREFIX', 'laravel_cache'),
],

// config/database.php
'redis' => [
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
    ],
],
```

### Configuración de Memoria

```php
// config/app.php
'memory_limit' => '256M',

// En servicios críticos
ini_set('memory_limit', '512M');
```

### Queue para Operaciones Pesadas

```php
// Para operaciones que no requieren respuesta inmediata
dispatch(new ProcessHeavyOperationJob($data));

// Para operaciones con delay
ProcessReportsJob::dispatch($data)->delay(now()->addMinutes(5));
```

## Herramientas de Desarrollo

### Debug en Local

```bash
# Habilitar query logging
DB::enableQueryLog();

# Ver queries ejecutadas
dd(DB::getQueryLog());

# Profiling con Clockwork
composer require itsgoingd/clockwork --dev
```

### Análisis de Performance

```bash
# Instalar herramientas de profiling
composer require barryvdh/laravel-debugbar --dev

# Análisis de memoria
composer require spatie/laravel-ray --dev
```

## Próximos Pasos

1. **Implementar APM**: New Relic o Datadog
2. **Cache distribuido**: Redis Cluster
3. **CDN**: Para assets estáticos
4. **Database optimization**: Query optimization y índices
5. **Horizontal scaling**: Load balancers y múltiples instancias
