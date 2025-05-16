--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_41.md
@@ -0,0 +1,55 @@
+# Geminis - Plan de Tareas Detallado - Parte 41
+
+Este documento se enfoca en la integración básica de pasarelas de pago.
+
+## Fase 13: API y Servicios Externos - Continuación (Pasarelas de Pago)
+
+### 13.4. Configuración de Pasarelas de Pago (Stripe/PayPal - Lado Servidor)
+*   **Contexto:** Preparar el backend para procesar pagos a través de pasarelas externas.
+*   `[ ]` **Stripe:**
+    *   Instalar el SDK de Stripe para PHP: `composer require stripe/stripe-php`.
+    *   Añadir claves API de Stripe (secreta y publicable) a `config/services.php` y `.env`.
+        ```php
+        // config/services.php
+        'stripe' => [
+            'key' => env('STRIPE_KEY'),
+            'secret' => env('STRIPE_SECRET'),
+            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
+        ],
+        ```
+    *   Crear un `StripeService.php` en `app/Services/` para encapsular la lógica de Stripe (ej. crear PaymentIntents, manejar webhooks).
+*   `[ ]` **PayPal:**
+    *   Instalar el SDK de PayPal para PHP: `composer require paypal/paypal-checkout-sdk`.
+    *   Añadir credenciales API de PayPal (Client ID, Secret) a `config/services.php` y `.env`.
+        ```php
+        // config/services.php
+        'paypal' => [
+            'client_id' => env('PAYPAL_CLIENT_ID'),
+            'secret' => env('PAYPAL_SECRET'),
+            'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' o 'live'
+            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
+        ],
+        ```
+    *   Crear un `PayPalService.php` en `app/Services/` (ej. crear órdenes, capturar pagos, manejar webhooks).
+*   `[ ]` **Verificación:** Las configuraciones y SDKs están instalados. Los servicios pueden ser instanciados.
+
+### 13.5. Controlador de Webhooks Genérico
+*   **Contexto:** Un punto de entrada para manejar notificaciones asíncronas de las pasarelas de pago.
+*   `[ ]` Crear `WebhookController.php` en `app/Http/Controllers/Webhook/`.
+    ```bash
+    php artisan make:controller Webhook/WebhookController
+    ```
+*   `[ ]` Método `handleStripeWebhook(Request $request)`:
+    *   Verificar la firma del webhook de Stripe.
+    *   Procesar el evento (ej. `payment_intent.succeeded`, `invoice.payment_failed`).
+    *   Actualizar estado de factura/orden, registrar transacción.
+*   `[ ]` Método `handlePaypalWebhook(Request $request)`:
+    *   Verificar la firma del webhook de PayPal.
+    *   Procesar el evento (ej. `CHECKOUT.ORDER.APPROVED`, `PAYMENT.SALE.COMPLETED`).
+*   `[ ]` Definir rutas para los webhooks en `routes/web.php` (o `routes/api.php` si es más apropiado). Estas rutas deben estar excluidas de la protección CSRF.
+*   `[ ]` **Verificación:** Las rutas de webhook existen y la lógica base de verificación de firma está planteada.
+
+---
+**¡Integración Base de Pasarelas de Pago (Servidor) Iniciada!**
+Las siguientes tareas se enfocarán en la interacción del cliente con estas pasarelas.
+```
