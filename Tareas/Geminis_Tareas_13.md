
**Tarea 13: Órdenes (Base)**
Migración y modelos para `Orders` y `OrderItems`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_13.md
@@ -0,0 +1,82 @@
+# Geminis - Plan de Tareas Detallado - Parte 13
+
+Este documento se enfoca en la creación de las entidades `Order` y `OrderItem`, que son el primer paso en el proceso de compra de un cliente.
+
+## Fase 6: Proceso de Compra y Facturación
+
+### 6.1. Migración de la Tabla `orders`
+*   **Contexto:** Registra la intención de compra de un cliente antes de que se genere una factura o se aprovisione un servicio.
+*   `[ ]` Crear la migración para la tabla `orders`:
+    ```bash
+    php artisan make:migration create_orders_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('orders', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('client_id')->constrained('users');
+        $table->foreignId('reseller_id')->nullable()->constrained('users');
+        $table->string('order_number')->unique();
+        $table->foreignId('invoice_id')->nullable()->unique()->constrained('invoices')->onDelete('set null'); // Se llenará después de generar la factura
+        $table->timestamp('order_date');
+        $table->enum('status', ['pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled'])->default('pending_payment')->index();
+        $table->decimal('total_amount', 10, 2);
+        $table->string('currency_code', 3);
+        $table->string('payment_gateway_slug')->nullable()->index();
+        $table->ipAddress('ip_address')->nullable();
+        $table->text('notes')->nullable();
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `orders` existe.
+
+### 6.2. Modelo `Order`
+*   `[ ]` Crear el modelo `Order`:
+    ```bash
+    php artisan make:model Order
+    ```
+*   `[ ]` En `app/Models/Order.php`, configurar `$fillable` y `$casts` (para `order_date`).
+    ```php
+    protected $fillable = [
+        'client_id', 'reseller_id', 'order_number', 'invoice_id', 'order_date', 'status',
+        'total_amount', 'currency_code', 'payment_gateway_slug', 'ip_address', 'notes',
+    ];
+    protected $casts = ['order_date' => 'datetime'];
+    ```
+*   `[ ]` Definir relaciones: `client()` (belongsTo User), `reseller()` (belongsTo User), `invoice()` (belongsTo Invoice), `items()` (hasMany OrderItem).
+*   `[ ]` **Verificación:** Se pueden crear órdenes mediante Tinker.
+
+### 6.3. Migración de la Tabla `order_items`
+*   **Contexto:** Detalla cada producto o servicio incluido en una orden.
+*   `[ ]` Crear la migración para la tabla `order_items`:
+    ```bash
+    php artisan make:migration create_order_items_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('order_items', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
+        $table->foreignId('product_id')->constrained('products');
+        $table->foreignId('product_pricing_id')->constrained('product_pricing'); // Ciclo de facturación elegido
+        $table->enum('item_type', ['product', 'addon', 'domain_registration', 'domain_renewal', 'domain_transfer', 'configurable_option'])->index();
+        $table->string('description'); // Ej: "Web Hosting - Plan Básico (Mensual)"
+        $table->integer('quantity')->default(1);
+        $table->decimal('unit_price', 10, 2);
+        $table->decimal('setup_fee', 10, 2)->default(0.00);
+        $table->decimal('total_price', 10, 2); // (unit_price * quantity) + setup_fee
+        $table->string('domain_name')->nullable(); // Si el ítem es un dominio
+        $table->integer('registration_period_years')->nullable(); // Para dominios
+        $table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null'); // Se llenará después de aprovisionar
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `order_items` existe.
+
+### 6.4. Modelo `OrderItem`
+*   `[ ]` Crear el modelo `OrderItem`: `php artisan make:model OrderItem`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relaciones: `order()` (belongsTo Order), `product()` (belongsTo Product), `productPricing()` (belongsTo ProductPricing), `clientService()` (belongsTo ClientService).
+*   `[ ]` **Verificación:** Se pueden crear ítems de orden y asociarlos a órdenes.
+
+---
+**¡Modelos Base para Órdenes Implementados!**
+La siguiente tarea se centrará en el proceso de creación de órdenes por parte del cliente y su listado.
+```
