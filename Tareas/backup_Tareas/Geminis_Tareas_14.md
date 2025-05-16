
**Tarea 14: Proceso de Creación de Órdenes y Listados**
Cómo los clientes crean órdenes y cómo se listan para admins y clientes.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_14.md
@@ -0,0 +1,65 @@
+# Geminis - Plan de Tareas Detallado - Parte 14
+
+Este documento se enfoca en el proceso de creación de órdenes por parte del cliente y el listado de estas órdenes tanto para clientes como para administradores.
+
+## Fase 6: Proceso de Compra y Facturación - Continuación
+
+### 6.5. Proceso de Creación de Órdenes (Cliente)
+*   **Contexto:** Los clientes deben poder seleccionar productos y añadirlos a un "carrito" o directamente generar una orden.
+*   **Decisión MVP:** Simplificar, no habrá un carrito persistente. El cliente seleccionará un producto y sus opciones, y esto generará una orden directamente.
+*   `[ ]` Crear `Client\OrderController.php` (o añadir a un controlador existente como `Client\ProductController.php`).
+*   `[ ]` Método `showOrderForm(Product $product)`:
+    *   Muestra una página donde el cliente puede ver el producto, seleccionar un ciclo de facturación (`product_pricing_id`).
+    *   Si el producto tiene grupos de opciones configurables (`product->configurableOptionGroups`), listarlos y permitir al cliente seleccionar opciones.
+    *   Calcular el precio total preliminar (producto base + opciones seleccionadas).
+    *   Vista `Client/Orders/Create.vue`.
+*   `[ ]` Método `placeOrder(Request $request, Product $product)`:
+    *   Validar la selección del cliente (ciclo de facturación, opciones configurables válidas).
+    *   Crear el registro en la tabla `orders`.
+    *   Crear los registros correspondientes en `order_items` (uno para el producto base, y uno por cada opción configurable seleccionada con su precio).
+    *   Generar un `order_number` único.
+    *   Calcular `total_amount` final.
+    *   Redirigir al cliente a una página de confirmación de orden o directamente a la pasarela de pago (futuro). Por ahora, a una página de "Orden Recibida".
+*   `[ ]` Crear rutas en `routes/web.php` para el cliente (ej. `/order/product/{product}`, `/order/place/{product}`).
+*   `[ ]` **Verificación:** Un cliente puede seleccionar un producto, elegir ciclo/opciones y generar una orden con sus ítems.
+
+### 6.6. Listado de Órdenes (Admin)
+*   `[ ]` Crear `Admin\OrderController.php`:
+    ```bash
+    php artisan make:controller Admin/OrderController --resource --model=Order
+    ```
+*   `[ ]` Definir rutas resource para `orders` en `routes/web.php` (admin).
+*   `[ ]` Implementar `index()`:
+    *   Listar todas las órdenes, paginadas.
+    *   Permitir filtros (por cliente, estado, fecha).
+    *   Mostrar información clave (Número de Orden, Cliente, Fecha, Total, Estado).
+    *   Vista `Admin/Orders/Index.vue`.
+*   `[ ]` Implementar `show()`:
+    *   Mostrar detalles de una orden, incluyendo sus `order_items`.
+    *   Cargar relaciones `client`, `items.product`, `items.productPricing`.
+    *   Vista `Admin/Orders/Show.vue`.
+*   `[ ]` (Opcional) Implementar `edit()` y `update()` para permitir a los admins modificar ciertos aspectos de una orden (ej. estado, notas).
+*   `[ ]` (Opcional) Implementar `destroy()` (cancelar/eliminar orden).
+*   `[ ]` (Opcional) Crear `OrderPolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** Los administradores pueden ver y gestionar órdenes.
+
+### 6.7. Listado de Órdenes (Cliente)
+*   `[ ]` En `Client\OrderController.php` (o similar), método `index()`:
+    *   Obtener las órdenes del cliente autenticado (`client_id = auth()->id()`).
+    *   Paginadas y ordenadas por fecha.
+    *   Vista `Client/Orders/Index.vue`.
+*   `[ ]` En `Client\OrderController.php`, método `show(Order $order)`:
+    *   Asegurar que la orden pertenezca al cliente autenticado.
+    *   Mostrar detalles de la orden y sus ítems.
+    *   Vista `Client/Orders/Show.vue`.
+*   `[ ]` Definir rutas en `routes/web.php` para el cliente (ej. `/client/orders`, `/client/orders/{order}`).
+*   `[ ]` Añadir enlace en `ClientLayout.vue` o `AppLayout.vue`.
+*   `[ ]` **Verificación:** Un cliente puede ver su historial de órdenes y los detalles de cada una.
+
+---
+**¡Proceso Básico de Órdenes Implementado!**
+El siguiente paso es la generación de facturas a partir de estas órdenes.
+```
