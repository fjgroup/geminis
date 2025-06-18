# Geminis - Plan de Tareas Detallado - Parte 04

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la creación de un Dashboard para el Administrador y la implementación de Políticas de Laravel para un control de acceso más granular.

## Fase 2: Mejoras en el Panel de Administración y Seguridad Básica - Continuación

### 2.5. Creación de un Dashboard Básico para el Administrador
*   **Contexto:** El panel de administración necesita una página de inicio o dashboard.
*   `[ ]` Crear un controlador para el Dashboard del Admin:
    ```bash
    php artisan make:controller Admin/AdminDashboardController
    ```
*   `[ ]` En `app/Http/Controllers/Admin/AdminDashboardController.php`, crear un método `index()`:
    ```php
    // app/Http/Controllers/Admin/AdminDashboardController.php
    namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller; // Asegúrate que Controller base está importado
    use Illuminate\Http\Request;
    use Inertia\Inertia;
    use Inertia\Response; // Importar Response para el tipado

    class AdminDashboardController extends Controller
    {
        public function index(): Response
        {
            // Aquí podrías cargar datos para el dashboard, ej: conteos, estadísticas.
            // $userCount = \App\Models\User::count();
            // $activeServicesCount = \App\Models\ClientService::where('status', 'active')->count();

            return Inertia::render('Admin/Dashboard'/*, [
                'userCount' => $userCount,
                'activeServicesCount' => $activeServicesCount,
            ]*/);
        }
    }
    ```
*   `[ ]` Definir la ruta para el dashboard del admin en `routes/web.php` dentro del grupo de administración:
    ```php
    // routes/web.php
    use App\Http\Controllers\Admin\AdminDashboardController; // IMPORTAR
    use App\Http\Controllers\Admin\UserController as AdminUserController;

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // AÑADIR ESTA RUTA
        Route::resource('users', AdminUserController::class);
        // ...
    });
    ```
*   `[ ]` Crear la vista del Dashboard en `resources/js/Pages/Admin/Dashboard.vue`:
    ```vue
    // resources/js/Pages/Admin/Dashboard.vue
    <script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Head } from '@inertiajs/vue3';

    // defineProps({
    //   userCount: Number,
    //   activeServicesCount: Number,
    // });
    </script>

    <template>
        <AdminLayout title="Admin Dashboard">
            <Head title="Admin Dashboard" />

            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Admin Dashboard
                </h2>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            Welcome to the Admin Dashboard!
                        </div>
                    </div>

                    <!-- Ejemplo de cómo mostrar estadísticas si las pasas desde el controlador -->
                    <!--
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Users</h3>
                            <p class="mt-1 text-3xl font-semibold text-indigo-600">{{ userCount }}</p>
                        </div>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Active Services</h3>
                            <p class="mt-1 text-3xl font-semibold text-indigo-600">{{ activeServicesCount }}</p>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </AdminLayout>
    </template>
    ```
*   `[ ]` Actualizar el enlace principal del "Admin Panel" en `resources/js/Layouts/AdminLayout.vue` para que apunte al nuevo dashboard:
    ```diff
    --- a/resources/js/Layouts/AdminLayout.vue
    +++ b/resources/js/Layouts/AdminLayout.vue
    @@ -23,7 +23,7 @@
                 <!-- Sidebar -->
                 <aside class="w-64 bg-gray-800 text-white p-6 space-y-6 hidden md:block">
                     <div class="text-2xl font-semibold text-center">
-                        <Link :href="route('admin.users.index')"> {/* O a un dashboard de admin */}
+                        <Link :href="route('admin.dashboard')">
                            Admin Panel
                            {/* <ApplicationMark class="block h-9 w-auto" />  Si tienes un logo */}
                        </Link>
    @@ -31,7 +31,7 @@
 
                     <nav class="mt-10">
                         <Link :href="route('admin.users.index')"
-                              :class="{ 'bg-gray-900 text-white': route().current('admin.users.*') }"
+                              :class="{ 'bg-gray-900 text-white': route().current('admin.users.*') || route().current('admin.dashboard') && route().current().uri.includes('users') }"
                              class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
                            Manage Users
                        </Link>
    ```
    *   **Nota sobre la clase activa del sidebar:** La lógica para la clase activa del enlace "Manage Users" puede necesitar ajustarse si el dashboard es la página principal. Una forma simple es que el dashboard tenga su propio enlace o que "Manage Users" se active si la ruta actual contiene `admin.users`.
