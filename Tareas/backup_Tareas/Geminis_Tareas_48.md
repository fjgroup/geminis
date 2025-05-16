
**Tarea 48: Base de Conocimiento (Cliente - Visualización)**
Permitir a los clientes navegar y buscar en la base de conocimiento.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_48.md
@@ -0,0 +1,42 @@
+# Geminis - Plan de Tareas Detallado - Parte 48
+
+Este documento se enfoca en la visualización de la Base de Conocimiento por parte de los clientes.
+
+## Fase 15: Base de Conocimiento y Contenido - Continuación
+
+### 15.6. Visualización de la Base de Conocimiento (Cliente)
+*   **Contexto:** Los clientes deben poder navegar por las categorías y leer los artículos de la KB.
+*   `[ ]` Crear `Client/KnowledgeBaseController.php`.
+*   `[ ]` Método `index()`:
+    *   Listar categorías principales de la KB (las que son `is_visible_to_clients = true` y `reseller_id` es NULL o el del revendedor del cliente si existe un contexto de marca blanca).
+    *   Pasar datos a la vista `Client/KnowledgeBase/Index.vue`.
+*   `[ ]` Vista `Client/KnowledgeBase/Index.vue`:
+    *   Usar `ClientLayout.vue`.
+    *   Mostrar lista de categorías, posiblemente con conteo de artículos.
+    *   Campo de búsqueda.
+*   `[ ]` Método `showCategory(KnowledgeBaseCategory $category)`:
+    *   Verificar visibilidad de la categoría.
+    *   Listar artículos publicados de esa categoría.
+    *   Pasar datos a la vista `Client/KnowledgeBase/CategoryShow.vue`.
+*   `[ ]` Vista `Client/KnowledgeBase/CategoryShow.vue`:
+    *   Mostrar nombre de la categoría y lista de sus artículos.
+*   `[ ]` Método `showArticle(KnowledgeBaseCategory $category, KnowledgeBaseArticle $article)`:
+    *   Verificar publicación y visibilidad del artículo y categoría.
+    *   Incrementar `views_count` del artículo.
+    *   Pasar datos a la vista `Client/KnowledgeBase/ArticleShow.vue`.
+*   `[ ]` Vista `Client/KnowledgeBase/ArticleShow.vue`:
+    *   Mostrar título y `content_html` del artículo.
+    *   (Opcional) Botones "¿Fue útil este artículo? Sí/No" que actualicen `helpful_yes_count`/`helpful_no_count`.
+*   `[ ]` Método `search(Request $request)`:
+    *   Buscar artículos por título o contenido.
+    *   Pasar resultados a una vista `Client/KnowledgeBase/SearchResults.vue`.
+*   `[ ]` Definir rutas en `routes/web.php` para `client.kb.*`.
+*   `[ ]` Añadir enlace "Base de Conocimiento" o "Ayuda" en `ClientLayout.vue`.
+*   `[ ]` **Verificación:**
+    *   El cliente puede navegar por las categorías de la KB.
+    *   El cliente puede leer artículos.
+    *   El cliente puede buscar artículos.
+
+---
+**¡Visualización de Base de Conocimiento por Cliente Implementada!**
+Los clientes ahora tienen un recurso de autoayuda.
+```
