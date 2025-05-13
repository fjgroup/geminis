
**Tarea 18: Sistema de Soporte (Departamentos y Tickets - Base)**
Migraciones y modelos para `SupportDepartments` y `SupportTickets`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_18.md
@@ -0,0 +1,75 @@
+# Geminis - Plan de Tareas Detallado - Parte 18
+
+Este documento inicia la implementación del sistema de soporte, comenzando con los departamentos y la estructura base de los tickets.
+
+## Fase 7: Sistema de Soporte
+
+### 7.1. Migración de la Tabla `support_departments`
+*   **Contexto:** Los tickets de soporte se organizan en departamentos.
+*   `[ ]` Crear la migración para la tabla `support_departments`:
+    ```bash
+    php artisan make:migration create_support_departments_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('support_departments', function (Blueprint $table) {
+        $table->id();
+        $table->string('name');
+        $table->string('email_address')->nullable()->unique()->comment('Para crear tickets por email');
+        $table->boolean('is_public')->default(true)->comment('Visible para clientes');
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para deptos. globales');
+        $table->foreignId('auto_assign_user_id')->nullable()->constrained('users')->comment('Agente asignado por defecto');
+        $table->integer('display_order')->default(0);
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `support_departments` existe.
+
+### 7.2. Modelo `SupportDepartment`
+*   `[ ]` Crear el modelo `SupportDepartment`: `php artisan make:model SupportDepartment`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relaciones: `reseller()` (belongsTo User, opcional), `autoAssignUser()` (belongsTo User, opcional), `tickets()` (hasMany SupportTicket).
+*   `[ ]` **Verificación:** Se pueden crear departamentos mediante Tinker.
+
+### 7.3. Migración de la Tabla `support_tickets`
+*   **Contexto:** Almacena los tickets de soporte creados por los clientes o el personal.
+*   `[ ]` Crear la migración para la tabla `support_tickets`:
+    ```bash
+    php artisan make:migration create_support_tickets_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('support_tickets', function (Blueprint $table) {
+        $table->id();
+        $table->string('ticket_number')->unique();
+        $table->foreignId('client_id')->constrained('users');
+        $table->foreignId('reseller_id')->nullable()->constrained('users');
+        $table->foreignId('department_id')->constrained('support_departments');
+        $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->comment('Agente asignado');
+        $table->foreignId('client_service_id')->nullable()->constrained('client_services')->comment('Servicio relacionado, si aplica');
+        $table->string('subject');
+        $table->enum('status', ['open', 'client_reply', 'staff_reply', 'on_hold', 'in_progress', 'closed'])->default('open')->index();
+        $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
+        $table->timestamp('last_reply_at')->nullable();
+        $table->string('last_replier_name')->nullable(); // O FK a users si siempre es un usuario del sistema
+        $table->timestamps();
+        $table->softDeletes();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `support_tickets` existe.
+
+### 7.4. Modelo `SupportTicket`
+*   `[ ]` Crear el modelo `SupportTicket`: `php artisan make:model SupportTicket`
+*   `[ ]` Configurar `$fillable`, `$casts` (para `last_reply_at`).
+*   `[ ]` Definir relaciones: `client()`, `reseller()`, `department()`, `assignedToUser()`, `clientService()`, `replies()` (hasMany SupportTicketReply).
+*   `[ ]` En el evento `creating` del modelo, generar un `ticket_number` único.
+*   `[ ]` **Verificación:** Se pueden crear tickets mediante Tinker.
+
+---
+**¡Modelos Base para Departamentos y Tickets de Soporte Implementados!**
+La siguiente tarea se centrará en el CRUD para departamentos y la creación/listado de tickets.
+```
