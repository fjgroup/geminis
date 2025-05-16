
**Tarea 38: API (Básica para Cliente/Revendedor)**
Definir y desarrollar algunos endpoints API básicos si se prevé la necesidad de una app móvil o integraciones externas.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_38.md
@@ -0,0 +1,50 @@
+# Geminis - Plan de Tareas Detallado - Parte 38
+
+Este documento se enfoca en la creación de una API básica para clientes y revendedores, si fuera necesaria para futuras aplicaciones móviles o integraciones.
+
+## Fase 13: API y Servicios Externos
+
+### 13.1. Autenticación API (Sanctum)
+*   **Contexto:** Si se va a exponer una API, se necesita un método de autenticación seguro. Laravel Sanctum es ideal para tokens API.
+*   `[ ]` Instalar Sanctum (si no se hizo antes para la API de Vue en el libro de ejemplo):
+    ```bash
+    composer require laravel/sanctum
+    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
+    php artisan migrate 
+    ```
+*   `[ ]` Añadir `Laravel\Sanctum\HasApiTokens` trait al modelo `User.php`.
+*   `[ ]` Configurar `auth.guards.api.driver` a `sanctum` en `config/auth.php` si se usará autenticación por token para la API.
+*   `[ ]` Crear rutas para generar tokens API (ej. en `Client/ProfileController` o `Reseller/ProfileController`):
+    *   `POST /api/tokens/create` - Genera un nuevo token para el usuario autenticado.
+    *   `GET /api/tokens` - Lista los tokens del usuario.
+    *   `DELETE /api/tokens/{tokenId}` - Revoca un token.
+*   `[ ]` **Verificación:** Los usuarios pueden generar y gestionar tokens API.
+
+### 13.2. Endpoints API para Clientes
+*   **Contexto:** Exponer funcionalidades del panel de cliente a través de una API.
+*   `[ ]` Crear controladores en `app/Http/Controllers/Api/Client/`.
+*   `[ ]` Ejemplos de Endpoints (protegidos con `auth:sanctum` y middleware `client`):
+    *   `GET /api/client/services` - Listar servicios del cliente.
+    *   `GET /api/client/services/{service}` - Ver detalle de un servicio.
+    *   `GET /api/client/invoices` - Listar facturas del cliente.
+    *   `GET /api/client/invoices/{invoice}` - Ver detalle de una factura.
+    *   `GET /api/client/tickets` - Listar tickets de soporte del cliente.
+    *   `POST /api/client/tickets` - Crear nuevo ticket.
+    *   `POST /api/client/tickets/{ticket}/reply` - Añadir respuesta a ticket.
+*   `[ ]` Usar Resources API de Laravel para formatear las respuestas JSON.
+*   `[ ]` **Verificación:** Los endpoints funcionan y devuelven los datos correctos para el cliente autenticado.
+
+### 13.3. Endpoints API para Revendedores
+*   **Contexto:** Exponer funcionalidades del panel de revendedor.
+*   `[ ]` Crear controladores en `app/Http/Controllers/Api/Reseller/`.
+*   `[ ]` Ejemplos de Endpoints (protegidos con `auth:sanctum` y middleware `reseller`):
+    *   `GET /api/reseller/clients` - Listar clientes del revendedor.
+    *   `POST /api/reseller/clients` - Crear un nuevo cliente para el revendedor.
+    *   `GET /api/reseller/client-services` - Listar servicios de los clientes del revendedor.
+*   `[ ]` Usar Resources API.
+*   `[ ]` **Verificación:** Los endpoints funcionan y devuelven los datos correctos para el revendedor autenticado.
+
+---
+**¡API Básica para Cliente/Revendedor Implementada!**
+Esto sienta las bases para futuras integraciones o aplicaciones móviles. La complejidad de la API puede crecer significativamente según los requisitos.
+```
