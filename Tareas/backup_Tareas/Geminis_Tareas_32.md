
**Tarea 32: Aplicación de Promociones**
Lógica para que los clientes puedan aplicar códigos de promoción durante el proceso de orden.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_32.md
@@ -0,0 +1,58 @@
+# Geminis - Plan de Tareas Detallado - Parte 32
+
+Este documento se enfoca en implementar la lógica para aplicar promociones a las órdenes.
+
+## Fase 6: Proceso de Compra y Facturación - Continuación (Aplicación de Promociones)
+
+### 6.21. Aplicación de Códigos de Promoción en el Proceso de Orden
+*   **Contexto:** Los clientes deben poder ingresar un código de promoción en el formulario de orden para obtener descuentos.
+*   `[ ]` Modificar `Client/Orders/Create.vue` (o donde se configure la orden):
+    *   Añadir un campo de texto para "Código de Promoción".
+    *   Añadir un botón "Aplicar Código".
+*   `[ ]` Crear un método en `Client\OrderController` (o donde se maneje la lógica de la orden) `applyPromotionCode(Request $request)`:
+    *   Recibe el `product_id`, `product_pricing_id` y el `promotion_code`.
+    *   Validar el código:
+        *   Buscar la promoción (`Promotion::where('code', $code)->where('is_active', true)->first()`).
+        *   Verificar fechas de validez (`start_date`, `end_date`).
+        *   Verificar usos máximos (`max_uses`, `current_uses`).
+        *   Verificar usos por cliente (necesitaría una tabla `promotion_usages` para rastrear esto).
+        *   Verificar si aplica al producto/orden (`applies_to`, `product_ids`, `min_order_amount`).
+    *   Si es válida, calcular el descuento (porcentaje o fijo).
+    *   Retornar el monto del descuento y los detalles de la promoción (o un error si no es válida). Esta respuesta será usada por el frontend para actualizar el total.
+*   `[ ]` En el frontend (`Client/Orders/Create.vue`):
+    *   Al hacer clic en "Aplicar Código", enviar una petición AJAX al método `applyPromotionCode`.
+    *   Actualizar el total de la orden mostrado al cliente con el descuento aplicado.
+    *   Almacenar el `promotion_id` o `promotion_code` aplicado para enviarlo al crear la orden.
+*   `[ ]` **Verificación:** Un cliente puede ingresar un código, se valida, y el total se actualiza.
+
+### 6.22. Guardar Promoción Aplicada en la Orden
+*   **Contexto:** Al crear la orden, se debe registrar qué promoción se aplicó y el monto del descuento.
+*   `[ ]` Añadir campos a la tabla `orders` (si no existen y son necesarios):
+    *   `promotion_id` (FK a `promotions`, nullable).
+    *   `discount_amount` (DECIMAL, nullable).
+*   `[ ]` Modificar migración de `orders` y ejecutar `php artisan migrate` (o `migrate:fresh --seed`).
+*   `[ ]` Actualizar `$fillable` en el modelo `Order.php`.
+*   `[ ]` Modificar `Client\OrderController@placeOrder`:
+    *   Si se aplicó una promoción (se tiene `promotion_id` del paso anterior):
+        *   Volver a validar la promoción (para evitar manipulaciones).
+        *   Guardar `promotion_id` y `discount_amount` en la nueva orden.
+        *   Ajustar `orders.total_amount` restando el `discount_amount`.
+        *   Incrementar `promotions.current_uses` (si `max_uses` está definido).
+        *   (Opcional) Registrar el uso en `promotion_usages`.
+*   `[ ]` **Verificación:** La promoción aplicada y el descuento se guardan correctamente en la orden.
+
+### 6.23. Mostrar Descuento en Detalles de Orden y Factura
+*   **Contexto:** El descuento aplicado debe ser visible en los detalles de la orden y en la factura.
+*   `[ ]` Modificar vistas `Admin/Orders/Show.vue` y `Client/Orders/Show.vue`:
+    *   Mostrar el nombre de la promoción aplicada (si existe) y el `discount_amount`.
+*   `[ ]` Modificar lógica de generación de facturas (`generateInvoiceFromOrder`):
+    *   Si la orden tiene `promotion_id` y `discount_amount`:
+        *   Añadir un `invoice_item` de tipo 'discount' con el monto negativo del descuento.
+        *   O ajustar el subtotal/total de la factura directamente.
+*   `[ ]` Modificar vistas `Admin/Invoices/Show.vue` y `Client/Invoices/Show.vue`:
+    *   Mostrar el descuento aplicado.
+*   `[ ]` **Verificación:** El descuento es visible en órdenes y facturas.
+
+---
+**¡Aplicación de Promociones Implementada!**
+Los clientes ahora pueden beneficiarse de descuentos.
+```
