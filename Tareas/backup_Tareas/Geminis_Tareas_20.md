
**Tarea 20: Respuestas a Tickets de Soporte**
Migraciones y modelos para `SupportTicketReplies` y `SupportTicketAttachments`. Funcionalidad de respuesta.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_20.md
@@ -0,0 +1,79 @@
+# Geminis - Plan de Tareas Detallado - Parte 20
+
+Este documento se enfoca en la funcionalidad de respuestas para los tickets de soporte y la gestión de archivos adjuntos.
+
+## Fase 7: Sistema de Soporte - Continuación
+
+### 7.9. Migración de la Tabla `support_ticket_replies`
+*   **Contexto:** Almacena cada respuesta individual a un ticket de soporte.
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_support_ticket_replies_table
+    ```
+*   `[ ]` Modificar el método `up()` según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('support_ticket_replies', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
+        $table->foreignId('user_id')->nullable()->constrained('users')->comment('Usuario que respondió (cliente o staff)');
+        $table->text('message');
+        $table->ipAddress('ip_address')->nullable();
+        $table->boolean('is_staff_reply')->default(false); // True si la respuesta es de un admin/reseller staff
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `support_ticket_replies` existe.
+
+### 7.10. Modelo `SupportTicketReply`
+*   `[ ]` Crear el modelo `SupportTicketReply`: `php artisan make:model SupportTicketReply`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relaciones: `ticket()` (belongsTo SupportTicket), `user()` (belongsTo User, el autor de la respuesta).
+*   `[ ]` **Verificación:** Se pueden crear respuestas mediante Tinker.
+
+### 7.11. Migración de la Tabla `support_ticket_attachments`
+*   **Contexto:** Almacena los archivos adjuntos a los tickets o a sus respuestas.
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_support_ticket_attachments_table
+    ```
+*   `[ ]` Modificar el método `up()` según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('support_ticket_attachments', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('reply_id')->nullable()->constrained('support_ticket_replies')->onDelete('cascade');
+        $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade')->comment('Si se adjunta al crear ticket, antes de la primera respuesta');
+        $table->string('file_name_original');
+        $table->string('file_path_stored'); // Usar Laravel File Storage
+        $table->string('mime_type');
+        $table->unsignedInteger('file_size_bytes');
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `support_ticket_attachments` existe.
+
+### 7.12. Modelo `SupportTicketAttachment`
+*   `[ ]` Crear el modelo `SupportTicketAttachment`: `php artisan make:model SupportTicketAttachment`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relaciones: `reply()` (belongsTo SupportTicketReply), `ticket()` (belongsTo SupportTicket).
+*   `[ ]` **Verificación:** Se pueden crear registros de adjuntos.
+
+### 7.13. Funcionalidad de Respuesta a Tickets (Cliente y Admin)
+*   `[ ]` Modificar `Client\SupportTicketController@show(SupportTicket $ticket)`:
+    *   Cargar el ticket con sus respuestas (`$ticket->load('replies.user', 'replies.attachments')`).
+    *   Pasar datos a la vista `Client/SupportTickets/Show.vue`.
+*   `[ ]` Vista `Client/SupportTickets/Show.vue`:
+    *   Mostrar detalles del ticket y el historial de respuestas.
+    *   Formulario para que el cliente añada una nueva respuesta (mensaje, adjuntos opcionales).
+*   `[ ]` Método `Client\SupportTicketController@addReply(Request $request, SupportTicket $ticket)`:
+    *   Validar respuesta. Crear `SupportTicketReply`. Actualizar estado y `last_reply_at` del ticket.
+*   `[ ]` Repetir lógica similar para `Admin\SupportTicketController` y vistas `Admin/SupportTickets/Show.vue`.
+    *   Los admins/staff pueden cambiar el estado del ticket, asignarlo a otro agente.
+*   `[ ]` Implementar subida de archivos adjuntos (usar `Storage` de Laravel).
+*   `[ ]` **Verificación:** Clientes y administradores pueden ver tickets y responder. Se pueden adjuntar archivos.
+
+---
+**¡Funcionalidad de Respuestas a Tickets Implementada!**
+El sistema de soporte ahora es interactivo. Los siguientes pasos se enfocarán en módulos adicionales como dominios y servidores.
+```
