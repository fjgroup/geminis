
**Tarea 28: Panel de Revendedor (Gestión de Servicios de Clientes)**
Permitir a los revendedores ver y gestionar los servicios de sus propios clientes.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_28.md
@@ -0,0 +1,50 @@
+# Geminis - Plan de Tareas Detallado - Parte 28
+
+Este documento se enfoca en permitir a los revendedores gestionar los servicios de sus clientes.
+
+## Fase 9: Panel de Revendedor - Continuación
+
+### 9.5. Gestión de Servicios de Clientes por Revendedor
+*   **Contexto:** Los revendedores deben poder ver los servicios de sus clientes, su estado, y posiblemente realizar acciones básicas (suspender, terminar, etc., si se les da ese permiso).
+*   `[ ]` Crear `Reseller/ClientServiceController.php`:
+    ```bash
+    php artisan make:controller Reseller/ClientServiceController --resource --model=ClientService
+    ```
+*   `[ ]` Definir rutas resource para `client-services` dentro del grupo `reseller` en `routes/web.php`.
+    *   Podrían ser rutas anidadas bajo clientes: `reseller/clients/{client}/services` o rutas de nivel superior filtradas. Por simplicidad, usar de nivel superior y filtrar.
+    ```php
+    // Dentro del grupo Route::prefix('reseller')...
+    Route::resource('client-services', App\Http\Controllers\Reseller\ClientServiceController::class)->names('clientServices');
+    // Se usa names() para evitar colisión con admin.client-services si existe.
+    ```
+*   `[ ]` Implementar método `index()` en `Reseller\ClientServiceController`:
+    *   Listar `ClientService` donde `reseller_id = Auth::id()`.
+    *   Cargar relaciones (`client`, `product`).
+    *   Paginado y con filtros (por cliente del revendedor, producto, estado del servicio).
+    *   Pasar datos a la vista `Reseller/ClientServices/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/ClientServices/Index.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Tabla para mostrar servicios (Cliente, Producto, Dominio, Próxima Vencimiento, Estado, Precio).
+    *   Enlaces para Ver detalles del servicio.
+*   `[ ]` Implementar método `show(ClientService $clientService)`:
+    *   Verificar que `$clientService->reseller_id === Auth::id()`.
+    *   Mostrar detalles del servicio, incluyendo opciones configurables seleccionadas.
+    *   Vista `Reseller/ClientServices/Show.vue`.
+*   `[ ]` (Opcional Avanzado) Implementar `edit()` y `update()` si los revendedores pueden modificar servicios (ej. cambiar estado, notas).
+    *   Verificar pertenencia del servicio al revendedor.
+    *   Validación adecuada.
+*   `[ ]` (Opcional pero recomendado) Crear `ResellerClientServicePolicy` o adaptar `ClientServicePolicy`:
+    *   `viewAny()`: `Auth::user()->role === 'reseller'`.
+    *   `view()`: `Auth::user()->role === 'reseller' && $clientService->reseller_id === Auth::id()`.
+    *   `update()`: `Auth::user()->role === 'reseller' && $clientService->reseller_id === Auth::id()`.
+    *   Aplicar esta policy en `Reseller\ClientServiceController`.
+*   `[ ]` Añadir enlace "Servicios de Clientes" en `ResellerLayout.vue`.
+*   `[ ]` **Verificación:**
+    *   Un revendedor puede ver una lista de todos los servicios pertenecientes a sus clientes.
+    *   Un revendedor puede ver los detalles de un servicio específico de su cliente.
+    *   Un revendedor NO puede ver servicios de clientes de otros revendedores ni servicios directos de la plataforma.
+
+---
+**¡Gestión de Servicios de Clientes por Revendedor Implementada!**
+El panel de revendedor está tomando forma. Las siguientes tareas se enfocarán en el panel del cliente final.
+```
