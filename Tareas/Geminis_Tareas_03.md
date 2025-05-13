# Geminis - Plan de Tareas Detallado - Parte 03

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la creación de un Layout para el panel de administración y la implementación de middlewares de autenticación y roles.

## Fase 2: Mejoras en el Panel de Administración y Seguridad Básica

**Objetivo:** Crear una estructura de navegación consistente para el panel de administración y proteger las rutas administrativas.

### 2.1. Creación de un Layout Básico para el Panel de Administración (`AdminLayout.vue`)
*   **Contexto:** Actualmente, nuestras páginas de administración de usuarios (`Index.vue`, `Create.vue`, `Edit.vue`) no tienen una estructura de navegación común (como un sidebar o un navbar). Vamos a crear un layout reutilizable.
*   `[ ]` Crear el directorio `resources/js/Layouts/` si no existe (Breeze ya debería haberlo creado con `AuthenticatedLayout.vue` y `GuestLayout.vue`).
*   `[ ]` Crear el archivo `resources/js/Layouts/AdminLayout.vue`.
*   `[ ]` Implementar una estructura básica en `AdminLayout.vue`. Incluirá un slot para el contenido de la página, y placeholders para un futuro sidebar y un header/navbar.
    ```vue
    // resources/js/Layouts/AdminLayout.vue
    <script setup>
    import { ref } from 'vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    // Podrías importar componentes de Breeze como ApplicationLogo, Dropdown, etc., si quieres reutilizarlos.
    // import ApplicationMark from '@/Components/ApplicationMark.vue'; // Ejemplo si usas Jetstream/Fortify logo
    // import Banner from '@/Components/Banner.vue';
    // import Dropdown from '@/Components/Dropdown.vue';
    // import DropdownLink from '@/Components/DropdownLink.vue';
    // import NavLink from '@/Components/NavLink.vue';
    // import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

    defineProps({
        title: String,
    });

    const showingNavigationDropdown = ref(false);

    const switchToTeam = (team) => {
        router.put(route('current-team.update'), {
            team_id: team.id,
        }, {
            preserveState: false,
        });
    };

    const logout = () => {
        router.post(route('logout'));
    };
    </script>

    <template>
        <div>
            <Head :title="title" />

            <!-- <Banner /> --> {/* Si usas Jetstream Banner */}

            <div class="min-h-screen bg-gray-100 flex">
                <!-- Sidebar -->
                <aside class="w-64 bg-gray-800 text-white p-6 space-y-6 hidden md:block">
                    <div class="text-2xl font-semibold text-center">
                        <Link :href="route('admin.users.index')"> {/* O a un dashboard de admin */}
                            Admin Panel
                            {/* <ApplicationMark class="block h-9 w-auto" />  Si tienes un logo */}
                        </Link>
                    </div>

                    <nav class="mt-10">
                        <Link :href="route('admin.users.index')"
                              :class="{ 'bg-gray-900 text-white': route().current('admin.users.*') }"
                              class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
                            Manage Users
                        </Link>
                        {/* <!-- Otros enlaces del sidebar aquí --> */}
                        <Link href="#"
                              class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2">
                            Products (Placeholder)
                        </Link>
                        <Link href="#"
                              class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2">
                            Settings (Placeholder)
                        </Link>
                    </nav>
                </aside>

                <!-- Main content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                            <h2 v-if="$slots.header" class="font-semibold text-xl text-gray-800 leading-tight">
                                <slot name="header" />
                            </h2>
                            <div v-else class="font-semibold text-xl text-gray-800 leading-tight">
                                {{ title }}
                            </div>

                            <!-- Settings Dropdown (adaptado de AuthenticatedLayout de Breeze/Jetstream) -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <div class="ml-3 relative">
                                    <!-- <Dropdown align="right" width="48"> --> {/* Si usas el componente Dropdown */}
                                        <button v-if="$page.props.jetstream?.canCreateTeams || $page.props.auth.user" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                            {{ $page.props.auth.user?.name }}

                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    <!--
                                        <template #content>
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Account
                                            </div>
                                            <DropdownLink :href="route('profile.show')"> Profile </DropdownLink>
                                            <DropdownLink v-if="$page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')"> API Tokens </DropdownLink>
                                            <div class="border-t border-gray-200" />
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button"> Log Out </DropdownLink>
                                            </form>
                                        </template>
                                    </Dropdown>
                                    -->
                                    <button @click="logout" class="ml-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        Log Out (Placeholder)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </template>
    ```
    *   **Nota:** Este layout es una mezcla de un sidebar simple y un header que podría tener un dropdown de usuario similar al de Breeze/Jetstream. Puedes simplificarlo o adaptarlo según tus necesidades. El botón de "Log Out" es un placeholder por ahora.
