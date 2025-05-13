
**Tarea 45: Módulos de Aprovisionamiento (Implementación cPanel - Suspender/Reactivar/Terminar)**
Implementar las acciones de suspender, reactivar y terminar cuentas en el módulo cPanel.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_45.md
@@ -0,0 +1,48 @@
+# Geminis - Plan de Tareas Detallado - Parte 45
+
+Este documento se enfoca en implementar las funciones de suspensión, reactivación y terminación para el módulo cPanel.
+
+## Fase 11: Automatización y Módulos Externos - Implementación Módulo cPanel (Continuación)
+
+### 11.6. Implementación de `CpanelModule->suspendAccount()`
+*   **Contexto:** Suspender una cuenta de hosting en cPanel.
+*   `[ ]` En `app/Modules/Provisioning/CpanelModule.php`:
+    *   Implementar `suspendAccount(ClientService $service): bool`:
+        *   Usar la API de WHM `suspendacct` (parámetros: `user`, `reason` (opcional)).
+        *   Obtener `username` de `$service->username`.
+        *   Realizar la llamada a la API.
+        *   Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
+*   `[ ]` **Verificación (Manual/Tinker):** Probar la suspensión de una cuenta existente.
+
+### 11.7. Implementación de `CpanelModule->unsuspendAccount()`
+*   **Contexto:** Reactivar una cuenta de hosting suspendida en cPanel.
+*   `[ ]` En `app/Modules/Provisioning/CpanelModule.php`:
+    *   Implementar `unsuspendAccount(ClientService $service): bool`:
+        *   Usar la API de WHM `unsuspendacct` (parámetro: `user`).
+        *   Realizar la llamada a la API.
+        *   Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
+*   `[ ]` **Verificación (Manual/Tinker):** Probar la reactivación de una cuenta suspendida.
+
+### 11.8. Implementación de `CpanelModule->terminateAccount()`
+*   **Contexto:** Eliminar permanentemente una cuenta de hosting en cPanel.
+*   `[ ]` En `app/Modules/Provisioning/CpanelModule.php`:
+    *   Implementar `terminateAccount(ClientService $service): bool`:
+        *   Usar la API de WHM `removeacct` (parámetro: `user`, opcional `keepdns`).
+        *   Realizar la llamada a la API.
+        *   Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
+*   `[ ]` **Verificación (Manual/Tinker):** Probar la terminación de una cuenta (¡con cuidado en entornos de prueba!).
+
+### 11.9. Integración de Acciones de Módulo en Controladores de Servicio (Admin/Reseller)
+*   **Contexto:** Permitir a los administradores/revendedores ejecutar estas acciones desde el panel.
+*   `[ ]` En `AdminClientServiceController` (y `ResellerClientServiceController` si aplica):
+    *   Añadir métodos para `suspend(ClientService $service)`, `unsuspend(ClientService $service)`, `terminate(ClientService $service)`.
+    *   En cada método:
+        *   Autorizar la acción (ej. con `ClientServicePolicy`).
+        *   Obtener el módulo de aprovisionamiento (`$provisioningService->getModule(...)`).
+        *   Llamar al método correspondiente del módulo (`$module->suspendAccount($service)`).
+        *   Si la acción del módulo es exitosa, actualizar `client_services.status`.
+        *   Redirigir con mensaje.
+*   `[ ]` Añadir botones/acciones en `Admin/ClientServices/Show.vue` (o `Index.vue`) y vistas de revendedor para invocar estos métodos.
+*   `[ ]` **Verificación:** Un admin/revendedor puede suspender, reactivar y terminar servicios de cPanel desde la interfaz. El estado del servicio en Geminis se actualiza.
+
+---
+**¡Funciones de Suspensión/Reactivación/Terminación cPanel Implementadas!**
+La gestión del ciclo de vida de los servicios de cPanel está más completa.
+```
