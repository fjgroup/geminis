
**Tarea 29: Panel de Cliente (Layout, Dashboard, Middleware)**
Inicio del panel de cliente, con su layout, dashboard básico y el middleware para proteger sus rutas.

```diff
--- /dev/null
+++ b/E:\herd\geminis\Tareas\Geminis_Tareas_29.md
@@ -0,0 +1,64 @@
+# Geminis - Plan de Tareas Detallado - Parte 29
+
+Este documento inicia la Fase 10, enfocada en el desarrollo del Panel de Cliente.
+
+## Fase 10: Panel de Cliente
+
+### 10.1. Middleware para Clientes (`EnsureUserIsClient`)
+*   **Contexto:** Proteger las rutas del portal del cliente para que solo usuarios con rol 'client' (o también 'reseller' si un revendedor puede actuar como cliente de la plataforma) puedan acceder.
+*   `[ ]` Crear el middleware:
+    ```bash
+    php artisan make:middleware EnsureUserIsClient
+    ```
+*   `[ ]` En `app/Http/Middleware/EnsureUserIsClient.php`, implementar la lógica:
+    ```php
+    public function handle(Request $request, Closure $next): Response
+    {
+        // Permite acceso a 'client' y 'reseller' (un reseller puede ser cliente de la plataforma)
+        // Si solo quieres 'client', ajusta la condición.
+        if (!Auth::check() || !in_array(Auth::user()->role, ['client', 'reseller'])) {
+            return redirect('/'); 
+        }
+        return $next($request);
+    }
+    ```
+*   `[ ]` Registrar el alias del middleware en `app/Http/Kernel.php`:
+    ```php
+    // En protected $middlewareAliases o $routeMiddleware
+    'client' => \App\Http\Middleware\EnsureUserIsClient::class,
+    ```
+*   `[ ]` **Verificación:** El middleware funciona.
+
+### 10.2. Layout para el Panel de Cliente (`ClientLayout.vue`)
+*   **Contexto:** Layout específico para el portal del cliente.
+*   `[ ]` Crear `resources/js/Layouts/ClientLayout.vue`.
+    *   Puede basarse en `AuthenticatedLayout.vue` de Breeze.
+    *   Navegación: Dashboard, Mis Servicios, Mis Dominios (futuro), Mis Facturas, Soporte, Mi Perfil.
+*   `[ ]` **Verificación:** El layout se renderiza.
+
+### 10.3. Dashboard Básico del Cliente
+*   `[ ]` Crear `Client/ClientDashboardController.php`:
+    ```bash
+    php artisan make:controller Client/ClientDashboardController
+    ```
+*   `[ ]` En el controlador, método `index()`:
+    *   Cargar datos para el cliente: conteo de servicios activos, facturas pendientes, tickets abiertos.
+    *   Filtrar por `Auth::id()`.
+    *   Pasar datos a la vista `Client/Dashboard.vue`.
+*   `[ ]` Crear vista `resources/js/Pages/Client/Dashboard.vue`:
+    *   Usar `ClientLayout.vue`.
+    *   Mostrar la información cargada.
+*   `[ ]` Definir rutas en `routes/web.php` para el panel de cliente:
+    ```php
+    use App\Http\Controllers\Client\ClientDashboardController;
+
+    Route::prefix('client')->name('client.')->middleware(['auth', 'verified', 'client'])->group(function () {
+        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
+        // Aquí irán más rutas del panel de cliente
+    });
+    ```
+*   `[ ]` El enlace "Dashboard" que Breeze crea (si se usa `AuthenticatedLayout`) ya debería funcionar o se ajusta para apuntar a `client.dashboard`.
+*   `[ ]` **Verificación:** Un cliente logueado puede acceder a su dashboard.
+
+---
+**¡Panel de Cliente (Base) Implementado!**
+Las siguientes tareas se enfocarán en las funcionalidades específicas del panel de cliente.
+```
