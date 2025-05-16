--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_06.md
@@ -0,0 +1,134 @@
+# Geminis - Plan de Tareas Detallado - Parte 06
+
+Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la gestión de precios para los productos.
+
+## Fase 3: Gestión de Productos - Continuación
+
+### 3.1. Migración de la Tabla `product_pricing`
+*   **Contexto:** Necesitamos una tabla para almacenar los diferentes precios y ciclos de facturación para cada producto.
+*   `[ ]` Crear la migración para la tabla `product_pricing`:
+    ```bash
+    php artisan make:migration create_product_pricing_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración para que coincida con la definición de la tabla `product_pricing` en `Geminis_Estructura.md`.
+    ```php
+    // database/migrations/xxxx_xx_xx_xxxxxx_create_product_pricing_table.php
+    Schema::create('product_pricing', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
+        $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially', 'one_time'])->index();
+        $table->decimal('price', 10, 2);
+        $table->decimal('setup_fee', 10, 2)->default(0.00);
+        $table->string('currency_code', 3)->index();
+        $table->boolean('is_active')->default(true);
+        $table->timestamps();
+        // Constraint: Índice único en (product_id, billing_cycle, currency_code)
+        $table->unique(['product_id', 'billing_cycle', 'currency_code'], 'product_cycle_currency_unique');
+    });
+    ```
+*   `[ ]` Ejecutar la migración:
+    ```bash
+    php artisan migrate
+    ```
+*   `[ ]` **Verificación:** La tabla `product_pricing` existe en la base de datos con las columnas y el índice único correctos.
+
+### 3.2. Modelo `ProductPricing`
+*   `[ ]` Crear el modelo `ProductPricing`:
+    ```bash
+    php artisan make:model ProductPricing
+    ```
+*   `[ ]` En `app/Models/ProductPricing.php`, configurar la propiedad `$fillable`:
+    ```php
+    // app/Models/ProductPricing.php
+    protected $fillable = [
+        'product_id',
+        'billing_cycle',
+        'price',
+        'setup_fee',
+        'currency_code',
+        'is_active',
+    ];
+    // Especificar el nombre de la tabla si no sigue la convención plural
+    // protected $table = 'product_pricing';
+    ```
+*   `[ ]` Definir la relación `product()` en `ProductPricing.php`:
+    ```php
+    public function product()
+    {
+        return $this->belongsTo(Product::class);
+    }
+    ```
+*   `[ ]` Definir la relación `pricings()` en `app/Models/Product.php`:
+    ```php
+    // app/Models/Product.php
+    public function pricings()
+    {
+        return $this->hasMany(ProductPricing::class);
+    }
+    ```
+*   `[ ]` **Verificación:** Puedes crear y asociar precios a productos usando Tinker.
+
+### 3.3. Controlador para `ProductPricing` (Integrado en `AdminProductController`)
+*   **Contexto:** La gestión de precios se hará anidada dentro de la gestión de productos. No crearemos un controlador separado para `ProductPricing` en el admin, sino que añadiremos métodos a `AdminProductController` o manejaremos la lógica directamente en las vistas de edición de productos.
+    *   Por simplicidad inicial, podríamos añadir una sección en la vista `Admin/Products/Edit.vue` para listar, añadir, editar y eliminar precios asociados a ese producto.
+
+### 3.4. Rutas para Precios de Productos (Anidadas o Acciones en `AdminProductController`)
+*   **Decisión:** Para mantenerlo simple, las acciones de precios se manejarán a través de nuevos métodos en `AdminProductController` y no como un resource anidado completo.
+*   `[ ]` Añadir rutas en `routes/web.php` para las acciones de precios (ejemplos):
+    ```php
+    // routes/web.php (dentro del grupo admin)
+    // ...
+    Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');
+    Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');
+    Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');
+    // La edición podría ser un modal en la vista de edición del producto, o una vista separada si es compleja.
+    // Route::get('products/{product}/pricing/{pricing}/edit', [AdminProductController::class, 'editPricing'])->name('products.pricing.edit');
+    ```
+
+### 3.5. Vistas para Precios de Productos (Integradas en `Admin/Products/Edit.vue`)
+*   `[ ]` Modificar `resources/js/Pages/Admin/Products/Edit.vue`:
+    *   Añadir una sección para mostrar una tabla de los precios existentes para el producto actual (`props.product.pricings`).
+    *   Incluir un formulario (quizás un modal) para añadir un nuevo precio a ese producto.
+    *   Permitir editar/eliminar precios existentes.
+    *   Campos del formulario de precios: `billing_cycle` (select), `price` (number), `setup_fee` (number), `currency_code` (select), `is_active` (checkbox).
+*   `[ ]` **Verificación:**
+    *   Al editar un producto, se pueden ver, añadir, editar y eliminar sus precios.
+    *   Las validaciones para los precios funcionan correctamente.
+
+### 3.6. Lógica en `AdminProductController` para Precios
+*   `[ ]` Implementar el método `storePricing(Request $request, Product $product)` en `AdminProductController.php`:
+    *   Validar los datos del nuevo precio.
+    *   Crear el `ProductPricing` asociado al `$product`.
+    *   Redirigir de vuelta a la página de edición del producto con un mensaje de éxito.
+*   `[ ]` Implementar el método `updatePricing(Request $request, Product $product, ProductPricing $pricing)`:
+    *   Validar los datos.
+    *   Actualizar el `$pricing`.
+    *   Redirigir.
+*   `[ ]` Implementar el método `destroyPricing(Product $product, ProductPricing $pricing)`:
+    *   Eliminar el `$pricing`.
+    *   Redirigir.
+*   `[ ]` **Verificación:** Las operaciones CRUD para los precios funcionan desde la página de edición del producto.
+
+### 3.7. Políticas de Acceso para `ProductPricing` (Opcional, o integrada en `ProductPolicy`)
+*   **Contexto:** Si la lógica de quién puede gestionar precios es la misma que quién puede gestionar productos, la `ProductPolicy` existente podría ser suficiente. Si es más granular, se podría crear una `ProductPricingPolicy`.
+*   `[ ]` (Si se crea) Generar `ProductPricingPolicy`:
+    ```bash
+    php artisan make:policy ProductPricingPolicy --model=ProductPricing
+    ```
+*   `[ ]` (Si se crea) Registrarla en `AuthServiceProvider`.
+*   `[ ]` (Si se crea) Implementar métodos y aplicarlos en `AdminProductController` para las acciones de precios.
+
+### 3.8. FormRequests para Precios (Opcional, o validación en controlador)
+*   **Contexto:** Para validaciones más complejas o reutilizables.
+*   `[ ]` (Si se crea) `php artisan make:request Admin/StoreProductPricingRequest`
+*   `[ ]` (Si se crea) `php artisan make:request Admin/UpdateProductPricingRequest`
+*   `[ ]` Implementar `authorize()` y `rules()` en estos FormRequests.
+*   `[ ]` Usarlos en los métodos `storePricing` y `updatePricing` del `AdminProductController`.
+
+---
+**¡Gestión de Precios de Productos Implementada!**
+Ahora los productos pueden tener diferentes opciones de precios y ciclos de facturación.
+Los siguientes pasos se enfocarán en módulos más complejos como la gestión de clientes, órdenes y facturación.
+```

Y ahora, para guardar tu progreso en GitHub, aquí tienes los comandos que debes ejecutar en tu terminal, desde la raíz de tu proyecto (`e:\herd\geminis`):

1.  **Verificar el estado de tus archivos (opcional pero recomendado):**
    ```bash
    git status
    ```
    Esto te mostrará una lista de los cambios, incluyendo el nuevo archivo `Geminis_Tareas_06.md`.

2.  **Añadir los archivos al "staging area":**
    ```bash
    git add .
    ```

3.  **Hacer "commit" de los cambios:**
    ```bash
    git commit -m "Finalización Tarea 05 (CRUD Productos) e inicio Tarea 06 (Precios de Productos)"
    ```
    (O un mensaje más específico si lo prefieres, como "CRUD de Productos completo, AdminLayout actualizado, creado archivo Tarea 06")

4.  **Subir los cambios a GitHub (push):**
    ```bash
    git push origin main
    ```
    (O el nombre de tu rama si estás usando una diferente).

¡Listo! Con esto, tu progreso estará seguro y tendrás el esqueleto para la siguiente fase de desarrollo. ¡Gran trabajo!
