--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_26.md
@@ -0,0 +1,67 @@
+# Geminis - Plan de Tareas Detallado - Parte 26
+
+Este documento inicia la Fase 9, enfocada en el desarrollo del Panel de Revendedor.
+
+## Fase 9: Panel de Revendedor
+
+### 9.1. Middleware para Revendedores (`EnsureUserIsReseller`)
+*   **Contexto:** Necesitamos proteger las rutas del panel de revendedor para que solo usuarios con rol 'reseller' puedan acceder.
+*   `[ ]` Crear el middleware:
+    ```bash
+    php artisan make:middleware EnsureUserIsReseller
+    ```
+*   `[ ]` En `app/Http/Middleware/EnsureUserIsReseller.php`, implementar la lógica:
+    ```php
+    public function handle(Request $request, Closure $next): Response
+    {
+        if (!Auth::check() || Auth::user()->role !== 'reseller') {
+            // O redirigir a login, o a una página de "acceso denegado" si está logueado pero no es reseller
+            return redirect('/'); 
+        }
+        return $next($request);
+    }
+    ```
+*   `[ ]` Registrar el alias del middleware en `app/Http/Kernel.php` (en `$middlewareAliases` o `$routeMiddleware`):
+    ```php
+    // En protected $middlewareAliases o $routeMiddleware
+    'reseller' => \App\Http\Middleware\EnsureUserIsReseller::class,
+    ```
+*   `[ ]` **Verificación:** El middleware funciona y protege rutas.
+
+### 9.2. Layout para el Panel de Revendedor (`ResellerLayout.vue`)
+*   **Contexto:** Similar al `AdminLayout`, pero específico para revendedores.
+*   `[ ]` Crear `resources/js/Layouts/ResellerLayout.vue`.
+    *   Puede ser una copia modificada de `AdminLayout.vue` o `AuthenticatedLayout.vue` (el que Breeze instala).
+    *   Debe tener una barra lateral/navegación con enlaces a las futuras secciones del panel de revendedor (Dashboard, Mis Clientes, Mis Servicios, Mis Facturas, Soporte, Configuración).
+    *   Mostrar el nombre del revendedor/marca (del `reseller_profile` o `user`).
+*   `[ ]` **Verificación:** El layout se renderiza correctamente.
+
+### 9.3. Dashboard Básico del Revendedor
+*   `[ ]` Crear `Reseller/ResellerDashboardController.php`:
+    ```bash
+    php artisan make:controller Reseller/ResellerDashboardController
+    ```
+*   `[ ]` En el controlador, método `index()`:
+    *   Cargar datos relevantes para el revendedor (ej. conteo de sus clientes, servicios activos de sus clientes, tickets abiertos de sus clientes).
+    *   Usar `Auth::id()` para filtrar los datos pertenecientes al revendedor logueado.
+    *   Pasar datos a la vista `Reseller/Dashboard.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Reseller/Dashboard.vue`:
+    *   Usar `ResellerLayout.vue`.
+    *   Mostrar las estadísticas/información cargada.
+*   `[ ]` Definir rutas en `routes/web.php` para el panel de revendedor:
+    ```php
+    use App\Http\Controllers\Reseller\ResellerDashboardController;
+
+    Route::prefix('reseller')->name('reseller.')->middleware(['auth', 'verified', 'reseller'])->group(function () {
+        Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');
+        // Aquí irán más rutas del panel de revendedor
+    });
+    ```
+*   `[ ]` Añadir enlace "Panel de Revendedor" en `AuthenticatedLayout.vue` o `AppLayout.vue` (visible solo si `auth()->user()->role === 'reseller'`).
+*   `[ ]` **Verificación:** Un revendedor logueado puede acceder a su dashboard y ver información básica.
+
+---
+**¡Panel de Revendedor (Base) Implementado!**
+Las siguientes tareas se enfocarán en las funcionalidades específicas del panel de revendedor, como la gestión de sus clientes.
+```
