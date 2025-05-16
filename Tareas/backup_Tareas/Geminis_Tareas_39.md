Geminis_Tareas_33.md

**Tarea 39: Pruebas (Unitarias y de Integración)**
Escribir pruebas automatizadas para los módulos y funcionalidades clave del sistema.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_39.md
@@ -0,0 +1,45 @@
+# Geminis - Plan de Tareas Detallado - Parte 39
+
+Este documento se enfoca en la creación de pruebas automatizadas para asegurar la calidad y estabilidad del sistema.
+
+## Fase 14: Pruebas y Refinamiento
+
+### 14.1. Configuración del Entorno de Pruebas
+*   **Contexto:** Asegurar que las pruebas se ejecuten en un entorno aislado.
+*   `[ ]` Verificar que `phpunit.xml` esté configurado para usar una base de datos en memoria (SQLite) o una base de datos de prueba separada.
+    ```xml
+    // phpunit.xml (ejemplo para SQLite en memoria)
+    <env name="DB_CONNECTION" value="sqlite"/>
+    <env name="DB_DATABASE" value=":memory:"/>
+    ```
+*   `[ ]` Usar el trait `RefreshDatabase` en las clases de prueba para migrar la BD antes de cada prueba y limpiarla después.
+*   `[ ]` **Verificación:** Las pruebas pueden ejecutarse sin afectar la base de datos de desarrollo.
+
+### 14.2. Pruebas Unitarias para Modelos y Servicios
+*   **Contexto:** Probar la lógica interna de modelos (relaciones, scopes, accessors/mutators) y servicios.
+*   `[ ]` Crear pruebas unitarias (en `tests/Unit`) para:
+    *   Modelo `User`: Relaciones `clients()`, `reseller()`, `resellerProfile()`.
+    *   Modelo `Product`: Relaciones `owner()`, `pricings()`, `configurableOptionGroups()`.
+    *   Modelo `ClientService`: Lógica de cambio de estado, cálculo de `next_due_date`.
+    *   `InvoiceService`: Generación de facturas de orden y renovación.
+    *   `EmailService`: Lógica de selección de plantillas y parseo (puede requerir mocks).
+*   `[ ]` **Verificación:** `php artisan test --filter=Unit` pasa.
+
+### 14.3. Pruebas de Integración (Feature Tests) para Funcionalidades Clave
+*   **Contexto:** Probar flujos completos de la aplicación, incluyendo interacciones HTTP, autorización y respuestas.
+*   `[ ]` Crear pruebas de feature (en `tests/Feature`) para:
+    *   **Autenticación:** Login, registro, protección de rutas.
+    *   **CRUD Admin Usuarios:** Crear, listar, editar, eliminar usuarios (probando policies).
+    *   **CRUD Admin Productos:** Crear, listar, editar, eliminar productos y sus precios.
+    *   **Proceso de Orden Cliente:** Seleccionar producto, aplicar promoción, generar orden y factura.
+    *   **Sistema de Soporte:** Cliente crea ticket, admin/reseller responde, cliente responde.
+    *   **Panel Revendedor:** Revendedor crea cliente, revendedor ve servicios de su cliente.
+    *   **Panel Cliente:** Cliente ve sus servicios y facturas.
+*   `[ ]` Usar factories para crear datos de prueba.
+*   `[ ]` Simular usuarios con diferentes roles (`actingAs`).
+*   `[ ]` Verificar respuestas HTTP (status codes, contenido JSON, vistas Inertia).
+*   `[ ]` **Verificación:** `php artisan test --filter=Feature` pasa.
+
+---
+**¡Pruebas Automatizadas Implementadas!**
+Esto aumenta la confianza en la estabilidad del código y facilita la detección de regresiones. Es un proceso continuo a medida que se añaden nuevas funcionalidades.
+```
