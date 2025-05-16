
**Tarea 11: Servicios de Cliente (Base)**
Migración, modelo y CRUD básico (Admin) para `ClientService`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_11.md
@@ -0,0 +1,76 @@
+# Geminis - Plan de Tareas Detallado - Parte 11
+
+Este documento se enfoca en la creación de la entidad `ClientService`, que representa las instancias de productos/servicios contratados por los clientes.
+
+## Fase 5: Gestión de Clientes y Servicios
+
+### 5.1. Migración de la Tabla `client_services`
+*   **Contexto:** Esta tabla es fundamental, ya que registra cada servicio activo (o inactivo) que un cliente ha contratado.
+*   `[ ]` Crear la migración para la tabla `client_services`:
+    ```bash
+    php artisan make:migration create_client_services_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('client_services', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('client_id')->constrained('users')->comment('FK a users.id del cliente');
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('FK a users.id del revendedor, si aplica');
+        $table->foreignId('order_id')->nullable()->constrained('orders')->comment('FK a la orden que originó este servicio');
+        $table->foreignId('product_id')->constrained('products');
+        $table->foreignId('product_pricing_id')->constrained('product_pricing')->comment('Ciclo de facturación elegido');
+        $table->string('domain_name')->nullable()->index();
+        $table->string('username')->nullable();
+        $table->text('password_encrypted')->nullable(); // Considerar encriptación real
+        $table->foreignId('server_id')->nullable()->constrained('servers');
+        $table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud'])->default('pending')->index();
+        $table->date('registration_date');
+        $table->date('next_due_date')->index();
+        $table->date('termination_date')->nullable();
+        $table->decimal('billing_amount', 10, 2); // Monto recurrente actual (puede incluir opciones)
+        $table->text('notes')->nullable();
+        $table->timestamps();
+        $table->softDeletes();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `client_services` existe con las columnas correctas.
+
+### 5.2. Modelo `ClientService`
+*   `[ ]` Crear el modelo `ClientService`:
+    ```bash
+    php artisan make:model ClientService
+    ```
+*   `[ ]` En `app/Models/ClientService.php`, configurar `$fillable` y `$dates` (para `registration_date`, `next_due_date`, `termination_date`).
+    ```php
+    protected $fillable = [
+        'client_id', 'reseller_id', 'order_id', 'product_id', 'product_pricing_id',
+        'domain_name', 'username', 'password_encrypted', 'server_id', 'status',
+        'registration_date', 'next_due_date', 'termination_date', 'billing_amount', 'notes',
+    ];
+    protected $casts = [
+        'registration_date' => 'date',
+        'next_due_date' => 'date',
+        'termination_date' => 'date',
+        'password_encrypted' => 'encrypted', // Si usas el cast 'encrypted' de Laravel
+    ];
+    // protected $dates = ['registration_date', 'next_due_date', 'termination_date']; // Alternativa a $casts para fechas
+    ```
+*   `[ ]` Definir relaciones: `client()` (belongsTo User), `reseller()` (belongsTo User), `product()` (belongsTo Product), `productPricing()` (belongsTo ProductPricing), `server()` (belongsTo Server), `order()` (belongsTo Order).
+*   `[ ]` Definir relación `configurableOptionsSelected()` (muchos a muchos con `configurable_options` a través de `client_service_configurable_options`).
+*   `[ ]` **Verificación:** Se pueden crear servicios y asociarlos mediante Tinker.
+
+### 5.3. CRUD Básico para `ClientService` (Admin)
+*   **Contexto:** Los administradores deben poder ver y gestionar (al menos inicialmente de forma manual) los servicios de los clientes.
+*   `[ ]` Crear controlador `Admin\ClientServiceController`:
+    ```bash
+    php artisan make:controller Admin/ClientServiceController --resource --model=ClientService
+    ```
+*   `[ ]` Definir rutas resource para `client-services` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`: Listar servicios, paginados, con filtros (cliente, producto, estado). Vista `Admin/ClientServices/Index.vue`.
+*   `[ ]` Implementar `create()` y `store()`: Formulario para creación manual de servicios. Vista `Admin/ClientServices/Create.vue`. (Seleccionar cliente, producto, ciclo, etc.).
+*   `[ ]` Implementar `edit()` y `update()`: Formulario para edición. Vista `Admin/ClientServices/Edit.vue`.
+*   `[ ]` Implementar `destroy()` (probablemente soft delete).
+*   `[ ]` Crear FormRequests (`StoreClientServiceRequest`, `UpdateClientServiceRequest`).
+*   `[ ]` (Opcional) Crear `ClientServicePolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUD básico de servicios funciona desde el panel de admin.
+
+---
+**¡Gestión Base de Servicios de Cliente (Admin) Implementada!**
+El siguiente paso será detallar la lógica de estados de los servicios y cómo se visualizan y gestionan desde el panel del cliente.
+```
