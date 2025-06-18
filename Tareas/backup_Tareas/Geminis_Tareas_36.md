
**Tarea 36: Panel de Revendedor (Gestión de Productos)**
Permitir a los revendedores (si tienen permiso) crear sus propios productos y/o seleccionar qué productos de plataforma revender.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_36.md
@@ -0,0 +1,65 @@
+# Geminis - Plan de Tareas Detallado - Parte 36
+
+Este documento se enfoca en expandir las capacidades de los revendedores para gestionar productos.
+
+## Fase 9: Panel de Revendedor - Gestión de Productos
+
+### 9.6. Listado de Productos para Revendedor
+*   **Contexto:** Los revendedores deben ver los productos que pueden ofrecer: los de plataforma (si son `is_resellable_by_default` o si los ha habilitado) y los propios (si `allow_custom_products`).
+*   `[ ]` Crear `Reseller/ProductController.php`:
+    ```bash
+    php artisan make:controller Reseller/ProductController --resource --model=Product
+    ```
+*   `[ ]` Definir rutas resource para `products` dentro del grupo `reseller` en `routes/web.php`.
+*   `[ ]` Implementar método `index()` en `Reseller\ProductController`:
+    *   Obtener productos de plataforma (`owner_id` IS NULL) que sean `is_resellable_by_default = true`.
+    *   Obtener productos propios del revendedor (`owner_id = Auth::id()`).
+    *   Combinar y paginar.
+    *   Pasar datos a la vista `Reseller/Products/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/Products/Index.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Tabla de productos (Nombre, Tipo, ¿Propio/Plataforma?, Estado).
+    *   Opción para "Crear Producto Propio" (si `Auth::user()->resellerProfile->allow_custom_products`).
+    *   Opción para "Gestionar Precios" (lleva a `edit`).
+*   `[ ]` Añadir enlace "Mis Productos" en `ResellerLayout.vue`.
+*   `[ ]` **Verificación:** El revendedor ve la lista correcta de productos.
+
+### 9.7. Creación/Edición de Productos Propios por Revendedor
+*   **Contexto:** Si `reseller_profiles.allow_custom_products` es true, el revendedor puede crear sus productos.
+*   `[ ]` Verificar el permiso `allow_custom_products` en el `ResellerProfile` del usuario autenticado.
+    *   Se puede acceder a través de `Auth::user()->resellerProfile->allow_custom_products` (asumiendo que la relación `resellerProfile` existe en el modelo `User`).
+*   `[ ]` Implementar `create()` y `store()` en `Reseller\ProductController`:
+    *   Solo accesible si tiene permiso.
+    *   Vista `Reseller/Products/Create.vue`. Formulario similar al de Admin, pero `owner_id` se asigna automáticamente a `Auth::id()`.
+    *   `is_resellable_by_default` no aplica o es `false`.
+    *   Validación (usar `StoreResellerProductRequest`).
+*   `[ ]` Implementar `edit(Product $product)` y `update(Request $request, Product $product)`:
+    *   Solo accesible si `Auth::user()->resellerProfile->allow_custom_products` Y `$product->owner_id === Auth::id()`.
+    *   Vista `Reseller/Products/Edit.vue`.
+        *   Permitir editar detalles del producto y gestionar sus `product_pricing` (similar a Admin).
+    *   Validación (usar `UpdateResellerProductRequest`).
+*   `[ ]` Implementar `destroy(Product $product)`:
+    *   Solo para productos propios (`$product->owner_id === Auth::id()`).
+*   `[ ]` Crear FormRequests: `StoreResellerProductRequest` y `UpdateResellerProductRequest`.
+    *   En `authorize()`, verificar `Auth::user()->role === 'reseller'` y `Auth::user()->resellerProfile->allow_custom_products`.
+    *   En `UpdateResellerProductRequest@authorize()`, también verificar que el producto pertenezca al revendedor.
+*   `[ ]` (Opcional) Adaptar `ProductPolicy` o crear `ResellerProductPolicy`.
+*   `[ ]` **Verificación:** Un revendedor con permiso puede crear y gestionar sus propios productos. No puede editar productos de plataforma.
+
+### 9.8. (Opcional) Selección de Productos de Plataforma para Revender
+*   **Contexto:** Si un producto de plataforma no es `is_resellable_by_default`, el admin podría habilitarlo para revendedores específicos, o el revendedor podría "activarlo" para su catálogo.
+*   **Decisión MVP:** Mantenerlo simple. Los revendedores solo ven/usan los de plataforma que son `is_resellable_by_default = true` y los suyos propios. Esta funcionalidad puede ser una mejora futura.
+
+### 9.9. Gestión de Precios para Productos de Revendedor
+*   **Contexto:** Los revendedores deben poder definir los precios para sus propios productos. Para productos de plataforma, podrían usar los precios base o tener un margen (esto es más complejo).
+*   **Decisión MVP:**
+    *   Para productos propios: El revendedor define sus `product_pricing` como lo hace el admin.
+    *   Para productos de plataforma: El revendedor usa los `product_pricing` definidos por el admin. No puede modificarlos.
+*   `[ ]` La lógica de gestión de precios en `Reseller/Products/Edit.vue` (para productos propios) será similar a la de `Admin/Products/Edit.vue`.
+    *   Rutas y métodos en `ResellerProductController` para `storePricing`, `updatePricing`, `destroyPricing`, asegurando que solo operen sobre productos del revendedor.
+*   `[ ]` **Verificación:** El revendedor puede gestionar precios de sus productos.
+
+---
+**¡Gestión de Productos por Revendedor Implementada!**
+Los revendedores ahora tienen más control sobre su catálogo.
+```
