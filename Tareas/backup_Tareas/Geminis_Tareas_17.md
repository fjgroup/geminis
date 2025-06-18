
**Tarea 17: Transacciones de Pago**
Migración, modelo y registro manual de transacciones.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_17.md
@@ -0,0 +1,63 @@
+# Geminis - Plan de Tareas Detallado - Parte 17
+
+Este documento se enfoca en la entidad `Transaction`, que registra todos los movimientos de dinero.
+
+## Fase 6: Proceso de Compra y Facturación - Continuación
+
+### 6.17. Migración de la Tabla `transactions`
+*   **Contexto:** Registra todos los pagos, reembolsos u otras transacciones financieras.
+*   `[ ]` Crear la migración para la tabla `transactions`:
+    ```bash
+    php artisan make:migration create_transactions_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('transactions', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
+        $table->foreignId('client_id')->constrained('users');
+        $table->foreignId('reseller_id')->nullable()->constrained('users');
+        $table->string('gateway_slug')->index()->comment('Ej: paypal, stripe, manual_credit');
+        $table->string('gateway_transaction_id')->nullable()->index()->comment('ID de la transacción en la pasarela');
+        $table->enum('type', ['payment', 'refund', 'chargeback', 'credit_added', 'credit_used'])->index();
+        $table->decimal('amount', 10, 2);
+        $table->string('currency_code', 3);
+        $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed')->index();
+        $table->decimal('fees_amount', 10, 2)->nullable()->comment('Comisiones de la pasarela');
+        $table->string('description')->nullable();
+        $table->timestamp('transaction_date');
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `transactions` existe.
+
+### 6.18. Modelo `Transaction`
+*   `[ ]` Crear el modelo `Transaction`: `php artisan make:model Transaction`
+*   `[ ]` Configurar `$fillable`, `$casts` (para `transaction_date`).
+*   `[ ]` Definir relaciones: `invoice()` (belongsTo Invoice), `client()` (belongsTo User), `reseller()` (belongsTo User).
+*   `[ ]` **Verificación:** Se pueden crear transacciones mediante Tinker.
+
+### 6.19. Registro Manual de Pagos (Admin)
+*   **Contexto:** Los administradores deben poder registrar pagos manualmente (ej. transferencia bancaria).
+*   `[ ]` En `Admin\InvoiceController@show` (o en `Admin/Invoices/Show.vue`), añadir un botón "Registrar Pago Manual".
+*   `[ ]` Este botón podría abrir un modal o llevar a un formulario simple para registrar una transacción.
+    *   Campos: Monto, Fecha de transacción, Gateway (manual), Descripción/Referencia.
+*   `[ ]` Crear un método en `Admin\InvoiceController` (ej. `addManualPayment(Request $request, Invoice $invoice)`).
+    *   Validar los datos.
+    *   Crear un registro en `transactions` asociado a la factura.
+    *   Actualizar el estado de la `invoice` a 'paid' si el monto cubre el total.
+    *   Actualizar `invoice.paid_date`.
+    *   Si el pago activa servicios, cambiar el estado de los `client_services` asociados a la orden de la factura (si aplica).
+*   `[ ]` **Verificación:** Un administrador puede registrar un pago manual para una factura, y el estado de la factura se actualiza.
+
+### 6.20. Listado de Transacciones (Admin)
+*   `[ ]` Crear `Admin\TransactionController.php` (solo método `index` por ahora).
+*   `[ ]` Ruta para `admin.transactions.index`.
+*   `[ ]` Implementar `index()`: Listar todas las transacciones, paginadas, con filtros. Vista `Admin/Transactions/Index.vue`.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Los administradores pueden ver un listado de todas las transacciones.
+
+---
+**¡Gestión Básica de Transacciones Implementada!**
+El siguiente paso es comenzar con el sistema de soporte. La integración con pasarelas de pago reales será una fase posterior.
+```
