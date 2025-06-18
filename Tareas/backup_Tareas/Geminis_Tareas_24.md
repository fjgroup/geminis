
**Tarea 24: Plantillas de Correo (Base)**
Migración, modelo y CRUD básico (Admin) para `EmailTemplates`.

```diff
--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_24.md
@@ -0,0 +1,64 @@
+# Geminis - Plan de Tareas Detallado - Parte 24
+
+Este documento se enfoca en la gestión de Plantillas de Correo Electrónico.
+
+## Fase 8: Módulos Adicionales - Continuación
+
+### 8.12. Migración de la Tabla `email_templates`
+*   **Contexto:** Almacena las plantillas de correo para diversas notificaciones del sistema.
+*   `[ ]` Crear la migración para la tabla `email_templates`:
+    ```bash
+    php artisan make:migration create_email_templates_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('email_templates', function (Blueprint $table) {
+        $table->id();
+        $table->string('name')->unique()->comment('Nombre descriptivo, ej: "Bienvenida Hosting Compartido"');
+        $table->string('slug')->unique()->index()->comment('Identificador interno, ej: welcome.shared_hosting');
+        $table->enum('type', ['general', 'product', 'support', 'invoice', 'domain', 'auth'])->index();
+        $table->string('subject');
+        $table->text('body_html');
+        $table->text('body_text')->nullable();
+        $table->string('language_code', 10)->default('es')->index();
+        $table->boolean('is_customizable_by_reseller')->default(false);
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para plantillas globales/base');
+        $table->timestamps();
+        // Unique constraint for reseller-specific overrides of a global template
+        $table->unique(['slug', 'language_code', 'reseller_id'], 'template_slug_lang_reseller_unique');
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `email_templates` existe.
+
+### 8.13. Modelo `EmailTemplate`
+*   `[ ]` Crear el modelo `EmailTemplate`: `php artisan make:model EmailTemplate`
+*   `[ ]` Configurar `$fillable`.
+*   `[ ]` Definir relación `reseller()` (belongsTo User, opcional).
+*   `[ ]` **Verificación:** Se pueden crear plantillas de correo.
+
+### 8.14. CRUD Básico para `EmailTemplate` (Admin)
+*   **Contexto:** Los administradores deben poder gestionar las plantillas de correo globales y las personalizaciones de revendedores.
+*   `[ ]` Crear `Admin\EmailTemplateController.php`:
+    ```bash
+    php artisan make:controller Admin/EmailTemplateController --resource --model=EmailTemplate
+    ```
+*   `[ ]` Definir rutas resource para `email-templates` en `routes/web.php` (admin).
+*   `[ ]` Implementar CRUD completo:
+    *   `index()`: Listar plantillas. Vista `Admin/EmailTemplates/Index.vue`.
+    *   `create()`/`store()`: Formulario para crear plantillas. Vista `Admin/EmailTemplates/Create.vue`.
+        *   Campos: nombre, slug (sugerir basado en nombre), tipo, asunto, cuerpo HTML (usar un editor WYSIWYG simple o textarea), cuerpo texto, idioma, personalizable por revendedor.
+    *   `edit()`/`update()`: Formulario para editar. Vista `Admin/EmailTemplates/Edit.vue`.
+    *   `destroy()`.
+*   `[ ]` Crear FormRequests (`StoreEmailTemplateRequest`, `UpdateEmailTemplateRequest`).
+*   `[ ]` (Opcional) Crear `EmailTemplatePolicy` y aplicarla.
+*   `[ ]` Añadir enlace en `AdminLayout.vue`.
+*   `[ ]` **Verificación:** CRUD básico de plantillas de correo funciona.
+
+---
+**¡Gestión Base de Plantillas de Correo (Admin) Implementada!**
+La lógica para enviar correos usando estas plantillas se integrará en los módulos correspondientes (órdenes, servicios, soporte, etc.).
+```
