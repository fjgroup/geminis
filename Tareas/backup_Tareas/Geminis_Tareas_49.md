
**Tarea 49: Sistema de Afiliados (Base - Admin)**
Estructura inicial para un sistema de afiliados, incluyendo la gestión de afiliados y visualización de estadísticas básicas por el admin.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_49.md
@@ -0,0 +1,85 @@
+# Geminis - Plan de Tareas Detallado - Parte 49
+
+Este documento inicia la implementación de un Sistema de Afiliados.
+
+## Fase 16: Sistema de Afiliados
+
+### 16.1. Migración de la Tabla `affiliates`
+*   **Contexto:** Almacena la información de los afiliados. Un afiliado es un usuario (cliente o revendedor).
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_affiliates_table
+    ```
+*   `[ ]` Modificar el método `up()`:
+    ```php
+    Schema::create('affiliates', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // El usuario que es afiliado
+        $table->string('referral_code')->unique();
+        $table->decimal('commission_rate_percentage', 5, 2)->default(10.00); // Porcentaje de comisión
+        $table->unsignedInteger('min_payout_amount')->default(50); // Monto mínimo para solicitar pago
+        $table->boolean('is_active')->default(false);
+        $table->decimal('balance', 10, 2)->default(0.00); // Saldo actual de comisiones
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración.
+*   `[ ]` **Verificación:** La tabla `affiliates` existe.
+
+### 16.2. Modelo `Affiliate`
+*   `[ ]` Crear el modelo: `php artisan make:model Affiliate`
+*   `[ ]` Configurar `$fillable`. Definir relación `user()`.
+
+### 16.3. Migraciones para Tablas de Seguimiento de Afiliados
+*   `[ ]` **`affiliate_clicks`**: Registra clics en enlaces de afiliados.
+    *   Campos: `id`, `affiliate_id` (FK a `affiliates`), `ip_address`, `user_agent`, `referral_url`, `landing_page_url`, `created_at`.
+*   `[ ]` **`affiliate_signups`**: Registra nuevos clientes referidos.
+    *   Campos: `id`, `affiliate_id`, `referred_user_id` (FK a `users`), `order_id` (FK a `orders`, opcional), `commission_earned` (nullable), `commission_paid_date` (nullable), `status` ('pending', 'approved', 'paid', 'rejected'), `created_at`.
+*   `[ ]` **`affiliate_payouts`**: Registra los pagos realizados a los afiliados.
+    *   Campos: `id`, `affiliate_id`, `payout_date`, `amount`, `payment_method_details`, `transaction_reference`, `status` ('pending', 'completed', 'failed'), `created_at`.
+*   `[ ]` Crear modelos para estas tablas: `AffiliateClick`, `AffiliateSignup`, `AffiliatePayout`.
+*   `[ ]` Ejecutar migraciones.
+*   `[ ]` **Verificación:** Las tablas existen.
+
+### 16.4. Gestión de Afiliados (Admin)
+*   **Contexto:** Los administradores deben poder activar/desactivar afiliados, ver sus estadísticas y gestionar pagos.
+*   `[ ]` Crear `Admin\AffiliateController.php`.
+*   `[ ]` Método `index()`:
+    *   Listar usuarios que son afiliados (`Affiliate::with('user')->paginate()`).
+    *   Mostrar estadísticas básicas (clics, referidos, saldo).
+    *   Vista `Admin/Affiliates/Index.vue`.
+*   `[ ]` Método `edit(Affiliate $affiliate)` y `update(Request $request, Affiliate $affiliate)`:
+    *   Permitir al admin activar/desactivar el afiliado, ajustar tasa de comisión, mínimo de pago.
+    *   Vista `Admin/Affiliates/Edit.vue`.
+*   `[ ]` (Opcional) Proceso para que un usuario solicite ser afiliado. Por ahora, el admin los crea/asigna.
+*   `[ ]` Añadir enlace "Afiliados" en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Admin puede ver y editar afiliados.
+
+### 16.5. Lógica de Seguimiento de Clics y Referidos (Básica)
+*   **Contexto:** Registrar cuando alguien usa un enlace de afiliado.
+*   `[ ]` **Middleware de Afiliado (`TrackAffiliateReferralMiddleware`):**
+    *   Si la URL contiene un `?ref=REFERRAL_CODE`:
+        *   Buscar el afiliado por `referral_code`.
+        *   Si existe y está activo, registrar en `affiliate_clicks`.
+        *   Guardar `affiliate_id` en la sesión del visitante por X tiempo (ej. 30-90 días).
+*   `[ ]` Aplicar este middleware a rutas públicas relevantes (ej. página de inicio, páginas de productos).
+*   `[ ]` **Registro de Signup:**
+    *   Al registrar un nuevo usuario (`RegisteredUserController@store` o similar):
+        *   Si hay un `affiliate_id` en la sesión, crear un registro en `affiliate_signups` con `status = 'pending'`.
+*   `[ ]` **Verificación:** Los clics y registros de referidos se rastrean (básicamente).
+
+---
+**¡Sistema de Afiliados (Base Admin y Seguimiento) Implementado!**
+La lógica de cálculo de comisiones, aprobación de referidos y el panel del afiliado son tareas para después.
+```