*   `[ ]` **Verificación:**
    *   Iniciar sesión como administrador.
    *   Navegar a `/admin/dashboard`. Deberías ver la página del dashboard con el mensaje de bienvenida.
    *   El logo/título "Admin Panel" en el sidebar ahora debería enlazar a `/admin/dashboard`.

---
<!-- Siguientes pasos: Implementación de Políticas de Laravel para Usuarios -->

### 2.6. Implementación de Políticas de Laravel para Usuarios (`UserPolicy`)
*   **Contexto:** Queremos un control más granular sobre quién puede ver, crear, actualizar o eliminar usuarios. Por ejemplo, un administrador no debería poder eliminarse a sí mismo.
*   `[ ]` Generar una Policy para el modelo `User`:
    ```bash
    php artisan make:policy UserPolicy --model=User
    ```
    Esto creará `app/Policies/UserPolicy.php`.
*   `[ ]` Registrar la `UserPolicy` en `app/Providers/AuthServiceProvider.php`:
    ```php
    // app/Providers/AuthServiceProvider.php
    namespace App\Providers;

    // use Illuminate\Support\Facades\Gate; // Ya debería estar
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use App\Models\User; // IMPORTAR
    use App\Policies\UserPolicy; // IMPORTAR

    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * The policy mappings for the application.
         *
         * @var array<class-string, class-string>
         */
        protected $policies = [
            User::class => UserPolicy::class, // AÑADIR ESTA LÍNEA
        ];

        /**
         * Register any authentication / authorization services.
         */
        public function boot(): void
        {
            $this->registerPolicies();

            //
        }
    }
    ```
*   `[ ]` Implementar los métodos de la Policy en `app/Policies/UserPolicy.php`. Empezaremos con `viewAny`, `create`, `update`, y `delete`.
    ```php
    // app/Policies/UserPolicy.php
    namespace App\Policies;

    use App\Models\User;
    use Illuminate\Auth\Access\HandlesAuthorization; // O Response en L10+

    class UserPolicy
    {
        use HandlesAuthorization; // O sin esto y retornar Response::allow/deny en L10+

        /**
         * Determine whether the user can view any models.
         * El usuario autenticado ($adminUser) puede ver la lista de usuarios ($targetUser no se usa aquí).
         */
        public function viewAny(User $adminUser): bool
        {
            return $adminUser->role === 'admin';
        }

        /**
         * Determine whether the user can view the model.
         * El usuario autenticado ($adminUser) puede ver el perfil de otro usuario ($targetUser).
         */
        public function view(User $adminUser, User $targetUser): bool
        {
            return $adminUser->role === 'admin';
            // Podrías añadir lógica más compleja, ej: un admin solo puede ver usuarios de su 'nivel' o 'departamento'.
        }

        /**
         * Determine whether the user can create models.
         */
        public function create(User $adminUser): bool
        {
            return $adminUser->role === 'admin';
        }

        /**
         * Determine whether the user can update the model.
         * El usuario autenticado ($adminUser) puede actualizar a $targetUser.
         */
        public function update(User $adminUser, User $targetUser): bool
        {
            if ($adminUser->role !== 'admin') {
                return false;
            }
            // Un admin no puede editar su propio rol o estado directamente a través de este flujo (podría tener una página de perfil separada).
            // O, si es un superadmin, podría editar a otros admins pero no a sí mismo en ciertos campos.
            // if ($adminUser->id === $targetUser->id) {
            //     return false; // O permitir solo ciertos campos
            // }
            return true;
        }

        /**
         * Determine whether the user can delete the model.
         */
        public function delete(User $adminUser, User $targetUser): bool
        {
            if ($adminUser->role !== 'admin') {
                return false;
            }
            // Un admin no puede eliminarse a sí mismo.
            if ($adminUser->id === $targetUser->id) {
                return false;
            }
            // Podrías añadir lógica para no permitir eliminar al último admin, etc.
            return true;
        }

        /**
         * Determine whether the user can restore the model.
         */
        public function restore(User $adminUser, User $targetUser): bool
        {
            return $adminUser->role === 'admin';
        }

        /**
         * Determine whether the user can permanently delete the model.
         */
        public function forceDelete(User $adminUser, User $targetUser): bool
        {
            return $adminUser->role === 'admin'; // Y quizás solo un superadmin
        }
    }
    ```
