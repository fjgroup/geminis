
**Tarea 16: Generación y Listado de Facturas**
Lógica para generar facturas desde órdenes y para renovaciones. Listados para admin y cliente.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_16.md
@@ -0,0 +1,65 @@
+# Geminis - Plan de Tareas Detallado - Parte 16
+
+Este documento se enfoca en la lógica de generación de facturas y su visualización.
+
+## Fase 6: Proceso de Compra y Facturación - Continuación
+
+### 6.12. Generación de Facturas a partir de Órdenes
+*   **Contexto:** Cuando una orden es creada (o marcada para facturar), se debe generar una factura correspondiente.
+*   `[ ]` Crear un servicio o un método en `OrderService` o `InvoiceService` para `generateInvoiceFromOrder(Order $order)`.
+    *   Este método creará un registro en `invoices`.
+    *   Copiará los `order_items` a `invoice_items`, ajustando descripciones si es necesario.
+    *   Calculará subtotal, impuestos (si aplica), y total para la factura.
+    *   Generará un `invoice_number` único.
+    *   Actualizará `orders.invoice_id` con el ID de la nueva factura.
+    *   Establecer `issue_date` y `due_date` (ej. `due_date` podría ser X días desde `issue_date`).
+*   `[ ]` Modificar `Client\OrderController@placeOrder` (o donde se complete la orden) para llamar a este servicio/método de generación de factura.
+*   `[ ]` **Verificación:** Al completar una orden, se genera una factura asociada con sus ítems.
+
+### 6.13. Generación de Facturas para Renovaciones (Conceptual - Job Futuro)
+*   **Contexto:** Los servicios (`client_services`) con `next_due_date` próxima necesitan que se les genere una factura de renovación.
+*   `[ ]` (Conceptual) Planificar un Job de Laravel (`GenerateRenewalInvoicesJob`) que se ejecute diariamente.
+    *   Buscará `client_services` activos cuya `next_due_date` esté dentro de un umbral (ej. en los próximos X días).
+    *   Para cada servicio, generará una nueva factura en estado 'unpaid'.
+    *   Los `invoice_items` se basarán en el `product_pricing_id` del servicio y las `client_service_configurable_options` activas.
+    *   Actualizará `client_services.next_due_date` para el siguiente ciclo después de generar la factura (o después de que se pague).
+*   **Nota:** La implementación completa de este Job puede ser una tarea posterior, pero es bueno tenerla en mente.
+
+### 6.14. Listado de Facturas (Admin)
+*   `[ ]` Crear `Admin\InvoiceController.php`:
+    ```bash
+    php artisan make:controller Admin/InvoiceController --resource --model=Invoice
+    ```
+*   `[ ]` Definir rutas resource para `invoices` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`: Listar facturas, paginadas, con filtros (cliente, estado, rango de fechas). Vista `Admin/Invoices/Index.vue`.
+*   `[ ]` Implementar `show()`: Mostrar detalles de una factura, incluyendo `invoice_items`. Vista `Admin/Invoices/Show.vue`.
+*   `[ ]` (Opcional) Implementar `edit()` y `update()` para modificar facturas (con restricciones, ej. solo si no está pagada).
+*   `[ ]` (Opcional) Crear `InvoicePolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Administradores pueden ver y gestionar facturas.
+
+### 6.15. Listado de Facturas (Cliente)
+*   `[ ]` En `Client\InvoiceController.php` (o similar), método `index()`:
+    *   Obtener las facturas del cliente autenticado.
+    *   Vista `Client/Invoices/Index.vue`.
+*   `[ ]` En `Client\InvoiceController.php`, método `show(Invoice $invoice)`:
+    *   Asegurar que la factura pertenezca al cliente.
+    *   Mostrar detalles. Vista `Client/Invoices/Show.vue`.
+*   `[ ]` Definir rutas en `routes/web.php` para el cliente.
+*   `[ ]` Añadir enlace en `ClientLayout.vue` o `AppLayout.vue`.
+*   `[ ]` **Verificación:** Clientes pueden ver su historial de facturas.
+
+### 6.16. (Opcional) Descarga de Factura en PDF
+*   **Contexto:** Los clientes (y administradores) a menudo necesitan una versión en PDF de la factura.
+*   `[ ]` Investigar un paquete de generación de PDF para Laravel (ej. `barryvdh/laravel-dompdf` o `spatie/laravel-pdf`).
+*   `[ ]` Crear un método en `InvoiceController` (tanto Admin como Client) `downloadPdf(Invoice $invoice)`.
+    *   Generar una vista Blade simple para el formato de la factura.
+    *   Usar el paquete de PDF para renderizar esa vista como PDF y ofrecerla para descarga.
+*   `[ ]` Añadir botón "Descargar PDF" en las vistas `Show.vue` de facturas.
+*   `[ ]` **Verificación:** Se puede descargar una factura en formato PDF.
+
+---
+**¡Generación y Listado de Facturas Implementado!**
+El siguiente paso es registrar los pagos para estas facturas.
+```
