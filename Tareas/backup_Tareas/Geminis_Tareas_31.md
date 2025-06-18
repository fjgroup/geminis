
**Tarea 31: Módulos de Aprovisionamiento (Platzhalter)**
Definir la estructura base (interfaces, servicios) para la futura integración de módulos de aprovisionamiento.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_31.md
@@ -0,0 +1,48 @@
+# Geminis - Plan de Tareas Detallado - Parte 31
+
+Este documento se enfoca en sentar las bases para la integración de módulos de aprovisionamiento de servicios (ej. cPanel, Plesk).
+
+## Fase 11: Automatización y Módulos Externos
+
+### 11.1. Definición de Interfaces para Módulos de Aprovisionamiento
+*   **Contexto:** Para permitir la integración de diferentes sistemas de aprovisionamiento (cPanel, Plesk, etc.), necesitamos una interfaz común.
+*   `[ ]` Crear directorio `app/Modules/Provisioning/Contracts/`.
+*   `[ ]` Crear interfaz `ProvisioningModuleInterface.php` en ese directorio:
+    ```php
+    // app/Modules/Provisioning/Contracts/ProvisioningModuleInterface.php
+    namespace App\Modules\Provisioning\Contracts;
+
+    use App\Models\ClientService;
+    use App\Models\User; // Para datos del cliente
+
+    interface ProvisioningModuleInterface
+    {
+        public function createAccount(ClientService $service, User $client, array $options = []): array; // Retorna array con datos del resultado o error
+        public function suspendAccount(ClientService $service): bool;
+        public function unsuspendAccount(ClientService $service): bool;
+        public function terminateAccount(ClientService $service): bool;
+        public function changePassword(ClientService $service, string $newPassword): bool;
+        public function getUsage(ClientService $service): array; // Ej: uso de disco, Bw
+        // Otros métodos relevantes: changePackage, loginToPanel, etc.
+    }
+    ```
+*   `[ ]` **Verificación:** La interfaz está definida.
+
+### 11.2. Creación de un Servicio de Aprovisionamiento (Manager)
+*   **Contexto:** Un servicio que actúe como "manager" o "factory" para obtener la instancia correcta del módulo de aprovisionamiento basado en `products.module_name`.
+*   `[ ]` Crear `app/Services/ProvisioningService.php`.
+*   `[ ]` En `ProvisioningService.php`, método `getModule(string $moduleName): ?ProvisioningModuleInterface`:
+    *   Este método usará un `match` o `if/else` para instanciar y retornar el módulo correcto (ej. `new CpanelModule()`).
+    *   Por ahora, puede retornar `null` o lanzar una excepción si el módulo no existe.
+    ```php
+    // Ejemplo en ProvisioningService.php
+    // public function getModule(string $moduleName): ?ProvisioningModuleInterface
+    // {
+    //     return match (strtolower($moduleName)) {
+    //         'cpanel' => new \App\Modules\Provisioning\CpanelModule(), // Crear esta clase después
+    //         'plesk' => new \App\Modules\Provisioning\PleskModule(),   // Crear esta clase después
+    //         default => null,
+    //     };
+    // }
+    ```
+*   `[ ]` (Opcional) Registrar `ProvisioningService` en el contenedor de servicios de Laravel si se va a inyectar.
+*   `[ ]` **Verificación:** El servicio base está creado.
+
+### 11.3. Clases Platzhalter para Módulos Específicos (Ej. CpanelModule)
+*   **Contexto:** Crear las clases vacías que implementarán la interfaz, para tener la estructura.
+*   `[ ]` Crear directorio `app/Modules/Provisioning/`.
+*   `[ ]` Crear `app/Modules/Provisioning/CpanelModule.php` que implemente `ProvisioningModuleInterface`.
+    *   Dejar los métodos de la interfaz vacíos o retornando valores por defecto (ej. `true` o `[]`).
+*   `[ ]` (Opcional) Crear `PleskModule.php` de forma similar.
+*   `[ ]` **Verificación:** Las clases existen y el `ProvisioningService` podría (conceptualmente) instanciarlas.
+
+---
+**¡Estructura Base para Módulos de Aprovisionamiento Definida!**
+La implementación real de la lógica de cada módulo (conexión a APIs, etc.) será una tarea mucho más grande y específica para cada módulo. Esta tarea solo sienta las bases.
+```
