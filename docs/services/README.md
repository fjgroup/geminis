# Servicios Refactorizados - Documentación

## Resumen

Este documento describe los servicios creados durante la refactorización para aplicar el Principio de Responsabilidad Única (SRP) y mejorar la arquitectura del sistema.

## Arquitectura

### Antes de la Refactorización
- **Controladores monolíticos** con 200-700 líneas de código
- **Lógica de negocio mezclada** con manejo HTTP
- **Dificultad para testing** y mantenimiento
- **Código duplicado** entre controladores

### Después de la Refactorización
- **Controladores ligeros** enfocados solo en HTTP
- **Servicios especializados** para lógica de negocio
- **Separación clara** de responsabilidades
- **Código reutilizable** y testeable

## Servicios Principales

### 1. InvoiceManagementService
**Propósito**: Gestión administrativa completa de facturas

**Responsabilidades**:
- Creación y actualización de facturas
- Cálculos de impuestos y totales
- Gestión de estados de factura
- Generación de reportes

**Métodos principales**:
```php
// Crear nueva factura
createInvoice(array $data): array

// Actualizar factura existente
updateInvoice(Invoice $invoice, array $data): array

// Calcular totales de factura
calculateInvoiceTotals(array $items, float $tax1Rate = 0, float $tax2Rate = 0): array

// Obtener estadísticas de facturas
getInvoiceStatistics(array $filters = []): array
```

### 2. ClientServiceService
**Propósito**: Gestión de servicios del cliente

**Responsabilidades**:
- Creación y configuración de servicios
- Gestión de estados del servicio
- Cálculos de precios y renovaciones
- Integración con productos

**Métodos principales**:
```php
// Crear nuevo servicio
createClientService(User $client, array $serviceData): array

// Actualizar configuración de servicio
updateServiceConfiguration(ClientService $service, array $config): array

// Calcular precio de servicio
calculateServicePrice(Product $product, ProductPricing $pricing, array $options = []): array

// Obtener servicios del cliente
getClientServices(User $client, array $filters = []): array
```

### 3. ClientCheckoutService
**Propósito**: Procesamiento de checkout y carrito

**Responsabilidades**:
- Validación de carrito
- Cálculos de checkout
- Creación de facturas desde carrito
- Gestión de descuentos

**Métodos principales**:
```php
// Procesar checkout
processCheckout(User $client, array $checkoutData): array

// Validar carrito
validateCart(array $cartItems): array

// Aplicar descuentos
applyDiscounts(array $cartItems, array $discountCodes): array

// Crear factura desde carrito
createInvoiceFromCart(User $client, array $cartItems): array
```

### 4. FundAdditionService
**Propósito**: Gestión de adición de fondos

**Responsabilidades**:
- Procesamiento de fondos manuales
- Integración con PayPal
- Validaciones de montos
- Historial de transacciones

**Métodos principales**:
```php
// Procesar adición manual
processManualFundAddition(User $client, array $data): array

// Iniciar pago PayPal
initiatePayPalPayment(User $client, float $amount): array

// Manejar éxito de PayPal
handlePayPalSuccess(User $client, array $sessionData): array

// Obtener historial
getFundAdditionHistory(User $client, int $perPage = 10): Collection
```

### 5. PaymentMethodService
**Propósito**: Gestión de métodos de pago

**Responsabilidades**:
- Validaciones dinámicas de métodos
- Configuración de métodos de pago
- Verificación de disponibilidad
- Gestión de logos y configuraciones

**Métodos principales**:
```php
// Validar método de pago
validatePaymentMethod(PaymentMethod $method, array $data): array

// Obtener métodos activos
getActivePaymentMethods(): Collection

// Configurar método
configurePaymentMethod(PaymentMethod $method, array $config): array

// Verificar disponibilidad
checkMethodAvailability(PaymentMethod $method, User $client): bool
```

## Servicios de Soporte

### InvoiceNumberService
**Propósito**: Generación de números de factura

**Características**:
- Números secuenciales por día
- Formato personalizable
- Validación de formato
- Estadísticas de numeración

### InvoiceValidationService
**Propósito**: Validaciones de negocio para facturas

**Características**:
- Validación de cancelación
- Verificación de integridad
- Validación de pagos
- Reglas de negocio centralizadas

### ClientServiceBusinessService
**Propósito**: Lógica de negocio para servicios del cliente

**Características**:
- Extensión de renovaciones
- Cálculo de fechas de vencimiento
- Estadísticas de servicios
- Validaciones de renovación

### ClientInvoiceService
**Propósito**: Gestión de facturas del lado del cliente

**Características**:
- Pago con balance
- Cancelación de reportes
- Historial de facturas
- Estadísticas del cliente

## Registro de Servicios

Todos los servicios están registrados en `ServicesServiceProvider` como singletons:

```php
// Servicios principales
$this->app->singleton(InvoiceManagementService::class);
$this->app->singleton(ClientServiceService::class);
$this->app->singleton(ClientCheckoutService::class);
$this->app->singleton(FundAdditionService::class);
$this->app->singleton(PaymentMethodService::class);

// Servicios de soporte
$this->app->singleton(InvoiceNumberService::class);
$this->app->singleton(InvoiceValidationService::class);
$this->app->singleton(ClientServiceBusinessService::class);
$this->app->singleton(ClientInvoiceService::class);
```

## Inyección de Dependencias

Los servicios se inyectan automáticamente en los controladores:

```php
class AdminInvoiceControllerRefactored extends Controller
{
    public function __construct(
        private InvoiceManagementService $invoiceService,
        private InvoiceValidationService $validationService
    ) {}
}
```

## Manejo de Errores

Todos los servicios siguen un patrón consistente de respuesta:

```php
// Respuesta exitosa
return [
    'success' => true,
    'data' => $result,
    'message' => 'Operación completada exitosamente'
];

// Respuesta con error
return [
    'success' => false,
    'data' => null,
    'message' => 'Descripción del error'
];
```

## Logging

Todos los servicios implementan logging consistente:

```php
Log::info('Servicio - Operación exitosa', [
    'service' => static::class,
    'operation' => 'methodName',
    'data' => $relevantData
]);

Log::error('Servicio - Error en operación', [
    'service' => static::class,
    'error' => $e->getMessage(),
    'data' => $inputData
]);
```

## Testing

Cada servicio tiene su suite de tests unitarios:

- `tests/Unit/Services/InvoiceManagementServiceTest.php`
- `tests/Unit/Services/ClientServiceServiceTest.php`
- `tests/Unit/Services/FundAdditionServiceTest.php`
- `tests/Unit/Services/PaymentMethodServiceTest.php`

## Próximos Pasos

1. **Monitoreo**: Implementar métricas de performance
2. **Cache**: Agregar cache para operaciones frecuentes
3. **Eventos**: Implementar eventos para operaciones críticas
4. **API**: Exponer servicios como APIs REST
5. **Documentación**: Generar documentación automática con Swagger