*   `[ ]` **Verificación (Conceptual):** Las políticas están definidas. Ahora necesitamos aplicarlas en los controladores.

### 2.7. Aplicar `UserPolicy` en `Admin\UserController`
*   `[ ]` En `app/Http/Controllers/Admin/UserController.php`, usar los métodos de la policy para autorizar acciones.
    *   **En `index()`:**
        ```php
        // Dentro de AdminUserController.php
        public function index()
        {
            $this->authorize('viewAny', User::class); // AÑADIR ESTO
            $users = User::latest()->paginate(10);
            return Inertia::render('Admin/Users/Index', ['users' => $users]);
        }
        ```
    *   **En `create()`:**
        ```php
        public function create()
        {
            $this->authorize('create', User::class); // AÑADIR ESTO
            return Inertia::render('Admin/Users/Create');
        }
        ```
    *   **En `store()`:** (La autorización se maneja en el FormRequest `StoreUserRequest` o se puede duplicar aquí si se prefiere)
        ```php
        // En StoreUserRequest.php, el método authorize() ya debería estar:
        // public function authorize(): bool { return $this->user()->can('create', User::class); }
        // O si no, en el controlador:
        public function store(StoreUserRequest $request): RedirectResponse
        {
            // $this->authorize('create', User::class); // Si no está en el FormRequest
            // ... (resto del método store)
        }
        ```
    *   **En `edit(User $user)`:**
        ```php
        public function edit(User $user)
        {
            $this->authorize('update', $user); // AÑADIR ESTO (o 'view' si es solo para ver el form)
            return Inertia::render('Admin/Users/Edit', ['user' => $user]);
        }
        ```
    *   **En `update(UpdateUserRequest $request, User $user)`:** (La autorización se maneja en `UpdateUserRequest` o se duplica)
        ```php
        // En UpdateUserRequest.php, el método authorize() ya debería estar:
        // public function authorize(): bool { return $this->user()->can('update', $this->route('user')); }
        // O si no, en el controlador:
        public function update(UpdateUserRequest $request, User $user): RedirectResponse
        {
            // $this->authorize('update', $user); // Si no está en el FormRequest
            // ... (resto del método update)
        }
        ```
    *   **En `destroy(User $user)`:**
        ```php
        public function destroy(User $user): RedirectResponse
        {
            $this->authorize('delete', $user); // AÑADIR ESTO
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
        }
        ```
*   `[ ]` Actualizar los métodos `authorize()` en `StoreUserRequest.php` y `UpdateUserRequest.php`:
    ```php
    // En app/Http/Requests/Admin/StoreUserRequest.php
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    // En app/Http/Requests/Admin/UpdateUserRequest.php
    public function authorize(): bool
    {
        // $this->route('user') obtiene el modelo User bindeado a la ruta
        return $this->user()->can('update', $this->route('user'));
    }
    ```
*   `[ ]` **Verificación:**
    *   Intentar acceder a `/admin/users` como un usuario no administrador (si el middleware `admin` fallara por alguna razón, la policy `viewAny` debería impedirlo).
    *   Como administrador, intentar eliminar tu propia cuenta de usuario. La acción debería ser denegada por la policy `delete` (recibirás un 403 Forbidden).
    *   (Si implementas más lógica en las policies) Probar otros escenarios, como un admin intentando editar su propio rol (si lo restringes).
    *   **Nota:** Los botones de "Edit" y "Delete" en la vista `Index.vue` podrían ocultarse condicionalmente usando `$page.props.auth.user.can('update', user)` y `can('delete', user)` si pasas el usuario completo a la vista y tienes las policies bien configuradas. Esto es una mejora de UX.

---
**¡Políticas de Acceso para Usuarios Implementadas!**
Ahora tienes un control más fino sobre las acciones del CRUD de usuarios, basado en el usuario autenticado y el usuario objetivo.
Los siguientes pasos podrían incluir:
    - Desarrollo de los módulos de Productos, Órdenes, etc., para el panel de administración, aplicando policies similares.
    - Creación de los Layouts y rutas para los paneles de Cliente y Revendedor, con sus propios middlewares y policies.
    - Implementación de la funcionalidad "Ver Usuario" (método `show` y vista `Show.vue`), protegida por la policy `view`.
<!-- Siguientes pasos: Implementación de Políticas de Laravel para Usuarios -->
```
