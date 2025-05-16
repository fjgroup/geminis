
**Tarea 35: Mejoras en Facturación (Renovaciones Automáticas)**
Implementación del Job para generar facturas de renovación automáticamente.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_35.md
@@ -0,0 +1,54 @@
+# Geminis - Plan de Tareas Detallado - Parte 35
+
+Este documento se enfoca en implementar la generación automática de facturas de renovación.
+
+## Fase 6: Proceso de Compra y Facturación - Mejoras (Renovaciones)
+
+### 6.24. Job para Generar Facturas de Renovación (`GenerateRenewalInvoicesJob`)
+*   **Contexto:** Automatizar la creación de facturas para servicios que están por vencer.
+*   `[ ]` Crear el Job:
+    ```bash
+    php artisan make:job GenerateRenewalInvoicesJob
+    ```
+*   `[ ]` En `app/Jobs/GenerateRenewalInvoicesJob.php`:
+    *   En el método `handle()`:
+        *   Obtener la fecha actual y la fecha X días en el futuro (ej. 15 días, configurable en `settings`).
+        *   Buscar `ClientService` que cumplan:
+            *   `status` = 'active'.
+            *   `next_due_date` <= fecha X días en el futuro.
+            *   Que no tengan ya una factura de renovación 'unpaid' para ese `next_due_date`. (Esto requiere una forma de marcar las facturas como "de renovación" o vincularlas al `client_service_id` y `next_due_date`).
+        *   Para cada servicio encontrado:
+            *   Llamar a un servicio `InvoiceService->generateRenewalInvoice(ClientService $service)`.
+*   `[ ]` **Verificación:** El Job se puede ejecutar manualmente y la lógica de búsqueda de servicios es correcta.
+
+### 6.25. Servicio de Facturación (`InvoiceService`) - Método de Renovación
+*   **Contexto:** Lógica para crear una factura específica para la renovación de un servicio.
+*   `[ ]` Crear (o añadir a) `app/Services/InvoiceService.php`.
+*   `[ ]` Método `generateRenewalInvoice(ClientService $service): ?Invoice`:
+    *   Determinar el `issue_date` (hoy) y `due_date` (el `service->next_due_date`).
+    *   Crear un registro en `invoices`:
+        *   `client_id`, `reseller_id` del servicio.
+        *   `invoice_number` único.
+        *   `status` = 'unpaid'.
+        *   `currency_code` del servicio/cliente.
+    *   Crear `invoice_items`:
+        *   Un ítem principal para el producto base del servicio (`service->product_id`, `service->product_pricing_id`). Descripción: "Renovación de [Nombre Producto] ([Fecha Inicio Ciclo] - [Fecha Fin Ciclo])".
+        *   Ítems adicionales para las `client_service_configurable_options` activas del servicio, con sus precios correspondientes.
+    *   Calcular subtotal, impuestos (si aplica), y total.
+    *   Guardar la factura y sus ítems.
+    *   Actualizar `client_services.next_due_date` al siguiente ciclo de facturación (ej. si era mensual, sumar 1 mes).
+    *   (Opcional) Enviar email de "Nueva Factura de Renovación" al cliente usando `EmailService`.
+    *   Retornar la factura creada.
+*   `[ ]` **Verificación:** El método genera correctamente una factura de renovación con sus ítems y actualiza la fecha del servicio.
+
### 6.26. Programación del Job (`Kernel.php`)
### 6.26. Programación del Job (`bootstrap/app.php`)
*   **Contexto:** Ejecutar el `GenerateRenewalInvoicesJob` automáticamente.
*   `[ ]` En `app/Console/Kernel.php`, dentro del método `schedule()`:
*   `[ ]` En `bootstrap/app.php`, dentro del closure de `withSchedule()` o `->withSchedule(function (Schedule $schedule) { ... })`:
    ```php
    // $schedule->job(new GenerateRenewalInvoicesJob)->daily(); // O dailyAt('01:00');
    $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping(); // Si los jobs se despachan a la cola
    $schedule->job(GenerateRenewalInvoicesJob::class)->dailyAt('03:00'); // Despacha el job a la cola diariamente
    // Ejemplo en bootstrap/app.php
    // return Application::configure(...)
    //     ->withRouting(...)
    //     ->withMiddleware(...)
    //     ->withExceptions(...)
    //     ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
    //         $schedule->job(new App\Jobs\GenerateRenewalInvoicesJob)->dailyAt('03:00'); // Despacha el job a la cola diariamente
    //         // Si los jobs se despachan a la cola, asegúrate que el worker de la cola esté corriendo.
    //         // Opcionalmente, para desarrollo o si no usas colas para este job:
    //         // $schedule->call(function () {
    //         //     (new App\Jobs\GenerateRenewalInvoicesJob)->handle();
    //         // })->dailyAt('03:00');
    //     })->create();
+    ```
+*   `[ ]` Asegurar que el programador de Laravel (scheduler) esté configurado en el servidor (cron job que ejecute `php artisan schedule:run`).
+*   `[ ]` **Verificación:** El job se ejecuta según lo programado y procesa las renovaciones. (Se puede probar localmente con `php artisan schedule:run`).
+
+---
+**¡Generación Automática de Facturas de Renovación Implementada!**
+Esto reduce la carga administrativa y asegura la facturación continua de servicios.
+```
