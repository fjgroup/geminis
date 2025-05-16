
**Tarea 33: Notificaciones por Email (Integración)**
Integrar el envío de emails usando las plantillas creadas para eventos clave del sistema.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_33.md
@@ -0,0 +1,70 @@
+# Geminis - Plan de Tareas Detallado - Parte 33
+
+Este documento se enfoca en integrar el sistema de plantillas de correo para enviar notificaciones en eventos clave.
+
+## Fase 12: Notificaciones y Comunicaciones
+
+### 12.1. Servicio de Envío de Correos
+*   **Contexto:** Centralizar la lógica de envío de correos usando las plantillas.
+*   `[ ]` Crear `app/Services/EmailService.php`.
+*   `[ ]` En `EmailService.php`, método `sendEmail(User $recipient, string $templateSlug, array $data = [], ?User $resellerContext = null)`:
+    *   Buscar la plantilla de correo:
+        *   Primero, buscar una plantilla específica del revendedor (si `$resellerContext` y `is_customizable_by_reseller` lo permiten).
+        *   Si no se encuentra, buscar la plantilla global con ese `slug`.
+    *   Si no se encuentra la plantilla, loguear un error y no enviar.
+    *   Parsear el `subject` y `body_html` de la plantilla, reemplazando placeholders (ej. `{{ client_name }}`, `{{ service_domain }}`) con los valores de `$data`.
+    *   Usar `Mail::send()` o crear un Mailable dinámico para enviar el correo.
+    *   Considerar el idioma del destinatario (`$recipient->language_code`) al buscar la plantilla.
+*   `[ ]` **Verificación:** El servicio puede (conceptualmente) encontrar una plantilla y prepararla.
+
+### 12.2. Configuración de Correo en `.env`
+*   `[ ]` Asegurar que las variables de entorno para el correo estén configuradas en `.env` (ej. `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`).
+*   `[ ]` Usar un servicio como Mailtrap.io para pruebas de desarrollo.
+*   `[ ]` **Verificación:** Laravel puede enviar un correo de prueba simple (ej. desde Tinker).
+
+### 12.3. Integración de Notificaciones por Email
+*   **Contexto:** Enviar correos en momentos clave.
+*   **Ejemplos de Eventos y Plantillas (crear plantillas si no existen):**
+    *   **Registro de Nuevo Cliente:**
+        *   Evento: Después de que un cliente se registra (Breeze ya envía uno, se puede personalizar o reemplazar).
+        *   Plantilla: `auth.welcome` o `client.registration`.
+        *   Datos: Nombre del cliente, enlace al login.
+        *   `[ ]` Integrar en `RegisteredUserController@store` o donde se creen usuarios.
+    *   **Creación de Nueva Orden:**
+        *   Evento: Después de `Client\OrderController@placeOrder`.
+        *   Plantilla: `order.confirmation`.
+        *   Datos: Número de orden, detalles de la orden, total.
+        *   `[ ]` Integrar.
+    *   **Generación de Nueva Factura:**
+        *   Evento: Después de `generateInvoiceFromOrder` o `GenerateRenewalInvoicesJob`.
+        *   Plantilla: `invoice.new`.
+        *   Datos: Número de factura, monto, fecha de vencimiento, enlace para ver/pagar.
+        *   `[ ]` Integrar.
+    *   **Servicio Activado:**
+        *   Evento: Cuando un `ClientService` cambia a estado 'active'.
+        *   Plantilla: `service.activated` (puede ser específica por `product.type`).
+        *   Datos: Nombre del producto, dominio, detalles de acceso (si aplica y es seguro enviarlos).
+        *   `[ ]` Integrar donde se activen servicios (manual admin, o después de pago de orden).
+    *   **Nuevo Ticket de Soporte Creado (para cliente y admin/staff):**
+        *   Evento: Después de `Client\SupportTicketController@store` o `Admin\SupportTicketController@store`.
+        *   Plantilla Cliente: `support.ticket.opened.client`.
+        *   Plantilla Staff: `support.ticket.opened.staff`.
+        *   Datos: Número de ticket, asunto, enlace al ticket.
+        *   `[ ]` Integrar.
+    *   **Nueva Respuesta en Ticket de Soporte (para cliente o staff):**
+        *   Evento: Después de `addReply` en controladores de tickets.
+        *   Plantilla Cliente: `support.ticket.reply.client` (si respondió staff).
+        *   Plantilla Staff: `support.ticket.reply.staff` (si respondió cliente).
+        *   Datos: Número de ticket, enlace al ticket, contenido de la respuesta (o un extracto).
+        *   `[ ]` Integrar.
+*   `[ ]` Para cada integración:
+    *   Identificar el lugar en el código donde ocurre el evento.
+    *   Llamar a `EmailService->sendEmail()` con los datos correctos.
+    *   Considerar el uso de Jobs para enviar correos y no bloquear la respuesta HTTP (`SendEmailJob` que internamente use `EmailService`).
+*   `[ ]` **Verificación:** Los correos se envían correctamente para cada evento y llegan a Mailtrap (o al destinatario real). Las variables en las plantillas se reemplazan correctamente.
+
+---
+**¡Notificaciones por Email Integradas!**
+El sistema ahora comunica eventos importantes a los usuarios.
+```
