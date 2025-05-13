
**Tarea 15: Facturas (Base)**
Migración y modelos para `Invoices` y `InvoiceItems`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_15.md
@@ -0,0 +1,78 @@
+# Geminis - Plan de Tareas Detallado - Parte 15
+
+Este documento se enfoca en la creación de las entidades `Invoice` e `InvoiceItem`, que representan las obligaciones de pago formales.
+
+## Fase 6: Proceso de Compra y Facturación - Continuación
+
+### 6.8. Migración de la Tabla `invoices`
+*   **Contexto:** Almacena las facturas generadas para los clientes, ya sea a partir de una orden o para renovaciones de servicios.
+*   `[ ]` Crear la migración para la tabla `invoices`:
+    ```bash
+    php artisan make:migration create_invoices_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('invoices', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('client_id')->constrained('users');
+        $table->foreignId('reseller_id')->nullable()->constrained('users');
+        // $table->foreignId('order_id')->nullable()->unique()->constrained('orders')->onDelete('set null'); // Ya está en orders.invoice_id
+        $table->string('invoice_number')->unique();
+        $table->date('issue_date');
+        $table->date('due_date')->index();
+        $table->date('paid_date')->nullable();
+        $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections'])->default('unpaid')->index();
+        $table->decimal('subtotal', 10, 2);
+        $table->string('tax1_name')->nullable();
+        $table->decimal('tax1_rate', 5, 2)->nullable(); // Ej: 21.00 para 21%
+        $table->decimal('tax1_amount', 10, 2)->nullable();
+        $table->string('tax2_name')->nullable();
+        $table->decimal('tax2_rate', 5, 2)->nullable();
+        $table->decimal('tax2_amount', 10, 2)->nullable();
+        $table->decimal('total_amount', 10, 2);
+        $table->string('currency_code', 3);
+        $table->text('notes_to_client')->nullable();
+        $table->text('admin_notes')->nullable();
+        $table->timestamps();
+        $table->softDeletes();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `invoices` existe.
+
+### 6.9. Modelo `Invoice`
+*   `[ ]` Crear el modelo `Invoice`: `php artisan make:model Invoice`
+*   `[ ]` Configurar `$fillable`, `$casts` (para fechas).
+*   `[ ]` Definir relaciones: `client()`, `reseller()`, `order()` (hasOne o belongsTo, dependiendo de la FK principal), `items()` (hasMany InvoiceItem), `transactions()` (hasMany Transaction).
+*   `[ ]` **Verificación:** Se pueden crear facturas mediante Tinker.
+
+### 6.10. Migración de la Tabla `invoice_items`
+*   **Contexto:** Detalla los conceptos facturados en cada factura.
+*   `[ ]` Crear la migración para la tabla `invoice_items`:
+    ```bash
+    php artisan make:migration create_invoice_items_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('invoice_items', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
+        $table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null')->comment('Para ítems de renovación');
+        $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null')->comment('Para ítems originados de una orden');
+        $table->string('description'); // Ej: "Web Hosting - Plan Básico (Renovación Mensual)"
+        $table->integer('quantity')->default(1);
+        $table->decimal('unit_price', 10, 2);
+        $table->decimal('total_price', 10, 2); // unit_price * quantity
+        $table->boolean('taxable')->default(true);
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `invoice_items` existe.
+
+### 6.11. Modelo `InvoiceItem`
+*   `[ ]` Crear el modelo `InvoiceItem`: `php artisan make:model InvoiceItem`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relaciones: `invoice()` (belongsTo Invoice), `clientService()` (belongsTo ClientService), `orderItem()` (belongsTo OrderItem).
+*   `[ ]` **Verificación:** Se pueden crear ítems de factura.
+
+---
+**¡Modelos Base para Facturas Implementados!**
+La siguiente tarea se centrará en la lógica de generación de facturas y su listado.
+```
