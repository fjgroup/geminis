
**Tarea 23: Promociones (Base)**
Migración, modelo y CRUD básico (Admin) para `Promotions`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_23.md
@@ -0,0 +1,67 @@
+# Geminis - Plan de Tareas Detallado - Parte 23
+
+Este documento se enfoca en la gestión de Promociones y Cupones.
+
+## Fase 8: Módulos Adicionales - Continuación
+
+### 8.9. Migración de la Tabla `promotions`
+*   **Contexto:** Gestiona descuentos, cupones y ofertas especiales.
+*   `[ ]` Crear la migración para la tabla `promotions`:
+    ```bash
+    php artisan make:migration create_promotions_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('promotions', function (Blueprint $table) {
+        $table->id();
+        $table->string('name')->comment('Nombre interno descriptivo');
+        $table->string('code')->unique()->nullable()->comment('Código para el cliente, NULL si es automática');
+        $table->text('description')->nullable()->comment('Visible para el cliente');
+        $table->enum('type', ['percentage', 'fixed_amount']);
+        $table->decimal('value', 10, 2)->comment('Valor del descuento (ej. 20.00 para 20% o $20)');
+        $table->enum('applies_to', ['order', 'product', 'category', 'client_group'])->default('order'); // client_group es futuro
+        $table->json('product_ids')->nullable()->comment('Si applies_to es product');
+        // $table->json('category_ids')->nullable(); // Si aplica a categorías de productos (necesitaríamos tabla product_categories)
+        $table->decimal('min_order_amount', 10, 2)->nullable();
+        $table->unsignedInteger('max_uses')->nullable()->comment('Usos totales máximos');
+        $table->unsignedInteger('max_uses_per_client')->nullable();
+        $table->unsignedInteger('current_uses')->default(0);
+        $table->dateTime('start_date')->nullable();
+        $table->dateTime('end_date')->nullable();
+        $table->boolean('requires_code')->default(true);
+        $table->boolean('is_active')->default(true)->index();
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL si es de plataforma');
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `promotions` existe.
+
+### 8.10. Modelo `Promotion`
+*   `[ ]` Crear el modelo `Promotion`: `php artisan make:model Promotion`
+*   `[ ]` Configurar `$fillable`, `$casts` (fechas, `product_ids` a `array` o `json`).
+*   `[ ]` Definir relación `reseller()` (belongsTo User, opcional).
+*   `[ ]` **Verificación:** Se pueden crear promociones.
+
+### 8.11. CRUD Básico para `Promotion` (Admin)
+*   **Contexto:** Los administradores (y revendedores en su panel) deben poder crear y gestionar promociones.
+*   `[ ]` Crear `Admin\PromotionController.php`:
+    ```bash
+    php artisan make:controller Admin/PromotionController --resource --model=Promotion
+    ```
+*   `[ ]` Definir rutas resource para `promotions` en `routes/web.php` (admin).
+*   `[ ]` Implementar CRUD completo:
+    *   `index()`: Listar promociones. Vista `Admin/Promotions/Index.vue`.
+    *   `create()`/`store()`: Formulario para crear promociones. Vista `Admin/Promotions/Create.vue`.
+        *   Campos: nombre, código (generar si está vacío), descripción, tipo, valor, a qué aplica (productos específicos con un multi-select de productos), fechas, usos, etc.
+    *   `edit()`/`update()`: Formulario para editar. Vista `Admin/Promotions/Edit.vue`.
+    *   `destroy()`.
+*   `[ ]` Crear FormRequests (`StorePromotionRequest`, `UpdatePromotionRequest`).
+*   `[ ]` (Opcional) Crear `PromotionPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUD básico de promociones funciona.
+
+---
+**¡Gestión Base de Promociones (Admin) Implementada!**
+La lógica de aplicación de promociones a órdenes y facturas se implementará más adelante.
+```
