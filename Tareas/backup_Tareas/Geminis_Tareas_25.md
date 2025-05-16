
**Tarea 25: Logs de Actividad y Configuración (Base)**
Migraciones y modelos para `ActivityLogs` y `Settings`. Implementación básica.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_25.md
@@ -0,0 +1,75 @@
+# Geminis - Plan de Tareas Detallado - Parte 25
+
+Este documento se enfoca en la creación de las tablas para Logs de Actividad y Configuraciones del Sistema.
+
+## Fase 8: Módulos Adicionales - Continuación
+
+### 8.15. Migración de la Tabla `activity_logs`
+*   **Contexto:** Registra acciones importantes realizadas en el sistema para auditoría.
+*   `[ ]` Crear la migración para la tabla `activity_logs`:
+    ```bash
+    php artisan make:migration create_activity_logs_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('activity_logs', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que realizó la acción, NULL si es del sistema');
+        $table->foreignId('reseller_context_id')->nullable()->constrained('users')->onDelete('set null')->comment('Contexto del revendedor, si aplica');
+        $table->string('loggable_type')->nullable()->index()->comment('Modelo relacionado (polimórfico)');
+        $table->unsignedBigInteger('loggable_id')->nullable()->index()->comment('ID del modelo relacionado (polimórfico)');
+        $table->string('action')->index()->comment('Ej: created_client, updated_service_status');
+        $table->text('description');
+        $table->json('details')->nullable()->comment('Datos adicionales en formato JSON');
+        $table->ipAddress('ip_address')->nullable();
+        $table->text('user_agent')->nullable();
+        $table->timestamp('created_at')->useCurrent(); // Solo created_at, no updated_at
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `activity_logs` existe.
+
+### 8.16. Modelo `ActivityLog`
+*   `[ ]` Crear el modelo `ActivityLog`: `php artisan make:model ActivityLog`
+*   `[ ]` Configurar `$fillable`. Definir que no use `updated_at` (`const UPDATED_AT = null;`).
+*   `[ ]` Definir relaciones polimórficas `loggable()` y relaciones `user()`, `resellerContext()`.
+*   `[ ]` **Verificación:** Se pueden crear logs.
+
+### 8.17. Migración de la Tabla `settings`
+*   **Contexto:** Almacén flexible clave-valor para configuraciones de la plataforma y revendedores.
+*   `[ ]` Crear la migración para la tabla `settings`:
+    ```bash
+    php artisan make:migration create_settings_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('settings', function (Blueprint $table) {
+        $table->id();
+        $table->string('group_slug')->default('general')->index()->comment('Ej: general, billing, mail');
+        $table->string('key')->index();
+        $table->text('value')->nullable();
+        $table->boolean('is_encrypted')->default(false);
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->onDelete('cascade')->comment('NULL para config global');
+        $table->timestamps();
+        $table->unique(['group_slug', 'key', 'reseller_id']);
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `settings` existe.
+
+### 8.18. Modelo `Setting`
+*   `[ ]` Crear el modelo `Setting`: `php artisan make:model Setting`
+*   `[ ]` Configurar `$fillable`, `$casts` (para `value` si se almacena JSON, o `is_encrypted`).
+*   `[ ]` Definir relación `reseller()` (belongsTo User, opcional).
+*   `[ ]` **Verificación:** Se pueden crear configuraciones.
+
+### 8.19. Implementación Básica de Logs y Settings (Admin)
+*   `[ ]` (ActivityLog) Crear un Trait o Service para registrar logs fácilmente desde otros controladores/servicios.
+*   `[ ]` (ActivityLog) Implementar el registro de logs para acciones clave (ej. creación/actualización de usuarios, productos).
+*   `[ ]` (ActivityLog) Crear `Admin\ActivityLogController` con método `index()` para listar logs. Vista `Admin/ActivityLogs/Index.vue`.
+*   `[ ]` (Settings) Crear `Admin\SettingController` con métodos `index()` (listar settings agrupadas) y `update()` (para actualizar valores). Vista `Admin/Settings/Index.vue`.
+*   `[ ]` Añadir enlaces en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Se registran logs y se pueden ver. Se pueden ver y modificar configuraciones básicas.
+
+---
+**¡Logs de Actividad y Configuraciones Base Implementados!**
+Con esto se cubren varios módulos de soporte administrativo. Las siguientes tareas se enfocarán en los paneles de Revendedor y Cliente.
+```

Estos archivos cubren hasta la Tarea 25. Cuando lleguemos a ese punto, podemos definir las siguientes.

**¿Qué te parece esta tanda de archivos de tareas?** Si estás de acuerdo, puedes crear estos archivos en tu directorio `e:\herd\geminis\` (o `e:\herd\hosting\`) y luego, cuando quieras que trabajemos en una tarea específica, simplemente me muestras el contenido de ese archivo.
