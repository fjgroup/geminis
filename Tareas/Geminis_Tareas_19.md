
**Tarea 19: CRUD Departamentos de Soporte y Gestión de Tickets (Cliente/Admin)**
CRUD para `SupportDepartments` (Admin). Creación y listado de `SupportTickets` (Cliente/Admin).

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_19.md
@@ -0,0 +1,73 @@
+# Geminis - Plan de Tareas Detallado - Parte 19
+
+Este documento se enfoca en la gestión de Departamentos de Soporte y la funcionalidad inicial para que los clientes y administradores manejen los Tickets de Soporte.
+
+## Fase 7: Sistema de Soporte - Continuación
+
+### 7.5. CRUD para `SupportDepartment` (Admin)
+*   **Contexto:** Los administradores necesitan gestionar los departamentos de soporte.
+*   `[ ]` Crear `Admin\SupportDepartmentController`:
+    ```bash
+    php artisan make:controller Admin/SupportDepartmentController --resource --model=SupportDepartment
+    ```
+*   `[ ]` Definir rutas resource para `support-departments` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`: Listar departamentos. Vista `Admin/SupportDepartments/Index.vue`.
+*   `[ ]` Implementar `create()` y `store()`: Formulario para crear departamentos (nombre, email, público, revendedor (opcional), auto-asignar (opcional)). Vista `Admin/SupportDepartments/Create.vue`.
+*   `[ ]` Implementar `edit()` y `update()`: Formulario para editar. Vista `Admin/SupportDepartments/Edit.vue`.
+*   `[ ]` Implementar `destroy()`.
+*   `[ ]` Crear FormRequests (`StoreSupportDepartmentRequest`, `UpdateSupportDepartmentRequest`).
+*   `[ ]` (Opcional) Crear `SupportDepartmentPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUD de departamentos de soporte funciona.
+
+### 7.6. Creación de Tickets de Soporte (Cliente)
+*   **Contexto:** Los clientes deben poder abrir nuevos tickets de soporte.
+*   `[ ]` Crear `Client\SupportTicketController.php` (o añadir a un controlador de cliente existente).
+*   `[ ]` Método `create()`:
+    *   Obtener los departamentos de soporte públicos (y los del revendedor del cliente, si aplica).
+    *   Obtener los servicios activos del cliente (`client_services`) para el select "Servicio Relacionado".
+    *   Pasar datos a la vista `Client/SupportTickets/Create.vue`.
+*   `[ ]` Vista `Client/SupportTickets/Create.vue`:
+    *   Formulario con campos: Departamento (select), Servicio Relacionado (select, opcional), Asunto, Prioridad (select), Mensaje (textarea), Adjuntos (opcional, futuro).
+*   `[ ]` Método `store(Request $request)`:
+    *   Validar datos.
+    *   Crear el `SupportTicket` (asignar `client_id`, `reseller_id` si aplica, `department_id`, etc.).
+    *   Crear el primer `SupportTicketReply` con el mensaje del cliente.
+    *   Actualizar `last_reply_at` y `last_replier_name` en el ticket.
+    *   (Futuro) Enviar notificaciones por email.
+    *   Redirigir al cliente a la vista del ticket recién creado.
+*   `[ ]` Definir rutas en `routes/web.php` para el cliente (ej. `/client/tickets/create`, `/client/tickets`).
+*   `[ ]` **Verificación:** Un cliente puede crear un nuevo ticket de soporte.
+
+### 7.7. Listado de Tickets de Soporte (Cliente)
+*   `[ ]` En `Client\SupportTicketController.php`, método `index()`:
+    *   Obtener los tickets del cliente autenticado.
+    *   Paginados y ordenados (ej. por última respuesta).
+    *   Vista `Client/SupportTickets/Index.vue` (Tabla: Número Ticket, Asunto, Departamento, Estado, Última Actualización).
+*   `[ ]` Añadir enlace "Mis Tickets" en `ClientLayout.vue` o `AppLayout.vue`.
+*   `[ ]` **Verificación:** Un cliente puede ver una lista de sus tickets.
+
+### 7.8. Listado de Tickets de Soporte (Admin)
+*   `[ ]` Crear `Admin\SupportTicketController.php`:
+    ```bash
+    php artisan make:controller Admin/SupportTicketController --resource --model=SupportTicket
+    ```
+*   `[ ]` Definir rutas resource para `support-tickets` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`:
+    *   Listar todos los tickets, paginados.
+    *   Filtros (departamento, estado, prioridad, cliente, agente asignado).
+    *   Vista `Admin/SupportTickets/Index.vue`.
+*   `[ ]` (Opcional) Crear `SupportTicketPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Administradores pueden ver todos los tickets.
+
+---
+**¡Gestión Base de Tickets de Soporte (Creación y Listados) Implementada!**
+La siguiente tarea se centrará en las respuestas a los tickets y la visualización detallada de los mismos.
+```
