
**Tarea 46: Panel de Cliente (Gestión de Dominios)**
Permitir a los clientes ver información básica de sus dominios y, potencialmente, gestionar nameservers.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_46.md
@@ -0,0 +1,46 @@
+# Geminis - Plan de Tareas Detallado - Parte 46
+
+Este documento se enfoca en permitir a los clientes gestionar aspectos básicos de sus dominios.
+
+## Fase 10: Panel de Cliente - Gestión de Dominios
+
+### 10.10. Visualización de Dominios del Cliente
+*   **Contexto:** Los clientes deben poder ver una lista de sus dominios registrados y sus detalles.
+*   `[ ]` Crear `Client/DomainController.php`:
+    ```bash
+    php artisan make:controller Client/DomainController --resource --model=Domain
+    ```
+*   `[ ]` Definir rutas resource para `domains` dentro del grupo `client` en `routes/web.php` (solo `index` y `show` por ahora).
+*   `[ ]` Implementar método `index()` en `Client\DomainController`:
+    *   Listar `Domain` donde `client_id = Auth::id()`.
+    *   Paginado.
+    *   Pasar datos a la vista `Client/Domains/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Client/Domains/Index.vue`:
+    *   Usar `ClientLayout.vue`.
+    *   Tabla/lista de dominios (Nombre Dominio, Fecha Registro, Fecha Expiración, Estado).
+    *   Enlace para ver detalles/gestionar.
+*   `[ ]` Implementar método `show(Domain $domain)`:
+    *   Verificar que `$domain->client_id === Auth::id()`.
+    *   Mostrar detalles del dominio (nameservers, estado de auto-renovación, EPP code si se permite ver).
+    *   Vista `Client/Domains/Show.vue`.
+*   `[ ]` Añadir enlace "Mis Dominios" en `ClientLayout.vue`.
+*   `[ ]` **Verificación:** El cliente puede ver sus dominios y detalles.
+
+### 10.11. (Opcional) Gestión de Nameservers por Cliente
+*   **Contexto:** Permitir a los clientes actualizar los nameservers de sus dominios (requiere integración con módulo registrador).
+*   `[ ]` **Interfaz Módulo Registrador:**
+    *   Definir una interfaz `DomainRegistrarModuleInterface` con métodos como `getNameservers(Domain $domain)`, `updateNameservers(Domain $domain, array $nameservers)`.
+    *   Crear clases Platzhalter para módulos específicos (ej. `EnomModule`).
+*   `[ ]` **Backend:**
+    *   En `Client\DomainController@show`, si el dominio tiene un `registrar_module_slug`, intentar obtener los NS actuales.
+    *   Método `updateNameservers(Request $request, Domain $domain)`:
+        *   Validar los nameservers.
+        *   Llamar al método del módulo registrador para actualizar los NS.
+        *   Actualizar `domains` tabla si es necesario.
+*   `[ ]` **Frontend (`Client/Domains/Show.vue`):**
+    *   Formulario para editar nameservers.
+*   `[ ]` **Verificación:** El cliente puede (conceptualmente) actualizar nameservers. La implementación real depende del módulo.
+
+---
+**¡Visualización de Dominios por Cliente Implementada!**
+La gestión avanzada de dominios (transferencias, EPP, etc.) y la integración real con registradores son tareas más complejas para el futuro.
+```
