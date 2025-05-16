--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_09.md
@@ -0,0 +1,103 @@
+# Geminis - Plan de Tareas Detallado - Parte 09
+
+Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la gestión administrativa (CRUD) de los Grupos de Opciones Configurables y las Opciones Configurables individuales, así como su asignación a productos.
+
+## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos - Continuación
+
+### 4.11. CRUD para `ConfigurableOptionGroup` (Admin)
+*   **Contexto:** Los administradores necesitan gestionar los grupos de opciones configurables (ej. "Sistema Operativo", "Ubicación del Servidor").
+*   `[ ]` Crear controlador `Admin\ConfigurableOptionGroupController`:
+    ```bash
+    php artisan make:controller Admin/ConfigurableOptionGroupController --resource --model=ConfigurableOptionGroup
+    ```
+*   `[ ]` Definir rutas resource para `configurable-option-groups` en `routes/web.php` (dentro del grupo `admin`):
+    ```php
+    Route::resource('configurable-option-groups', AdminConfigurableOptionGroupController::class);
+    ```
+*   `[ ]` Implementar método `index()` en `AdminConfigurableOptionGroupController`:
+    *   Listar grupos, paginados. Permitir filtrar por nombre o si son globales/específicos de producto.
+    *   Pasar datos a la vista `Admin/ConfigurableOptionGroups/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Admin/ConfigurableOptionGroups/Index.vue`:
+    *   Tabla para mostrar grupos (Nombre, Descripción, ¿Producto asociado?, Orden).
+    *   Enlaces para Crear, Editar, Eliminar.
+*   `[ ]` Implementar métodos `create()` y `store()`:
+    *   Vista `Admin/ConfigurableOptionGroups/Create.vue` con formulario (nombre, descripción, product_id (opcional, select con productos), display_order).
+    *   Validación (usar FormRequest `StoreConfigurableOptionGroupRequest`).
+*   `[ ]` Implementar métodos `edit()` y `update()`:
+    *   Vista `Admin/ConfigurableOptionGroups/Edit.vue`.
+    *   Validación (usar FormRequest `UpdateConfigurableOptionGroupRequest`).
+*   `[ ]` Implementar método `destroy()`.
+*   `[ ]` Crear `StoreConfigurableOptionGroupRequest` y `UpdateConfigurableOptionGroupRequest`.
+*   `[ ]` (Opcional) Crear `ConfigurableOptionGroupPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue` para "Grupos de Opciones".
+*   `[ ]` **Verificación:** CRUD completo para grupos de opciones funciona.
+
+### 4.12. CRUD para `ConfigurableOption` (Admin - Anidado o en vista de Grupo)
+*   **Contexto:** Dentro de cada grupo, se deben poder gestionar las opciones individuales (ej. "CentOS", "Ubuntu").
+*   **Decisión:** Se gestionarán desde la vista de edición del `ConfigurableOptionGroup`.
+*   `[ ]` Modificar `Admin/ConfigurableOptionGroups/Edit.vue`:
+    *   Añadir una sección para listar las `configurable_options` del grupo actual (`props.configurable_option_group.options`).
+    *   Formulario (modal o en línea) para añadir/editar opciones (nombre, valor, orden).
+*   `[ ]` Añadir rutas y métodos en `AdminConfigurableOptionGroupController` para gestionar opciones anidadas:
+    *   `storeOption(Request $request, ConfigurableOptionGroup $configurable_option_group)`
+    *   `updateOption(Request $request, ConfigurableOptionGroup $configurable_option_group, ConfigurableOption $option)`
+    *   `destroyOption(ConfigurableOptionGroup $configurable_option_group, ConfigurableOption $option)`
+*   `[ ]` Implementar lógica en estos métodos (validación, creación, actualización, eliminación de `ConfigurableOption`).
+*   `[ ]` **Verificación:** Se pueden añadir, editar y eliminar opciones dentro de un grupo.
+
+### 4.13. Asignación de Grupos de Opciones Configurables a Productos
+*   **Contexto:** Los productos deben poder tener asociados uno o más grupos de opciones configurables.
+*   `[ ]` Modificar `app/Models/Product.php`:
+    *   Definir relación `configurableOptionGroups()` (muchos a muchos con `configurable_option_groups` usando la tabla pivote `product_configurable_option_group`).
+    ```php
+    public function configurableOptionGroups()
+    {
+        return $this->belongsToMany(ConfigurableOptionGroup::class, 'product_configurable_option_group')
+                    ->withTimestamps()
+                    ->withPivot('display_order') // Si quieres acceder al orden de la tabla pivote
+                    ->orderBy('pivot_display_order', 'asc'); // Ordenar por el campo en la tabla pivote
+    }
+    ```
+*   `[ ]` Modificar `app/Models/ConfigurableOptionGroup.php`:
+    *   Definir relación `products()` (muchos a muchos con `products`).
+    ```php
+    public function products()
+    {
+        return $this->belongsToMany(Product::class, 'product_configurable_option_group')
+                    ->withTimestamps()
+                    ->withPivot('display_order');
+    }
+    ```
+*   `[ ]` Modificar `Admin/Products/Edit.vue`:
+    *   Añadir una sección para asignar/desasignar grupos de opciones configurables al producto.
+    *   Podría ser un listado de todos los grupos disponibles (globales y los específicos de otros productos, si se permite) con checkboxes.
+    *   O un multiselect.
+*   `[ ]` Modificar `AdminProductController@update`:
+    *   Procesar la lista de `configurable_option_group_ids` seleccionados.
+    *   Usar el método `sync()` en la relación `configurableOptionGroups()` del producto para actualizar las asociaciones.
+    *   Si se maneja `display_order` en la tabla pivote, la lógica de `sync()` puede ser más compleja o requerir iterar y usar `attach`/`detach`/`updateExistingPivot`.
+*   `[ ]` Modificar `AdminProductController@edit`:
+    *   Cargar los grupos de opciones asignados al producto (`$product->load('configurableOptionGroups')`).
+    *   Cargar todos los grupos de opciones disponibles para la selección.
+*   `[ ]` **Verificación:** Se pueden asignar y desasignar grupos de opciones a un producto.
+
+---
+**¡Gestión de Grupos de Opciones y Opciones (Admin) Implementada!**
+El siguiente paso será la gestión de precios para las opciones configurables individuales.
+```