*   `[ ]` **Verificación:** El archivo `AdminLayout.vue` existe y tiene la estructura básica.

### 2.2. Integrar `AdminLayout.vue` en las Vistas de Usuarios
*   `[ ]` Modificar `resources/js/Pages/Admin/Users/Index.vue`:
    *   Importar `AdminLayout`.
    *   Envolver el contenido del `<template>` con `<AdminLayout>`.
    *   Pasar el `title` como prop a `AdminLayout`.
    ```vue
    // resources/js/Pages/Admin/Users/Index.vue
    <script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue'; // IMPORTAR
    // ... (resto del script setup)

    defineProps({
      users: Object,
      // flash: Object, // Si manejas flash messages aquí
    });
    </script>

    <template>
      <AdminLayout title="Manage Users"> {/* USAR LAYOUT Y PASAR TÍTULO */}
        <Head title="Manage Users" /> {/* Head puede seguir aquí o solo en el Layout */}

        {/* El contenido anterior de Index.vue va aquí, dentro del slot por defecto de AdminLayout */}
        <div class="py-0"> {/* Ajustar padding si el layout ya lo maneja */}
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {/* ... (resto del contenido de la tabla de usuarios, etc.) ... */}
            {/* Asegúrate de que el botón "Create User" y la tabla estén dentro de este div o del slot del layout */}
          </div>
        </div>
      </AdminLayout>
    </template>
    ```
*   `[ ]` Modificar `resources/js/Pages/Admin/Users/Create.vue` de forma similar:
    ```vue
    // resources/js/Pages/Admin/Users/Create.vue
    <script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue'; // IMPORTAR
    // ... (resto del script setup)
    </script>

    <template>
      <AdminLayout title="Create User"> {/* USAR LAYOUT Y PASAR TÍTULO */}
        <Head title="Create User" />
        {/* ... (contenido del formulario de creación aquí) ... */}
      </AdminLayout>
    </template>
    ```
*   `[ ]` Modificar `resources/js/Pages/Admin/Users/Edit.vue` de forma similar:
    ```vue
    // resources/js/Pages/Admin/Users/Edit.vue
    <script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue'; // IMPORTAR
    // ... (resto del script setup)
    </script>

    <template>
      <AdminLayout :title="'Edit User - ' + user.name"> {/* USAR LAYOUT Y PASAR TÍTULO */}
        <Head :title="'Edit User - ' + user.name" />
        {/* ... (contenido del formulario de edición aquí) ... */}
      </AdminLayout>
    </template>
    ```
*   `[ ]` **Verificación:**
    *   Navegar a `/admin/users`, `/admin/users/create`, y `/admin/users/{id}/edit`.
    *   Todas estas páginas deberían ahora mostrar la estructura del `AdminLayout` (el sidebar y el header placeholders) con el contenido específico de la página dentro del área principal.
    *   Los enlaces del sidebar (ej: "Manage Users") deberían funcionar y resaltar la sección activa (si implementaste la clase activa).

