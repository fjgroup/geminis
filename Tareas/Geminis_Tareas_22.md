
**Tarea 22: Gestión de Servidores (Base)**
Migraciones y modelos para `Servers` y `ServerGroups`. CRUD básico (Admin).

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_22.md
@@ -0,0 +1,78 @@
+# Geminis - Plan de Tareas Detallado - Parte 22
+
+Este documento se enfoca en la gestión de Servidores y Grupos de Servidores, fundamentales para el aprovisionamiento de servicios de hosting.
+
+## Fase 8: Módulos Adicionales - Continuación
+
+### 8.4. Migración de la Tabla `server_groups`
+*   **Contexto:** Agrupa servidores para facilitar la asignación automática de nuevas cuentas de hosting.
+*   `[ ]` Crear la migración para la tabla `server_groups`:
+    ```bash
+    php artisan make:migration create_server_groups_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('server_groups', function (Blueprint $table) {
+        $table->id();
+        $table->string('name')->unique();
+        $table->enum('fill_type', ['fill_sequentially', 'fill_until_full_then_next', 'random'])->default('fill_until_full_then_next')->comment('Estrategia de asignación');
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `server_groups` existe.
+
+### 8.5. Modelo `ServerGroup`
+*   `[ ]` Crear el modelo `ServerGroup`: `php artisan make:model ServerGroup`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relación `servers()` (hasMany Server).
+*   `[ ]` **Verificación:** Se pueden crear grupos de servidores.
+
+### 8.6. Migración de la Tabla `servers`
+*   **Contexto:** Almacena la información de los servidores físicos o virtuales.
+*   `[ ]` Crear la migración para la tabla `servers`:
+    ```bash
+    php artisan make:migration create_servers_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('servers', function (Blueprint $table) {
+        $table->id();
+        $table->string('name')->unique();
+        $table->string('hostname_or_ip');
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('Si es un servidor de un revendedor');
+        $table->foreignId('server_group_id')->nullable()->constrained('server_groups')->onDelete('set null');
+        $table->string('module_slug')->index()->comment('Ej: cpanel, plesk');
+        $table->string('api_username')->nullable();
+        $table->text('api_password_or_key_encrypted')->nullable(); // Encriptar
+        $table->unsignedInteger('api_port')->nullable();
+        $table->boolean('api_use_ssl')->default(true);
+        $table->string('status_url')->nullable()->comment('URL para verificar estado del servidor');
+        $table->unsignedInteger('max_accounts')->nullable();
+        $table->unsignedInteger('current_accounts_count')->default(0);
+        $table->boolean('is_active')->default(true)->index();
+        $table->text('notes')->nullable();
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `servers` existe.
+
+### 8.7. Modelo `Server`
+*   `[ ]` Crear el modelo `Server`: `php artisan make:model Server`
+*   `[ ]` Configurar `$fillable`, `$casts` (para `api_password_or_key_encrypted`).
+*   `[ ]` Definir relaciones: `reseller()`, `group()` (belongsTo ServerGroup), `clientServices()` (hasMany ClientService).
+*   `[ ]` **Verificación:** Se pueden crear servidores.
+
+### 8.8. CRUD Básico para `ServerGroup` y `Server` (Admin)
+*   `[ ]` Crear controladores `Admin\ServerGroupController` y `Admin\ServerController` (resources).
+*   `[ ]` Definir rutas resource para `server-groups` y `servers` (admin).
+*   `[ ]` Implementar CRUDs completos para ambos, con sus vistas y FormRequests.
+    *   Para `Server`, el `server_group_id` será un select.
+*   `[ ]` (Opcional) Crear Policies y aplicarlas.
+*   `[ ]` Añadir enlaces en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUDs para grupos de servidores y servidores funcionan.
+
+---
+**¡Gestión Base de Servidores y Grupos Implementada!**
+La integración con módulos de aprovisionamiento (cPanel, Plesk) será una fase avanzada.
+```
