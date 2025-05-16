--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_07.md
@@ -0,0 +1,109 @@
+# Geminis - Plan de Tareas Detallado - Parte 07
+
+Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en los perfiles de revendedor y los grupos de opciones configurables para los productos.
+
+## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos
+
+### 4.1. Migración de la Tabla `reseller_profiles`
+*   **Contexto:** Necesitamos una tabla para almacenar información y configuraciones específicas de los usuarios con rol 'reseller'.
+*   `[ ]` Crear la migración para la tabla `reseller_profiles`:
+    ```bash
+    php artisan make:migration create_reseller_profiles_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración para que coincida con la definición de la tabla `reseller_profiles` en `Geminis_Estructura.md`.
+    ```php
+    // database/migrations/xxxx_xx_xx_xxxxxx_create_reseller_profiles_table.php
+    Schema::create('reseller_profiles', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
+        $table->string('brand_name')->nullable();
+        $table->string('custom_domain')->nullable()->unique();
+        $table->string('logo_url')->nullable();
+        $table->string('support_email')->nullable();
+        $table->string('terms_url')->nullable();
+        $table->boolean('allow_custom_products')->default(false);
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración:
+    ```bash
+    php artisan migrate
+    ```
+*   `[ ]` **Verificación:** La tabla `reseller_profiles` existe en la base de datos con las columnas correctas.
+
+### 4.2. Modelo `ResellerProfile`
+*   `[ ]` Crear el modelo `ResellerProfile`:
+    ```bash
+    php artisan make:model ResellerProfile
+    ```
+*   `[ ]` En `app/Models/ResellerProfile.php`, configurar la propiedad `$fillable`:
+    ```php
+    // app/Models/ResellerProfile.php
+    protected $fillable = [
+        'user_id',
+        'brand_name',
+        'custom_domain',
+        'logo_url',
+        'support_email',
+        'terms_url',
+        'allow_custom_products',
+    ];
+    ```
+*   `[ ]` Definir la relación `user()` en `ResellerProfile.php` (un perfil pertenece a un usuario):
+    ```php
+    public function user()
+    {
+        return $this->belongsTo(User::class);
+    }
+    ```
+*   `[ ]` Definir la relación `resellerProfile()` en `app/Models/User.php` (un usuario revendedor tiene un perfil):
+    ```php
+    // app/Models/User.php
+    public function resellerProfile()
+    {
+        return $this->hasOne(ResellerProfile::class);
+    }
+    ```
+*   `[ ]` **Verificación:** Puedes crear y asociar perfiles a usuarios revendedores usando Tinker.
+
+### 4.3. CRUD Básico para `ResellerProfile` (Integrado en `Admin\UserController`)
+*   **Contexto:** La gestión de perfiles de revendedor se podría integrar en la vista de edición del usuario (`Admin/Users/Edit.vue`) si el usuario tiene el rol 'reseller'.
+*   `[ ]` Modificar `resources/js/Pages/Admin/Users/Edit.vue`:
+    *   Añadir una sección que solo se muestre si `props.user.role === 'reseller'`.
+    *   En esta sección, mostrar un formulario para editar los campos de `ResellerProfile` (brand_name, custom_domain, allow_custom_products, etc.).
+    *   El `useForm` principal podría extenderse para incluir estos campos, o manejar un segundo `useForm` para el perfil.
+*   `[ ]` Modificar `app/Http/Controllers/Admin/UserController.php` (método `edit`):
+    *   Si el usuario es un revendedor, cargar su `resellerProfile` (ej. `$user->load('resellerProfile');`) y pasarlo a la vista.
+*   `[ ]` Modificar `app/Http/Controllers/Admin/UserController.php` (método `update`):
+    *   Si el usuario es un revendedor y se envían datos del perfil, validar y actualizar/crear el `ResellerProfile` asociado.
+*   `[ ]` Actualizar `app/Http/Requests/Admin/UpdateUserRequest.php` para incluir reglas de validación para los campos de `ResellerProfile` (ej. `reseller_profile.brand_name`).
+*   `[ ]` **Verificación:** Al editar un usuario revendedor, se pueden ver y modificar los datos de su perfil.
+
+### 4.4. Migración de la Tabla `configurable_option_groups`
+*   **Contexto:** Los productos pueden tener opciones configurables (ej: Sistema Operativo para un VPS). Estas opciones se agrupan.
+*   `[ ]` Crear la migración para la tabla `configurable_option_groups`:
+    ```bash
+    php artisan make:migration create_configurable_option_groups_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    // database/migrations/xxxx_xx_xx_xxxxxx_create_configurable_option_groups_table.php
+    Schema::create('configurable_option_groups', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade'); // Puede ser global (NULL) o específico de un producto
+        $table->string('name');
+        $table->string('description')->nullable();
+        $table->integer('display_order')->default(0);
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `configurable_option_groups` existe.
+
+---
+**¡Perfiles de Revendedor y Grupos de Opciones Configurables (Base) Implementados!**
+El siguiente paso será crear el modelo para `ConfigurableOptionGroup`, sus relaciones, y empezar a construir la interfaz para gestionarlos.
+```

Y, por supuesto, antes de sumergirte en la Tarea 07, vamos a guardar todo el excelente trabajo que has hecho hasta ahora en GitHub:

1.  **Verificar el estado de tus archivos:**
    ```bash
    git status
    ```
2.  **Añadir los archivos al "staging area":**
    ```bash
    git add .
    ```
3.  **Hacer "commit" de los cambios:**
    ```bash
    git commit -m "Tarea 06 completada: Gestión de precios de productos implementada"
    ```
4.  **Subir los cambios a GitHub (push):**
    ```bash
    git push origin main
    ```
    (O el nombre de tu rama si es diferente).

¡Listo! Tu progreso está seguro y ya tienes el camino trazado para la siguiente etapa. ¡Sigamos adelante cuando estés listo!
