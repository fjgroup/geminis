
**Tarea 44: Módulos de Aprovisionamiento (Implementación cPanel - Crear Cuenta)**
Comenzar la implementación real de un módulo de aprovisionamiento, como cPanel, para la creación de cuentas.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_44.md
@@ -0,0 +1,58 @@
+# Geminis - Plan de Tareas Detallado - Parte 44
+
+Este documento se enfoca en la implementación del método `createAccount` para un módulo de aprovisionamiento específico, como cPanel.
+
+## Fase 11: Automatización y Módulos Externos - Implementación Módulo cPanel
+
+### 11.4. Implementación de `CpanelModule->createAccount()`
+*   **Contexto:** Conectar con la API de cPanel/WHM para crear una nueva cuenta de hosting cuando un servicio se activa.
+*   `[ ]` **Configuración del Servidor en Geminis:**
+    *   Asegurar que la tabla `servers` tenga los campos necesarios para cPanel: `hostname_or_ip`, `api_username`, `api_password_or_key_encrypted` (para token API de WHM), `api_port` (2087 por defecto), `api_use_ssl`.
+*   `[ ]` **Librería Cliente cPanel API:**
+    *   Investigar e instalar una librería PHP para interactuar con la API de WHM (ej. `CpanelWhm/cpanel-php` o una más moderna si existe, o usar Guzzle HTTP directamente).
+    *   `composer require GuzzleHttp/Guzzle` (si se usa directamente).
+*   `[ ]` **En `app/Modules/Provisioning/CpanelModule.php`:**
+    *   Añadir constructor que reciba los datos del servidor (`Server $serverModel`).
+    *   Implementar el método `createAccount(ClientService $service, User $client, array $options = []): array`:
+        *   Obtener los datos del servidor desde `$this->serverModel`.
+        *   Obtener datos del producto (`$service->product`) para determinar el plan de cPanel (podría estar en `products.module_specific_config` como un JSON).
+        *   Datos necesarios para `createacct` API de WHM: `username`, `domain`, `password`, `email`, `plan`.
+        *   Generar un nombre de usuario y contraseña seguros si no se proporcionan en `$options`.
+        *   Construir la URL de la API de WHM (ej. `https://{hostname}:2087/json-api/createacct?...`).
+        *   Realizar la llamada a la API usando Guzzle o la librería cliente:
+            *   Autenticación: `Authorization: WHM username:apitoken` o `Authorization: Basic base64(user:pass)`.
+        *   Manejar la respuesta de la API:
+            *   Si éxito:
+                *   Actualizar `client_services` con `username`, `password_encrypted` (encriptar la contraseña generada), `server_id`, `domain_name`.
+                *   Retornar `['success' => true, 'message' => 'Cuenta creada', 'data' => ['username' => ..., 'ip' => ...]]`.
+            *   Si error:
+                *   Loguear el error.
+                *   Retornar `['success' => false, 'message' => 'Error de cPanel: ' . $errorMessage]`.
+*   `[ ]` **Verificación (Manual/Tinker):**
+    *   Configurar un servidor cPanel de prueba en la tabla `servers`.
+    *   Crear un `ClientService` de prueba.
+    *   Desde Tinker, instanciar `CpanelModule` con el servidor y llamar a `createAccount()`.
+    *   Verificar que la cuenta se cree en cPanel/WHM y que `client_services` se actualice.
+
+### 11.5. Integración de `createAccount` en el Flujo de Activación de Servicio
+*   **Contexto:** Llamar al aprovisionamiento cuando un servicio se activa (ej. después de pagar una orden).
+*   `[ ]` Modificar el lugar donde los servicios se activan (ej. `InvoiceService` después de un pago exitoso, o en `AdminClientServiceController` si es activación manual):
+    *   Obtener el `ClientService` y el `Product` asociado.
+    *   Si `product.module_name` es 'cpanel':
+        *   Obtener el `Server` asignado al producto/servicio (esto necesita lógica de asignación de servidor, ej. de un `server_groups`).
+        *   Si no hay servidor asignado, loguear error y marcar el servicio para aprovisionamiento manual.
+        *   Instanciar `ProvisioningService` y obtener el módulo: `$module = $provisioningService->getModule('cpanel', $server);`.
+        *   Llamar a `$module->createAccount($clientService, $clientService->client)`.
+        *   Manejar el resultado (actualizar estado del servicio, notificar al cliente/admin).
+*   `[ ]` **Verificación:** Al activar un servicio de tipo cPanel, se intenta crear la cuenta automáticamente.
+
+---
+**¡Creación de Cuentas cPanel (Base) Implementada!**
+Este es un paso importante hacia la automatización. Las siguientes tareas pueden incluir suspender/terminar cuentas.
+```
