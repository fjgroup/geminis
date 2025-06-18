
**Tarea 42: Panel de Cliente (Gestión de Métodos de Pago)**
Permitir a los clientes añadir, ver y eliminar sus métodos de pago (ej. tarjetas con Stripe).

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_42.md
@@ -0,0 +1,48 @@
+# Geminis - Plan de Tareas Detallado - Parte 42
+
+Este documento se enfoca en permitir a los clientes gestionar sus métodos de pago.
+
+## Fase 10: Panel de Cliente - Gestión Financiera
+
+### 10.6. Gestión de Métodos de Pago del Cliente (Stripe)
+*   **Contexto:** Los clientes deben poder guardar y gestionar sus tarjetas de crédito para pagos futuros (usando Stripe SetupIntents y Customers).
+*   `[ ]` **Backend (StripeService):**
+    *   Método `createSetupIntent(User $client)`: Crea un SetupIntent para que el cliente añada una nueva tarjeta.
+    *   Método `listPaymentMethods(User $client)`: Lista los métodos de pago guardados del cliente en Stripe.
+    *   Método `detachPaymentMethod(User $client, string $paymentMethodId)`: Elimina un método de pago de Stripe.
+    *   Método `setDefaultPaymentMethod(User $client, string $paymentMethodId)`: Establece un método de pago como predeterminado.
+*   `[ ]` **Controlador Cliente (`Client/PaymentMethodController.php`):**
+    ```bash
+    php artisan make:controller Client/PaymentMethodController
+    ```
+    *   `index()`: Llama a `StripeService->listPaymentMethods()` y pasa los datos a la vista.
+    *   `create()`: Llama a `StripeService->createSetupIntent()` y pasa el `client_secret` del SetupIntent a la vista.
+    *   `store(Request $request)`: (Este se maneja principalmente en el frontend con Stripe.js, pero el backend podría confirmar el SetupIntent si es necesario o manejar el webhook `setup_intent.succeeded`).
+    *   `destroy(string $paymentMethodId)`: Llama a `StripeService->detachPaymentMethod()`.
+    *   `setDefault(Request $request, string $paymentMethodId)`: Llama a `StripeService->setDefaultPaymentMethod()`.
+*   `[ ]` **Vistas Cliente:**
+    *   `Client/PaymentMethods/Index.vue`:
+        *   Lista los métodos de pago guardados (últimos 4 dígitos, fecha de expiración, tipo).
+        *   Botón para "Añadir Nuevo Método de Pago" (lleva a `create`).
+        *   Botones para "Eliminar" y "Marcar como Predeterminado".
+    *   `Client/PaymentMethods/Create.vue`:
+        *   Integra Stripe Elements (Card Element) para la entrada segura de los datos de la tarjeta.
+        *   Usa el `client_secret` del SetupIntent para confirmar la configuración de la tarjeta con Stripe.js.
+        *   Al éxito, redirigir a `index` o mostrar mensaje.
+*   `[ ]` Definir rutas en `routes/web.php` para `client.payment-methods.*`.
+*   `[ ]` Añadir enlace "Métodos de Pago" en `ClientLayout.vue`.
+*   `[ ]` **Verificación:**
+    *   Un cliente puede añadir una nueva tarjeta de forma segura.
+    *   Un cliente puede ver sus tarjetas guardadas.
+    *   Un cliente puede eliminar una tarjeta.
+    *   Un cliente puede establecer una tarjeta como predeterminada.
+
+### 10.7. (Opcional) Gestión de Métodos de Pago (PayPal)
+*   **Contexto:** Si se permite guardar acuerdos de facturación de PayPal.
+*   `[ ]` Investigar y planificar la lógica para acuerdos de facturación de PayPal (Billing Agreements / Subscriptions API de PayPal).
+*   `[ ]` Implementar lógica similar a la de Stripe si se decide soportar.
+
+---
+**¡Gestión de Métodos de Pago por Cliente Implementada (Stripe)!**
+Los clientes ahora pueden gestionar sus instrumentos de pago para facilitar futuras transacciones.
+```
