
**Tarea 47: Base de Conocimiento (Admin CRUD)**
Creación de la estructura y gestión administrativa para artículos de la base de conocimiento.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_47.md
@@ -0,0 +1,78 @@
+# Geminis - Plan de Tareas Detallado - Parte 47
+
+Este documento inicia la implementación de una Base de Conocimiento (KB).
+
+## Fase 15: Base de Conocimiento y Contenido
+
+### 15.1. Migración de la Tabla `kb_categories`
+*   **Contexto:** Las categorías para organizar los artículos de la KB.
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_kb_categories_table
+    ```
+*   `[ ]` Modificar el método `up()`:
+    ```php
+    Schema::create('kb_categories', function (Blueprint $table) {
+        $table->id();
+        $table->string('name');
+        $table->string('slug')->unique();
+        $table->text('description')->nullable();
+        $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->onDelete('set null'); // Para subcategorías
+        $table->integer('display_order')->default(0);
+        $table->boolean('is_visible_to_clients')->default(true);
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para categorías globales');
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración.
+*   `[ ]` **Verificación:** La tabla `kb_categories` existe.
+
+### 15.2. Modelo `KnowledgeBaseCategory`
+*   `[ ]` Crear el modelo: `php artisan make:model KnowledgeBaseCategory -m` (el `-m` crea la migración si no existe, pero ya la creamos).
+*   `[ ]` Configurar `$fillable`. Definir relaciones `parent()`, `children()`, `articles()`, `reseller()`.
+
+### 15.3. Migración de la Tabla `kb_articles`
+*   **Contexto:** Los artículos individuales de la KB.
+*   `[ ]` Crear la migración:
+    ```bash
+    php artisan make:migration create_kb_articles_table
+    ```
+*   `[ ]` Modificar el método `up()`:
+    ```php
+    Schema::create('kb_articles', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('category_id')->constrained('kb_categories')->onDelete('cascade');
+        $table->string('title');
+        $table->string('slug')->unique();
+        $table->longText('content_html');
+        $table->integer('views_count')->default(0);
+        $table->integer('helpful_yes_count')->default(0);
+        $table->integer('helpful_no_count')->default(0);
+        $table->boolean('is_published')->default(true);
+        $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario admin/staff que lo creó/editó
+        $table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para artículos globales');
+        $table->timestamps();
+        $table->softDeletes();
+    });
+    ```
+*   `[ ]` Ejecutar la migración.
+*   `[ ]` **Verificación:** La tabla `kb_articles` existe.
+
+### 15.4. Modelo `KnowledgeBaseArticle`
+*   `[ ]` Crear el modelo: `php artisan make:model KnowledgeBaseArticle`
+*   `[ ]` Configurar `$fillable`. Definir relaciones `category()`, `author()`, `reseller()`.
+
+### 15.5. CRUD Admin para `KnowledgeBaseCategory` y `KnowledgeBaseArticle`
+*   `[ ]` Crear controladores `Admin\KnowledgeBaseCategoryController` y `Admin\KnowledgeBaseArticleController` (resources).
+*   `[ ]` Definir rutas resource para `kb-categories` y `kb-articles` (admin).
+*   `[ ]` Implementar CRUDs completos para ambos, con sus vistas (`Admin/KbCategories/*`, `Admin/KbArticles/*`) y FormRequests.
+    *   Para artículos, usar un editor WYSIWYG para el campo `content_html`.
+    *   Permitir asignar artículos a categorías.
+*   `[ ]` (Opcional) Crear Policies y aplicarlas.
+*   `[ ]` Añadir enlaces en `AdminLayout.vue` para "Base de Conocimiento".
+*   `[ ]` **Verificación:** CRUDs para categorías y artículos de KB funcionan desde el panel de admin.
+
+---
+**¡Admin CRUD para Base de Conocimiento Implementado!**
+La siguiente tarea se enfocará en la visualización de la KB por parte de los clientes.
+```
