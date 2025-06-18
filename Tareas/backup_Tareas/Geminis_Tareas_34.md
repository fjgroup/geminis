
**Tarea 34: Mejoras en Sistema de Soporte (Admin/Reseller)**
Funcionalidades adicionales para la gestión de tickets por administradores y revendedores.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_34.md
@@ -0,0 +1,48 @@
+# Geminis - Plan de Tareas Detallado - Parte 34
+
+Este documento se enfoca en añadir funcionalidades avanzadas al sistema de soporte para administradores y revendedores.
+
+## Fase 7: Sistema de Soporte - Mejoras
+
+### 7.14. Asignación de Tickets a Agentes (Admin/Reseller)
+*   **Contexto:** Los tickets deben poder asignarse a un agente específico (usuario admin o staff del revendedor).
+*   `[ ]` En `Admin/SupportTickets/Show.vue` y `Reseller/SupportTickets/Show.vue` (si los revendedores gestionan sus propios tickets/departamentos):
+    *   Añadir un select para "Asignar a" que liste los usuarios administradores (y/o staff del revendedor).
+    *   Al cambiar, actualizar `support_tickets.assigned_to_user_id` mediante una petición AJAX.
+*   `[ ]` Modificar `AdminSupportTicketController` (y `ResellerSupportTicketController` si aplica) para tener un método `assignTicket(Request $request, SupportTicket $ticket)`.
+    *   Validar `assigned_to_user_id`.
+    *   Actualizar el ticket.
+    *   (Opcional) Registrar en `activity_logs`.
+    *   (Opcional) Enviar email de notificación al agente asignado.
+*   `[ ]` **Verificación:** Se puede asignar un ticket a un agente.
+
+### 7.15. Notas Internas en Tickets
+*   **Contexto:** Permitir a los agentes añadir notas privadas a un ticket, no visibles para el cliente.
+*   `[ ]` Añadir campo `is_internal_note` (BOOLEAN, default FALSE) a la tabla `support_ticket_replies`.
+*   `[ ]` Modificar migración y ejecutar. Actualizar `$fillable` en `SupportTicketReply.php`.
+*   `[ ]` En `Admin/SupportTickets/Show.vue` (y `Reseller`):
+    *   Al añadir una respuesta, ofrecer un checkbox "Nota interna".
+    *   Las respuestas marcadas como internas deben tener un estilo visual diferente en el listado de respuestas (ej. fondo amarillo claro).
+    *   Estas notas NO deben ser visibles en `Client/SupportTickets/Show.vue`.
+*   `[ ]` Modificar `SupportTicketReplyController` (o donde se guarden las respuestas) para guardar el valor de `is_internal_note`.
+*   `[ ]` Modificar la carga de respuestas en `Client\SupportTicketController@show` para filtrar `where('is_internal_note', false)`.
+*   `[ ]` **Verificación:** Los agentes pueden añadir notas internas, y estas no son visibles para los clientes.
+
+### 7.16. (Opcional) Respuestas Predefinidas
+*   **Contexto:** Para agilizar respuestas comunes.
+*   `[ ]` Crear tabla `predefined_replies` (`id`, `department_id` (nullable), `title`, `content_text`, `created_at`, `updated_at`).
+*   `[ ]` Crear modelo `PredefinedReply` y CRUD básico en Admin para gestionarlas.
+*   `[ ]` En `Admin/SupportTickets/Show.vue` (y `Reseller`):
+    *   Añadir un select "Insertar Respuesta Predefinida".
+    *   Al seleccionar, el contenido de la respuesta predefinida se inserta en el textarea de respuesta del ticket.
+*   `[ ]` **Verificación:** Se pueden crear y usar respuestas predefinidas.
+
+### 7.17. Filtros Avanzados en Listado de Tickets (Admin)
+*   `[ ]` En `Admin/SupportTickets/Index.vue` y `AdminSupportTicketController@index`:
+    *   Añadir más filtros: por agente asignado, por cliente, por prioridad.
+*   `[ ]` **Verificación:** Los filtros funcionan correctamente.
+
+---
+**¡Mejoras en el Sistema de Soporte Implementadas!**
+La gestión de tickets es ahora más robusta para el personal.
+```
