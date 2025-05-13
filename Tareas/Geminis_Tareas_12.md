
**Tarea 12: Lógica de Estados de `ClientService` y Panel de Cliente (Inicio)**
Se enfocará en la lógica de los diferentes estados de un servicio y cómo el cliente comienza a ver sus servicios.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_12.md
@@ -0,0 +1,66 @@
+# Geminis - Plan de Tareas Detallado - Parte 12
+
+Este documento se enfoca en la lógica de estados para los `ClientService` y en comenzar la visualización de estos servicios en el panel del cliente.
+
+## Fase 5: Gestión de Clientes y Servicios - Continuación
+
+### 5.4. Lógica de Estados para `ClientService`
+*   **Contexto:** Los servicios pasan por diferentes estados (`pending`, `active`, `suspended`, `terminated`, `cancelled`, `fraud`). Necesitamos definir cómo y cuándo cambian estos estados.
+*   `[ ]` En `app/Models/ClientService.php`, añadir métodos para cambiar de estado (ej. `activate()`, `suspend()`, `terminate()`).
+    *   Estos métodos podrían también disparar eventos (ej. `ServiceActivated`, `ServiceSuspended`) para futuras automatizaciones (aprovisionamiento, emails).
+    ```php
+    // Ejemplo en ClientService.php
+    public function activate() {
+        $this->status = 'active';
+        // Podría ajustar next_due_date si es la primera activación
+        $this->save();
+        // event(new ServiceActivated($this));
+    }
+    // ... otros métodos de cambio de estado
+    ```
+*   `[ ]` En `Admin/ClientServices/Edit.vue`, añadir botones o acciones para cambiar el estado del servicio manualmente por un administrador.
+*   `[ ]` Actualizar `AdminClientServiceController@update` para manejar cambios de estado si se envían desde el formulario de edición, o crear métodos específicos para acciones de estado.
+*   `[ ]` **Verificación:** Un administrador puede cambiar el estado de un servicio.
+
+### 5.5. Migración Tabla `client_service_configurable_options`
+*   **Contexto:** Tabla pivote para registrar qué opciones configurables específicas (y con qué precio de opción) ha seleccionado un cliente para un servicio particular.
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_client_service_configurable_options_table
+    ```
+*   `[ ]` Modificar el método `up()` según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('client_service_configurable_options', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('client_service_id')->constrained('client_services')->onDelete('cascade');
+        $table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
+        // Almacenamos el precio de la opción en el momento de la contratación/renovación
+        // Esto podría venir de configurable_option_pricing o ser un precio ad-hoc si la estructura de precios de opciones cambia.
+        // Por ahora, asumimos que se selecciona una 'configurable_option_pricing_id' si existe.
+        $table->foreignId('configurable_option_pricing_id')->nullable()->constrained('configurable_option_pricing')->onDelete('set null');
+        $table->decimal('price_override', 10, 2)->nullable()->comment('Precio de la opción si se anula el de configurable_option_pricing');
+        $table->integer('quantity')->default(1); // Para opciones que pueden tener cantidad (ej. licencias adicionales)
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla existe.
+
+### 5.6. Panel de Cliente: Listado de Servicios (Básico)
+*   **Contexto:** Los clientes necesitan ver los servicios que han contratado.
+*   `[ ]` Crear `Client/DashboardController.php` o `Client/ServiceController.php` si no existen.
+*   `[ ]` En el controlador del cliente, método `index` o `services`, obtener los `client_services` del usuario autenticado.
+    *   Filtrar por `client_id = auth()->id()`.
+    *   Cargar relaciones necesarias (producto, precios).
+*   `[ ]` Crear vista `resources/js/Pages/Client/Services/Index.vue`.
+    *   Mostrar una tabla/lista de los servicios del cliente (Nombre del producto, Dominio, Próxima Fecha de Vencimiento, Estado, Precio).
+*   `[ ]` Definir rutas en `routes/web.php` para el panel de cliente (ej. `/client/services`).
+    *   Asegurar middleware de autenticación y que el rol sea 'client' (o que `EnsureUserIsClient` exista y funcione).
+*   `[ ]` Añadir enlace en `ClientLayout.vue` (si existe) o en `AppLayout.vue` (si es el layout general para usuarios autenticados).
+*   `[ ]` **Verificación:** Un cliente logueado puede ver una lista de sus servicios.
+
+---
+**¡Lógica de Estados de Servicio y Listado Básico en Panel de Cliente Implementados!**
+Los siguientes pasos se enfocarán en el proceso de órdenes y cómo estas generan servicios.
+```
