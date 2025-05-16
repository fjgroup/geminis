Geminis_Tareas_40.md

**Tarea 43: Panel de Cliente (Proceso de Pago de Facturas)**
Permitir a los clientes seleccionar una factura pendiente y pagarla.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_43.md
@@ -0,0 +1,52 @@
+# Geminis - Plan de Tareas Detallado - Parte 43
+
+Este documento se enfoca en permitir a los clientes pagar sus facturas pendientes.
+
+## Fase 10: Panel de Cliente - Gestión Financiera (Continuación)
+
+### 10.8. Proceso de Pago de Facturas por el Cliente
+*   **Contexto:** Los clientes deben poder seleccionar una factura 'unpaid' y realizar el pago.
+*   `[ ]` **Vista `Client/Invoices/Show.vue` (Modificación):**
+    *   Si la factura está 'unpaid', mostrar un botón "Pagar Factura".
+    *   Al hacer clic, podría llevar a una nueva página `Client/Invoices/Pay.vue` o mostrar un modal.
+*   `[ ]` **Vista `Client/Invoices/Pay.vue` (o Modal):**
+    *   Mostrar resumen de la factura (total a pagar).
+    *   Permitir al cliente seleccionar un método de pago guardado (de Tarea 42).
+    *   Opción para añadir un nuevo método de pago (si no tiene o quiere usar otro).
+    *   Botón "Confirmar Pago".
+*   `[ ]` **Backend (StripeService o PaymentService):**
+    *   Método `processInvoicePayment(Invoice $invoice, User $client, string $paymentMethodId)`:
+        *   Crear un PaymentIntent en Stripe por el monto de la factura, usando el `customer_id` del cliente y el `paymentMethodId`.
+        *   Confirmar el PaymentIntent.
+        *   Si el pago es exitoso:
+            *   Registrar la transacción en la tabla `transactions`.
+            *   Actualizar `invoices.status` a 'paid' y `invoices.paid_date`.
+            *   Si la factura está asociada a una orden que activa servicios, disparar lógica de activación de servicios.
+            *   Enviar email de confirmación de pago.
+        *   Si falla, retornar error.
+*   `[ ]` **Controlador Cliente (`Client/InvoiceController.php` - Modificación o Nuevo):**
+    *   Método `showPaymentForm(Invoice $invoice)`:
+        *   Verificar que la factura pertenezca al cliente y esté 'unpaid'.
+        *   Cargar métodos de pago guardados del cliente.
+        *   Pasar datos a `Client/Invoices/Pay.vue`.
+    *   Método `processPayment(Request $request, Invoice $invoice)`:
+        *   Validar `payment_method_id`.
+        *   Llamar a `StripeService->processInvoicePayment()`.
+        *   Redirigir con mensaje de éxito/error.
+*   `[ ]` **Verificación:**
+    *   Un cliente puede seleccionar una factura no pagada.
+    *   Puede elegir un método de pago existente o añadir uno nuevo para pagar.
+    *   Tras un pago exitoso, la factura se marca como pagada, se registra la transacción y se notifica al cliente.
+    *   Los servicios asociados se activan (si aplica).
+
+### 10.9. (Opcional) Pago con PayPal
+*   **Contexto:** Permitir pagar facturas usando PayPal.
+*   `[ ]` En `Client/Invoices/Pay.vue`, añadir opción "Pagar con PayPal".
+*   `[ ]` Al seleccionar, redirigir a PayPal para aprobar el pago (usando `PayPalService` para crear la orden de PayPal).
+*   `[ ]` Manejar el retorno de PayPal (éxito/cancelación) y llamar a `PayPalService` para capturar el pago.
+*   `[ ]` Actualizar factura y transacción de forma similar a Stripe.
+*   `[ ]` **Verificación:** El cliente puede pagar una factura usando PayPal.
+
+---
+**¡Proceso de Pago de Facturas por Cliente Implementado!**
+Los clientes ahora pueden liquidar sus facturas pendientes de forma autónoma.
+```
