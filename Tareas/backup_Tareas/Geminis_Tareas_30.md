
**Tarea 30: Panel de Cliente (Visualización de Servicios y Facturas)**
Permitir a los clientes ver sus servicios contratados y su historial de facturas.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_30.md
@@ -0,0 +1,58 @@
+# Geminis - Plan de Tareas Detallado - Parte 30
+
+Este documento se enfoca en permitir a los clientes visualizar sus servicios y facturas.
+
+## Fase 10: Panel de Cliente - Continuación
+
+### 10.4. Visualización de Servicios del Cliente
+*   **Contexto:** Los clientes deben poder ver una lista de sus servicios contratados y los detalles de cada uno.
+*   `[ ]` Crear `Client/ServiceController.php` (o añadir a `ClientDashboardController`):
+    ```bash
+    php artisan make:controller Client/ServiceController --resource --model=ClientService
+    ```
+*   `[ ]` Definir rutas resource para `services` dentro del grupo `client` en `routes/web.php`.
+    ```php
+    // Dentro del grupo Route::prefix('client')...
+    Route::resource('services', App\Http\Controllers\Client\ServiceController::class)->only(['index', 'show']);
+    // Solo index y show por ahora para el cliente
+    ```
+*   `[ ]` Implementar método `index()` en `Client\ServiceController`:
+    *   Listar `ClientService` donde `client_id = Auth::id()`.
+    *   Cargar relaciones (`product`, `productPricing`).
+    *   Paginado.
+    *   Pasar datos a la vista `Client/Services/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Client/Services/Index.vue`:
+    *   Usar `ClientLayout.vue`.
+    *   Tabla/lista de servicios (Producto, Dominio, Próxima Vencimiento, Estado, Precio).
+    *   Enlace para ver detalles.
+*   `[ ]` Implementar método `show(ClientService $service)`:
+    *   Verificar que `$service->client_id === Auth::id()`.
+    *   Mostrar detalles del servicio, opciones configurables, etc.
+    *   Vista `Client/Services/Show.vue`.
+*   `[ ]` Añadir enlace "Mis Servicios" en `ClientLayout.vue`.
+*   `[ ]` **Verificación:** El cliente puede ver sus servicios y detalles.
+
+### 10.5. Visualización de Facturas del Cliente
+*   **Contexto:** Los clientes deben poder ver su historial de facturas y los detalles de cada una.
+*   `[ ]` Crear `Client/InvoiceController.php`:
+    ```bash
+    php artisan make:controller Client/InvoiceController --resource --model=Invoice
+    ```
+*   `[ ]` Definir rutas resource para `invoices` dentro del grupo `client` en `routes/web.php`.
+    ```php
+    // Dentro del grupo Route::prefix('client')...
+    Route::resource('invoices', App\Http\Controllers\Client\InvoiceController::class)->only(['index', 'show']);
+    ```
+*   `[ ]` Implementar método `index()` en `Client\InvoiceController`:
+    *   Listar `Invoice` donde `client_id = Auth::id()`. Paginado.
+    *   Pasar datos a la vista `Client/Invoices/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Client/Invoices/Index.vue`:
+    *   Usar `ClientLayout.vue`. Tabla/lista de facturas (Número, Fecha Emisión, Fecha Vencimiento, Total, Estado). Enlace para ver/pagar.
+*   `[ ]` Implementar método `show(Invoice $invoice)`:
+    *   Verificar que `$invoice->client_id === Auth::id()`.
+    *   Mostrar detalles de la factura, ítems, opción de descarga PDF (implementada en Tarea 16).
+    *   Vista `Client/Invoices/Show.vue`.
+*   `[ ]` Añadir enlace "Mis Facturas" en `ClientLayout.vue`.
+*   `[ ]` **Verificación:** El cliente puede ver sus facturas y detalles.
+
+---
+**¡Visualización de Servicios y Facturas por Cliente Implementada!**
+El panel de cliente ahora ofrece información útil. Las siguientes tareas pueden incluir la gestión de dominios por el cliente y la interacción con el sistema de soporte.
+```
