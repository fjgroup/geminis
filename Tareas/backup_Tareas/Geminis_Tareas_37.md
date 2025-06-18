
**Tarea 37: Panel de Revendedor (Configuraciones)**
Permitir a los revendedores personalizar su perfil (`ResellerProfile`) y algunas configuraciones (`Settings`) específicas.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_37.md
@@ -0,0 +1,50 @@
+# Geminis - Plan de Tareas Detallado - Parte 37
+
+Este documento se enfoca en permitir a los revendedores configurar aspectos de su cuenta y marca.
+
+## Fase 9: Panel de Revendedor - Configuraciones
+
+### 9.10. Gestión del Perfil de Revendedor (`ResellerProfile`)
+*   **Contexto:** Los revendedores deben poder actualizar la información de su marca y configuraciones de perfil.
+*   `[ ]` Crear `Reseller/ProfileController.php`.
+*   `[ ]` Método `edit()` en `Reseller\ProfileController`:
+    *   Obtener el `ResellerProfile` del revendedor autenticado (`Auth::user()->resellerProfile()->firstOrCreate(['user_id' => Auth::id()])`).
+    *   Pasar el perfil a la vista `Reseller/Profile/Edit.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/Profile/Edit.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Formulario para editar campos de `ResellerProfile` (brand_name, custom_domain (informativo, admin lo configura), logo_url, support_email, terms_url).
+    *   `allow_custom_products` sería solo visible, no editable por el revendedor (lo gestiona el admin).
+*   `[ ]` Método `update(Request $request)` en `Reseller\ProfileController`:
+    *   Validar los datos del formulario.
+    *   Actualizar el `ResellerProfile` del revendedor.
+    *   Manejar subida de logo si se incluye.
+*   `[ ]` Definir rutas en `routes/web.php` para `reseller.profile.edit` y `reseller.profile.update`.
+*   `[ ]` Añadir enlace "Mi Perfil" o "Configuración de Marca" en `ResellerLayout.vue`.
+*   `[ ]` **Verificación:** El revendedor puede actualizar los detalles de su perfil/marca.
+
+### 9.11. Gestión de Configuraciones Específicas del Revendedor (`Settings`)
+*   **Contexto:** Permitir a los revendedores sobrescribir ciertas configuraciones globales o definir las suyas (ej. pasarelas de pago, textos de emails si `is_customizable_by_reseller` es true).
+*   `[ ]` Crear `Reseller/SettingController.php`.
+*   `[ ]` Método `index()` en `Reseller\SettingController`:
+    *   Obtener las configuraciones globales que el revendedor puede sobrescribir.
+    *   Obtener las configuraciones específicas del revendedor (`Setting::where('reseller_id', Auth::id())->get()`).
+    *   Pasar datos a la vista `Reseller/Settings/Index.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/Settings/Index.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Formulario para editar las configuraciones permitidas (ej. claves API de su propia pasarela de pago, si se implementa).
+*   `[ ]` Método `update(Request $request)` en `Reseller\SettingController`:
+    *   Validar los datos.
+    *   Actualizar o crear registros en la tabla `settings` con `reseller_id = Auth::id()`.
+*   `[ ]` Definir rutas en `routes/web.php` para `reseller.settings.index` y `reseller.settings.update`.
+*   `[ ]` Añadir enlace "Configuraciones" en `ResellerLayout.vue`.
+*   `[ ]` **Verificación:** El revendedor puede ver y modificar las configuraciones que le son permitidas.
+
+### 9.12. (Opcional) Personalización de Plantillas de Correo por Revendedor
+*   **Contexto:** Si una `EmailTemplate` es `is_customizable_by_reseller`, el revendedor podría editar su propia versión.
+*   `[ ]` En `Reseller/EmailTemplateController.php` (crearlo):
+    *   Listar plantillas personalizables y las ya personalizadas por el revendedor.
+    *   Formulario para editar el `subject` y `body_html` de una plantilla (crea un nuevo registro en `email_templates` con `reseller_id` y el mismo `slug`).
+*   `[ ]` `EmailService` debe priorizar la plantilla del revendedor si existe.
+*   `[ ]` **Verificación:** El revendedor puede personalizar plantillas de correo permitidas.
+
+---
+**¡Configuraciones del Panel de Revendedor Implementadas!**
+Los revendedores tienen ahora más control sobre su entorno.
+```
