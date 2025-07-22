# APIs de Controladores Refactorizados

## Resumen

Esta documentación describe las APIs de los controladores refactorizados que ahora siguen el Principio de Responsabilidad Única.

## Controladores Administrativos

### AdminInvoiceControllerRefactored

**Base URL**: `/admin/invoices`

#### Endpoints Principales

##### GET /admin/invoices
**Descripción**: Listar facturas con filtros y paginación

**Parámetros de consulta**:
- `page` (int): Número de página
- `per_page` (int): Items por página (máx: 100)
- `status` (string): Filtrar por estado
- `client_id` (int): Filtrar por cliente
- `date_from` (date): Fecha desde
- `date_to` (date): Fecha hasta

**Respuesta exitosa (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "20250122-0001",
      "client": {
        "id": 1,
        "name": "Cliente Ejemplo"
      },
      "total_amount": 100.00,
      "status": "unpaid",
      "issue_date": "2025-01-22",
      "due_date": "2025-02-21"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 10
  }
}
```

##### POST /admin/invoices
**Descripción**: Crear nueva factura

**Cuerpo de la solicitud**:
```json
{
  "client_id": 1,
  "issue_date": "2025-01-22",
  "due_date": "2025-02-21",
  "currency_code": "USD",
  "items": [
    {
      "description": "Servicio Web Hosting",
      "quantity": 1,
      "amount": 100.00,
      "product_id": 1
    }
  ],
  "tax1_rate": 10.0,
  "notes": "Notas adicionales"
}
```

**Respuesta exitosa (201)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "20250122-0001",
    "total_amount": 110.00,
    "status": "unpaid"
  },
  "message": "Factura creada exitosamente"
}
```

##### PUT /admin/invoices/{id}
**Descripción**: Actualizar factura existente

**Parámetros de ruta**:
- `id` (int): ID de la factura

**Cuerpo de la solicitud**: Similar a POST

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "20250122-0001",
    "updated_at": "2025-01-22T10:30:00Z"
  },
  "message": "Factura actualizada exitosamente"
}
```

#### Endpoints AJAX

##### GET /admin/invoices/stats
**Descripción**: Obtener estadísticas de facturas

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "total_invoices": 150,
    "unpaid_invoices": 25,
    "total_unpaid_amount": 2500.00,
    "overdue_invoices": 5,
    "monthly_revenue": 15000.00
  }
}
```

##### POST /admin/invoices/bulk-action
**Descripción**: Ejecutar acción en lote

**Cuerpo de la solicitud**:
```json
{
  "action": "mark_as_paid",
  "invoice_ids": [1, 2, 3],
  "additional_data": {
    "payment_date": "2025-01-22"
  }
}
```

### AdminPaymentMethodControllerRefactored

**Base URL**: `/admin/payment-methods`

#### Endpoints Principales

##### GET /admin/payment-methods
**Descripción**: Listar métodos de pago

**Respuesta exitosa (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "PayPal",
      "slug": "paypal",
      "type": "automatic",
      "is_active": true,
      "display_order": 1
    }
  ]
}
```

##### POST /admin/payment-methods/{id}/validate
**Descripción**: Validar configuración de método de pago

**Cuerpo de la solicitud**:
```json
{
  "account_number": "1234567890",
  "swift_code": "ABCDUS33",
  "test_mode": true
}
```

## Controladores del Cliente

### ClientServiceControllerRefactored

**Base URL**: `/client/services`

#### Endpoints Principales

##### GET /client/services
**Descripción**: Listar servicios del cliente

**Parámetros de consulta**:
- `status` (string): Filtrar por estado
- `product_type` (string): Filtrar por tipo de producto

**Respuesta exitosa (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "product": {
        "name": "Web Hosting Premium"
      },
      "status": "active",
      "next_due_date": "2025-02-22",
      "amount": 29.99
    }
  ]
}
```

##### POST /client/services/{id}/request-cancellation
**Descripción**: Solicitar cancelación de servicio

**Cuerpo de la solicitud**:
```json
{
  "reason": "No longer needed",
  "cancellation_type": "immediate"
}
```

### ClientCheckoutControllerRefactored

**Base URL**: `/client/checkout`

#### Endpoints Principales

##### GET /client/checkout
**Descripción**: Mostrar página de checkout

**Respuesta**: Vista Inertia con datos del carrito

