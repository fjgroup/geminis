**Tarea 10: Precios de Opciones Configurables**
Se enfocará en cómo se definen y gestionan los precios para las `ConfigurableOption` individuales, vinculándolos a los ciclos de facturación del producto base.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_10.md
@@ -0,0 +1,64 @@
+# Geminis - Plan de Tareas Detallado - Parte 10
+
+Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la gestión de precios para las opciones configurables individuales.
+
+## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos - Continuación
+
+### 4.14. Gestión de Precios para `ConfigurableOption`
+*   **Contexto:** Cada opción configurable (ej. "CentOS 7", "1GB RAM Adicional") puede tener un precio diferente que se suma al precio base del producto, y este precio puede variar según el ciclo de facturación del producto.
+*   **Decisión:** La gestión de precios de las opciones se hará desde la vista de edición del `ConfigurableOptionGroup`, anidada bajo cada opción.
+*   `[ ]` Modificar `Admin/ConfigurableOptionGroups/Edit.vue`:
+    *   Para cada `ConfigurableOption` listada, añadir una sub-sección o un botón para "Gestionar Precios".
+    *   Al gestionar precios para una opción, mostrar una tabla de sus `configurable_option_pricing`.
+    *   Formulario (modal o en línea) para añadir/editar precios de la opción:
+        *   `product_pricing_id`: Un select que muestre los ciclos de facturación y precios del producto al que pertenece este grupo (o de todos los productos si el grupo es global y se está asignando). Esto es complejo si el grupo es global.
+            *   **Alternativa más simple para grupos globales:** Los precios de las opciones se definen sin `product_pricing_id` directo, y se asume que se aplican a cualquier ciclo. O se definen precios por ciclo ('monthly', 'annually') directamente en `configurable_option_pricing` sin FK a `product_pricing`.
+            *   **Decisión para MVP:** Vincular a `product_pricing_id`. Esto significa que un grupo de opciones específico de un producto tendrá opciones cuyos precios se definen para los ciclos de ESE producto. Los grupos globales son más complejos de preciar de esta manera universal.
+            *   **Reconsideración:** Para que los grupos globales sean reutilizables, la tabla `configurable_option_pricing` podría tener `billing_cycle` y `currency_code` directamente, en lugar de `product_pricing_id`. Luego, al calcular el precio total de un servicio, se buscaría el precio de la opción que coincida con el ciclo del servicio.
+            *   **Decisión Final (Compromiso):** Mantener `product_pricing_id` por ahora. Los "grupos globales" serán plantillas que, al asignarse a un producto, requerirán que se definan/copien los precios de sus opciones para los ciclos de ese producto específico. O, más simple, los grupos globales no tienen precios predefinidos y se establecen al vincularlos a un producto.
+            *   **Simplificación Máxima para MVP:** Los precios de las opciones se definen por opción, y se asocian a un `product_pricing_id` del producto al que está vinculado el grupo. Si el grupo es global, esta sección de precios solo se activa cuando el grupo se asocia a un producto.
+        *   `price`: Precio adicional de la opción.
+        *   `setup_fee`: Tarifa de configuración adicional de la opción.
+*   `[ ]` Modificar `AdminConfigurableOptionGroupController`:
+    *   Al editar un grupo (`edit` method), si el grupo está asociado a un producto (`$configurableOptionGroup->product_id` no es NULL), cargar los `product.pricings` de ese producto y pasarlos a la vista para el select de `product_pricing_id`.
+*   `[ ]` Añadir rutas y métodos en `AdminConfigurableOptionGroupController` para gestionar precios de opciones:
+    *   `storeOptionPricing(Request $request, ConfigurableOptionGroup $group, ConfigurableOption $option)`
+    *   `updateOptionPricing(Request $request, ConfigurableOptionGroup $group, ConfigurableOption $option, ConfigurableOptionPricing $pricing)`
+    *   `destroyOptionPricing(ConfigurableOptionGroup $group, ConfigurableOption $option, ConfigurableOptionPricing $pricing)`
+*   `[ ]` Implementar lógica en estos métodos (validación, creación, actualización, eliminación de `ConfigurableOptionPricing`).
+    *   Asegurar que `product_pricing_id` pertenezca al producto del grupo (si el grupo es específico de producto).
+*   `[ ]` **Verificación:**
+    *   Se pueden añadir, editar y eliminar precios para las opciones configurables.
+    *   La validación de unicidad (`configurable_option_id`, `product_pricing_id`) en `configurable_option_pricing` funciona.
+
+### 4.15. (Opcional) Políticas de Acceso para Opciones Configurables y sus Precios
+*   `[ ]` Considerar si se necesitan policies separadas para `ConfigurableOption` y `ConfigurableOptionPricing`, o si la policy de `ConfigurableOptionGroup` (o `ProductPolicy`) es suficiente.
+*   `[ ]` (Si se crean) Generar, registrar e implementar policies.
+
+---
+**¡Gestión de Precios para Opciones Configurables Implementada!**
+Los productos ahora pueden tener una estructura de precios más compleja con opciones adicionales. El siguiente paso es empezar a trabajar con los servicios del cliente.
+```
