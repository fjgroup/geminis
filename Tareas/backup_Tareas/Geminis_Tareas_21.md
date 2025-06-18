
**Tarea 21: Gestión de Dominios (Base)**
Migración, modelo y CRUD básico (Admin) para `Domains`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_21.md
@@ -0,0 +1,67 @@
+# Geminis - Plan de Tareas Detallado - Parte 21
+
+Este documento se enfoca en la entidad `Domain`, para la gestión de nombres de dominio.
+
+## Fase 8: Módulos Adicionales
+
+### 8.1. Migración de la Tabla `domains`
+*   **Contexto:** Almacena información sobre los dominios registrados o gestionados a través de la plataforma.
+*   `[ ]` Crear la migración para la tabla `domains`:
+    ```bash
+    php artisan make:migration create_domains_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('domains', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('client_id')->constrained('users');
+        $table->foreignId('reseller_id')->nullable()->constrained('users');
+        $table->foreignId('order_id')->nullable()->constrained('orders')->comment('Orden de compra/renovación');
+        $table->foreignId('client_service_id')->nullable()->unique()->constrained('client_services')->onDelete('set null')->comment('Si se gestiona como un servicio facturable');
+        $table->string('domain_name')->unique();
+        $table->string('registrar_module_slug')->nullable()->index()->comment('Módulo de registrador usado');
+        $table->date('registration_date');
+        $table->date('expiry_date')->index();
+        $table->date('next_due_date')->index()->comment('Próxima fecha de pago para renovación');
+        $table->enum('status', ['pending_registration', 'pending_transfer', 'active', 'expired', 'cancelled', 'fraud'])->default('pending_registration')->index();
+        $table->boolean('auto_renew_enabled')->default(false);
+        $table->boolean('id_protection_enabled')->default(false);
+        $table->text('epp_code_encrypted')->nullable(); // Encriptar
+        $table->string('nameserver1')->nullable();
+        $table->string('nameserver2')->nullable();
+        $table->string('nameserver3')->nullable();
+        $table->string('nameserver4')->nullable();
+        $table->text('admin_notes')->nullable();
+        $table->timestamps();
+        $table->softDeletes();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `domains` existe.
+
+### 8.2. Modelo `Domain`
+*   `[ ]` Crear el modelo `Domain`: `php artisan make:model Domain`
+*   `[ ]` Configurar `$fillable`, `$casts` (fechas, epp_code_encrypted).
+*   `[ ]` Definir relaciones: `client()`, `reseller()`, `order()`, `clientService()`.
+*   `[ ]` **Verificación:** Se pueden crear dominios mediante Tinker.
+
+### 8.3. CRUD Básico para `Domain` (Admin)
+*   **Contexto:** Los administradores deben poder gestionar los registros de dominios.
+*   `[ ]` Crear `Admin\DomainController.php`:
+    ```bash
+    php artisan make:controller Admin/DomainController --resource --model=Domain
+    ```
+*   `[ ]` Definir rutas resource para `domains` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`: Listar dominios, paginados, con filtros. Vista `Admin/Domains/Index.vue`.
+*   `[ ]` Implementar `create()` y `store()`: Formulario para registrar un dominio manualmente. Vista `Admin/Domains/Create.vue`.
+*   `[ ]` Implementar `edit()` y `update()`: Formulario para editar detalles del dominio (fechas, estado, nameservers, etc.). Vista `Admin/Domains/Edit.vue`.
+*   `[ ]` Implementar `destroy()`.
+*   `[ ]` Crear FormRequests (`StoreDomainRequest`, `UpdateDomainRequest`).
+*   `[ ]` (Opcional) Crear `DomainPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUD básico de dominios funciona.
+
+---
+**¡Gestión Base de Dominios (Admin) Implementada!**
+La integración real con registradores de dominios será una fase avanzada. El siguiente paso es la gestión de servidores.
+```