##### POST /client/checkout/process
**Descripción**: Procesar checkout

**Cuerpo de la solicitud**:
```json
{
  "payment_method": "paypal",
  "billing_info": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "discount_code": "SAVE10"
}
```

##### POST /client/checkout/validate
**Descripción**: Validar datos de checkout

**Respuesta exitosa (200)**:
```json
{
  "valid": true,
  "errors": {},
  "totals": {
    "subtotal": 100.00,
    "discount": -10.00,
    "tax": 9.00,
    "total": 99.00
  }
}
```

### ClientFundAdditionControllerRefactored

**Base URL**: `/client/funds`

#### Endpoints Principales

##### GET /client/add-funds
**Descripción**: Mostrar formulario de adición de fondos

##### POST /client/add-funds
**Descripción**: Procesar adición manual de fondos

**Cuerpo de la solicitud**:
```json
{
  "amount": 100.00,
  "payment_method_id": 1,
  "reference_number": "REF123456",
  "payment_date": "2025-01-22"
}
```

##### POST /client/funds/paypal/initiate
**Descripción**: Iniciar pago con PayPal

**Cuerpo de la solicitud**:
```json
{
  "amount": 50.00
}
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "approval_link": "https://paypal.com/approve/123"
  }
}
```

#### Endpoints AJAX

##### GET /client/funds/stats
**Descripción**: Obtener estadísticas de fondos

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "total_added": 500.00,
    "pending_additions": 2,
    "current_balance": 150.00
  }
}
```

##### GET /client/funds/minimum-amounts
**Descripción**: Obtener montos mínimos por método

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "paypal": 30.00,
    "bank_transfer": 10.00,
    "default": 5.00
  }
}
```

### ClientInvoiceControllerRefactored

**Base URL**: `/client/invoices`

#### Endpoints Principales

##### GET /client/invoices
**Descripción**: Listar facturas del cliente

##### GET /client/invoices/{id}
**Descripción**: Ver detalles de factura

##### POST /client/invoices/{id}/pay-with-balance
**Descripción**: Pagar factura con balance

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "message": "Factura pagada exitosamente",
  "data": {
    "new_balance": 50.00,
    "transaction_id": 123
  }
}
```

#### Endpoints AJAX

##### GET /client/invoices/stats
**Descripción**: Obtener estadísticas de facturas

##### GET /client/invoices/{id}/check-balance
**Descripción**: Verificar si puede pagar con balance

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "can_pay": true,
    "client_balance": 150.00,
    "invoice_amount": 100.00
  }
}
```

## Códigos de Estado HTTP

- **200 OK**: Operación exitosa
- **201 Created**: Recurso creado exitosamente
- **400 Bad Request**: Datos de entrada inválidos
- **401 Unauthorized**: No autenticado
- **403 Forbidden**: No autorizado para esta acción
- **404 Not Found**: Recurso no encontrado
- **422 Unprocessable Entity**: Errores de validación
- **500 Internal Server Error**: Error del servidor

## Formato de Errores

```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {
    "field_name": ["Error específico del campo"]
  }
}
```

## Autenticación

Todos los endpoints requieren autenticación mediante:
- **Sesión web** para interfaces de usuario
- **API Token** para integraciones (futuro)

## Rate Limiting

- **Web**: 60 requests por minuto por IP
- **API**: 100 requests por minuto por usuario (futuro)

## Migración desde Controladores Antiguos

### Cambios en Rutas

Los controladores refactorizados mantienen las mismas rutas pero con nuevos nombres de clase:

```php
// Antes
Route::resource('invoices', AdminInvoiceController::class);

// Después
Route::resource('invoices', AdminInvoiceControllerRefactored::class);
```

### Cambios en Respuestas

Las respuestas ahora siguen un formato consistente:

```php
// Antes (inconsistente)
return response()->json($data);
return redirect()->back()->with('success', 'Mensaje');

// Después (consistente)
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Mensaje'
]);
```

### Nuevos Endpoints AJAX

Los controladores refactorizados incluyen nuevos endpoints AJAX para mejor UX:

- `/admin/invoices/stats` - Estadísticas en tiempo real
- `/client/funds/minimum-amounts` - Montos mínimos dinámicos
- `/client/invoices/search` - Búsqueda de facturas

## Versionado de API

Actualmente en versión `v1`. Futuras versiones mantendrán compatibilidad hacia atrás.