---
<!-- Siguientes pasos: Middlewares de Autenticación y Roles -->
```

<!-- Siguientes pasos: Middlewares de Autenticación y Roles -->

### 2.3. Aplicar Middleware de Autenticación a las Rutas de Administración
*   **Contexto:** Actualmente, las rutas `/admin/*` son públicas. Necesitamos asegurar que solo los usuarios autenticados puedan acceder.
*   `[ ]` En `routes/web.php`, modificar el grupo de rutas del administrador para incluir el middleware `auth` de Laravel (Breeze ya lo configura).
    ```php
    // routes/web.php
    use App\Http\Controllers\Admin\UserController as AdminUserController;
    // ... otras importaciones

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () { // AÑADIR MIDDLEWARE 'auth' y 'verified'
        Route::resource('users', AdminUserController::class);
        // Aquí irán otras rutas del panel de administración
        // Ejemplo: Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });

    // Asegúrate de que las rutas de autenticación de Breeze (login, register, etc.) estén fuera de este grupo de admin.
    // require __DIR__.'/auth.php'; // Breeze usualmente pone esto al final
    ```
    *   El middleware `verified` es opcional pero recomendado si quieres que los usuarios verifiquen su email antes de acceder al panel. Breeze también lo configura.
*   `[ ]` **Verificación:**
    *   Cerrar sesión si estabas logueado.
    *   Intentar acceder a `/admin/users`. Deberías ser redirigido a la página de login (`/login`).
    *   Iniciar sesión con un usuario existente. Ahora deberías poder acceder a `/admin/users`.

### 2.4. Crear y Aplicar Middleware de Rol de Administrador (`EnsureUserIsAdmin`)
*   **Contexto:** Solo los usuarios con `role = 'admin'` deberían poder acceder al panel de administración.
*   `[ ]` Crear el middleware:
    ```bash
    php artisan make:middleware EnsureUserIsAdmin
    ```
    Esto creará `app/Http/Middleware/EnsureUserIsAdmin.php`.
*   `[ ]` Implementar la lógica en `app/Http/Middleware/EnsureUserIsAdmin.php`:
    ```php
    // app/Http/Middleware/EnsureUserIsAdmin.php
    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response; // Importar Response
    use Illuminate\Support\Facades\Auth; // Importar Auth

    class EnsureUserIsAdmin
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next): Response
        {
            if (Auth::check() && Auth::user()->role === 'admin') {
                return $next($request);
            }

            // Si no es admin, redirigir o abortar.
            // Opción 1: Redirigir al dashboard general (si existe) o a la home.
            // return redirect('/dashboard')->with('error', 'You do not have admin access.');
            // Opción 2: Abortar con un error 403 Forbidden.
            abort(403, 'Unauthorized action. Admin access required.');
        }
    }
    ```
*   `[ ]` Registrar el middleware en `app/Http/Kernel.php` dentro del array `$routeMiddleware` (o `$middlewareAliases` en Laravel 10+):
    ```php
    // app/Http/Kernel.php
    protected $routeMiddleware = [ // o $middlewareAliases en L10+
        // ... otros middlewares
        'auth' => \App\Http\Middleware\Authenticate::class, // Ejemplo, ya debería estar
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class, // AÑADIR ESTA LÍNEA
        // ...
    ];
    ```
*   `[ ]` Aplicar el middleware `admin` al grupo de rutas de administración en `routes/web.php`:
    ```php
    // routes/web.php
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () { // AÑADIR MIDDLEWARE 'admin'
        Route::resource('users', AdminUserController::class);
        // ...
    });
    ```
*   `[ ]` **Verificación:**
    *   Crear (o modificar un usuario existente en la BD) para que tenga `role = 'client'` o `role = 'reseller'`.
    *   Iniciar sesión con este usuario no administrador.
    *   Intentar acceder a `/admin/users`. Deberías ver un error 403 Forbidden (o ser redirigido, según lo que hayas configurado en el middleware).
    *   Modificar el usuario en la BD para que tenga `role = 'admin'`.
    *   Cerrar sesión y volver a iniciar sesión con este usuario administrador.
    *   Ahora deberías poder acceder a `/admin/users` sin problemas.

---
**¡Panel de Administración con Layout y Seguridad Básica Implementados!**
En este punto, el panel de administración tiene una estructura visual consistente y está protegido para que solo usuarios autenticados con el rol de 'admin' puedan acceder.
Los siguientes pasos podrían incluir:
    - Creación de un Dashboard para el Admin.
    - Implementación de Políticas de Laravel para un control de acceso más granular dentro del CRUD de usuarios (ej: un admin no puede eliminarse a sí mismo).
    - Desarrollo de los módulos de Productos, Órdenes, etc., para el panel de administración.
    - Creación de los Layouts y rutas para los paneles de Cliente y Revendedor.
```

Con esto, `Geminis_Tareas_03.md` estaría completo, cubriendo la creación del layout de administración y la seguridad básica de sus rutas. ¡Avísame cuando quieras continuar con el siguiente archivo o tema!
