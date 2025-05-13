
**Tarea 27: Panel de Revendedor (Gestión de Clientes)**
CRUD para que los revendedores gestionen sus propios clientes.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_27.md
@@ -0,0 +1,57 @@
+# Geminis - Plan de Tareas Detallado - Parte 27
+
+Este documento se enfoca en permitir a los revendedores gestionar sus propios clientes.
+
+## Fase 9: Panel de Revendedor - Continuación
+
+### 9.4. Gestión de Clientes por Revendedor (CRUD)
+*   **Contexto:** Los revendedores necesitan crear, ver, editar y (posiblemente) suspender/eliminar a sus propios clientes.
+*   `[ ]` Crear `Reseller/ClientController.php`:
+    ```bash
+    php artisan make:controller Reseller/ClientController --resource --model=User 
+    // El modelo es User, pero la lógica filtrará por reseller_id
+    ```
+*   `[ ]` Definir rutas resource para `clients` dentro del grupo `reseller` en `routes/web.php`:
+    ```php
+    // Dentro del grupo Route::prefix('reseller')...
+    Route::resource('clients', App\Http\Controllers\Reseller\ClientController::class);
+    ```
+*   `[ ]` Implementar método `index()` en `Reseller\ClientController`:
+    *   Listar usuarios (`User`) donde `role = 'client'` Y `reseller_id = Auth::id()`.
+    *   Paginado y con filtros (nombre, email, estado del cliente).
+    *   Pasar datos a la vista `Reseller/Clients/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/Clients/Index.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Tabla para mostrar clientes del revendedor (Nombre, Email, Empresa, Estado, Fecha de Registro).
+    *   Enlaces para Crear, Editar, Ver (opcional), Eliminar/Suspender.
+*   `[ ]` Implementar métodos `create()` y `store()`:
+    *   Vista `Reseller/Clients/Create.vue`. Formulario similar al de Admin para crear usuarios, pero `role` será 'client' por defecto y `reseller_id` se asignará automáticamente al ID del revendedor logueado.
+    *   Validación (usar un FormRequest `StoreResellerClientRequest` o adaptar `StoreUserRequest`).
+    *   En `store()`, asegurar que `reseller_id` se establezca a `Auth::id()`.
+*   `[ ]` Implementar métodos `edit(User $client)` y `update(Request $request, User $client)`:
+    *   **Importante:** En `edit` y `update`, verificar que `$client->reseller_id === Auth::id()`. Si no, abortar con 403/404. Esto se puede hacer con una Policy o directamente en el controlador.
+    *   Vista `Reseller/Clients/Edit.vue`. Formulario similar al de Admin para editar usuarios.
+    *   Validación (usar `UpdateResellerClientRequest` o adaptar `UpdateUserRequest`).
+*   `[ ]` Implementar método `destroy(User $client)`:
+    *   Verificar que `$client->reseller_id === Auth::id()`.
+    *   Decidir si es borrado lógico (soft delete) o cambio de estado a 'inactive'/'suspended'.
+*   `[ ]` Crear FormRequests: `StoreResellerClientRequest` y `UpdateResellerClientRequest`.
+    *   En `authorize()`, verificar que `Auth::user()->role === 'reseller'`.
+    *   En `UpdateResellerClientRequest@authorize()`, también verificar que el cliente que se intenta editar pertenezca al revendedor autenticado.
+*   `[ ]` (Opcional pero recomendado) Crear `ResellerClientPolicy` o usar `UserPolicy` con lógica adaptada:
+    *   `viewAny()`: `Auth::user()->role === 'reseller'`.
+    *   `view()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
+    *   `create()`: `Auth::user()->role === 'reseller'`.
+    *   `update()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
+    *   `delete()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
+    *   Aplicar esta policy en `Reseller\ClientController`.
+*   `[ ]` Añadir enlace "Mis Clientes" en `ResellerLayout.vue`.
+*   `[ ]` **Verificación:** Un revendedor puede gestionar sus clientes. No puede ver ni gestionar clientes de otros revendedores o clientes directos de la plataforma.
+
+---
+**¡Gestión de Clientes por Revendedor Implementada!**
+El siguiente paso es permitir a los revendedores ver y gestionar los servicios de sus clientes.
+```
