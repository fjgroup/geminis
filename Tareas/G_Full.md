# Geminis - Plan de Tareas Detallado

Este documento detalla el paso a paso para la construcción del sistema Geminis. Cada tarea debe ser completada y verificada antes de pasar a la siguiente.

## Fase -1: Control de Versiones (Git y GitHub)

Objetivo:\*\* Establecer el control de versiones para el proyecto y asegurar que los cambios se registren regularmente.

**Inicializar Repositorio Git (si no existe):**
Dentro del directorio raíz de tu proyecto (`hostgemini`), ejecuta:

```bash
git status
git add
git status
git comit - m segunda version
git push
```

**Crear Repositorio en GitHub (o similar):**
Ve a GitHub (o tu plataforma de preferencia) y crea un nuevo repositorio (puede ser privado o público).
Sigue las instrucciones de GitHub para conectar tu repositorio local al remoto. Generalmente implica comandos como:

```bash
git remote add origin URL_DEL_REPOSITORIO_REMOTO
git branch -M main
git push -u origin main
```

**Recordatorio Constante:** Antes de cada modificación importante, instalación de paquetes nuevos, o al finalizar una sección de tareas (ej. al final de la Fase 0, o antes de empezar la Fase 1), **realiza un commit y un push a tu repositorio remoto.**
Ejemplo de flujo de commit:

```bash
git add .
git commit -m "Descripción clara de los cambios realizados"
git push origin main
```

Este documento detalla el paso a paso para la construcción del sistema Geminis. Cada tarea debe ser completada y verificada antes de pasar a la siguiente.

## Fase 0: Configuración Inicial del Proyecto

Objetivo:\*\* Tener un proyecto Laravel funcional con las herramientas base (Vue, Inertia) y la base de datos conectada.

### 0.1. Creación del Proyecto Laravel (Si es nuevo)

Si aún no existe, crear un nuevo proyecto Laravel:

```bash
composer create-project laravel/laravel hostgemini
cd hostgemini
```

**Verificación:** Acceder a la URL base del proyecto en el navegador y ver la página de bienvenida de Laravel.

### 0.2. Configuración del Entorno

Copiar `.env.example` a `.env` si no existe.
Configurar las variables de entorno en `.env`:
`APP_NAME="HostGemini"` (o el nombre que prefieras)
`APP_URL=http://hostgemini.test` (o tu URL de desarrollo local)
`DB_CONNECTION=mysql`
`DB_HOST=127.0.0.1`
`DB_PORT=3306`
`DB_DATABASE=hostgemini` (crea esta base de datos vacía en tu MySQL)
`DB_USERNAME=root` (o tu usuario de BD)
`DB_PASSWORD=` (o tu contraseña de BD)
Generar la clave de aplicación:

```bash
php artisan key:generate
```

Ejecutar las migraciones iniciales de Laravel (si las hay por defecto):

```bash
php artisan migrate
```

**Verificación:** El comando `php artisan migrate` se ejecuta sin errores. Puedes conectar a tu base de datos `hostgemini` y ver las tablas creadas por Laravel (users, password_resets, etc., si no las eliminaste).

### 0.3. Instalación de Laravel Breeze o Jetstream (para Autenticación Base e Inertia/Vue)

Decisión:\*\* Usaremos Laravel Breeze con la opción de Inertia + Vue. Es más ligero para empezar.
Instalar Laravel Breeze:

```bash
composer require laravel/breeze --dev
```

Instalar el scaffolding de Breeze con Vue + Inertia:

```bash
php artisan breeze:install vue --ssr
# O sin SSR si no lo necesitas inicialmente:
# php artisan breeze:install vue
```

Instalar dependencias NPM:

```bash
npm install
```

Compilar los assets:

```bash
npm run dev
# O npm run build para producción
```

Ejecutar las migraciones (Breeze añade algunas):

```bash
php artisan migrate
```

**Verificación:**
Visitar `/login` en el navegador. Deberías ver el formulario de login de Breeze.
Visitar `/register`. Deberías ver el formulario de registro.
Poder registrar un nuevo usuario y ser redirigido al `/dashboard`.
La tabla `users` en la base de datos ahora tiene los campos que Breeze necesita (name, email, password, etc.).

### 0.4. Limpieza Inicial de Rutas y Vistas de Breeze (Opcional, para nuestro enfoque)

Contexto:\*\* Dado que construiremos los paneles de admin, reseller y client por separado y la autenticación se manejará de forma más específica después, podemos simplificar las rutas y vistas que Breeze genera por defecto si interfieren con el desarrollo público inicial. Por ahora, las dejaremos, ya que el login único es un objetivo.

## Fase 1: Módulo de Usuarios (CRUD Básico - Panel de Administración)

Objetivo:\*\* Crear la gestión completa (Listar, Crear, Ver, Editar, Eliminar) para los usuarios desde un futuro panel de administración. Por ahora, las rutas serán públicas para facilitar el desarrollo.

### 1.1. Migración de la Tabla `users`

Contexto:\*\* La migración de `users` ya fue creada por Laravel y modificada por Breeze. Necesitamos ajustarla según nuestro `Geminis_Estructura.md`.
Localizar el archivo de migración `..._create_users_table.php` en `database/migrations/`.
Modificar el método `up()` de la migración para que coincida con la definición de la tabla `users` en `Geminis_Estructura.md`. Asegúrate de incluir todos los campos: `role`, `reseller_id`, `company_name`, `phone_number`, direcciones, `status`, `language_code`, `currency_code`, `last_login_at`, y `deleted_at` (softDeletes).
Ejemplo de cómo añadir `role` y `reseller_id`:

```php
// Dentro de Schema::create('users', function (Blueprint $table) { ...
$table->string('name');
$table->string('email')->unique();
$table->timestamp('email_verified_at')->nullable();
$table->string('password');
// NUEVOS CAMPOS SEGÚN Geminis_Estructura.md
$table->enum('role', ['admin', 'client', 'reseller'])->default('client'); // O el default que prefieras
$table->foreignId('reseller_id')->nullable()->constrained('users')->onDelete('set null'); // Ojo: users debe existir, o ajustar. Por ahora, puede ser solo ->nullable();
$table->string('company_name')->nullable();
$table->string('phone_number')->nullable();
// ... (resto de campos: address_line1, etc.)
$table->string('address_line1')->nullable();
$table->string('address_line2')->nullable();
$table->string('city')->nullable();
$table->string('state_province')->nullable();
$table->string('postal_code')->nullable();
$table->string('country_code', 2)->nullable();
$table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
$table->string('language_code', 10)->default('es');
$table->string('currency_code', 3)->default('USD');
$table->timestamp('last_login_at')->nullable();
$table->rememberToken();
$table->timestamps();
$table->softDeletes(); // Para borrado lógico
// })
```

Nota sobre `reseller_id` FK:\*\* Si `reseller_id` es una FK a `users.id`, la tabla `users` ya existe. Si hay problemas con la auto-referencia en la misma migración, se puede añadir la constraint en una migración separada después. Por simplicidad inicial, podrías solo definirlo como `->nullable()->unsignedBigInteger('reseller_id')` y añadir el índice y la FK después. Por ahora, usaremos `->constrained('users')` asumiendo que el SGBD lo maneja bien o lo ajustaremos.
Si ya ejecutaste migraciones y necesitas rehacer la de `users`:

```bash
php artisan migrate:fresh # ¡CUIDADO! Esto borra TODA la base de datos.
# O, si quieres ser más selectivo, puedes hacer rollback de la última y reintentar.
# php artisan migrate:rollback --step=1
# php artisan migrate
```

**Verificación:**
El comando `php artisan migrate` (o `migrate:fresh`) se ejecuta sin errores.
Inspeccionar la estructura de la tabla `users` en tu cliente de base de datos. Debe tener todos los campos definidos en `Geminis_Estructura.md` con los tipos correctos.

### 1.2. Modelo `User`

Contexto:\*\* El modelo `App\Models\User.php` ya existe. Necesitamos actualizarlo.
Abrir `app/Models/User.php`.
Asegurar que la trait `HasFactory` y `Notifiable` estén presentes. Añadir `SoftDeletes`.

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // AÑADIR ESTO

class User extends Authenticatable
{
use HasFactory, Notifiable, SoftDeletes; // AÑADIR SoftDeletes
// ...
}
```

Actualizar la propiedad `$fillable` para incluir los nuevos campos que queremos que sean asignables masivamente.

```php
protected $fillable = [
'name',
'email',
'password',
'role', // Añadir
'reseller_id', // Añadir
'company_name', // Añadir
'phone_number', // Añadir
'address_line1', // Añadir
'address_line2', // Añadir
'city', // Añadir
'state_province', // Añadir
'postal_code', // Añadir
'country_code', // Añadir
'status', // Añadir
'language_code', // Añadir
'currency_code', // Añadir
'last_login_at', // Añadir
];
```

Actualizar la propiedad `$hidden` si es necesario (generalmente `password` y `remember_token` ya están).
Actualizar la propiedad `$casts` para los campos que lo necesiten (ej: `email_verified_at` a `datetime`, `last_login_at` a `datetime`). Los ENUM no necesitan cast explícito aquí, pero las validaciones se encargarán de los valores permitidos.

```php
protected $casts = [
'email_verified_at' => 'datetime',
'last_login_at' => 'datetime', // Añadir
'password' => 'hashed', // Breeze ya lo pone
];
```

(Opcional por ahora, pero bueno para el futuro) Definir relaciones Eloquent. Por ejemplo, si un usuario es un revendedor, podría tener muchos clientes:

```php
// En User.php
public function clients()
{
return $this->hasMany(User::class, 'reseller_id');
}

public function reseller()
{
return $this->belongsTo(User::class, 'reseller_id');
}
```

**Verificación:**
Abrir `php artisan tinker`.
Intentar crear un usuario con los nuevos campos:

```php
App\Models\User::create([
'name' => 'Admin User',
'email' => 'admin@example.com',
'password' => bcrypt('password'),
'role' => 'admin',
'status' => 'active'
// ... otros campos fillable
]);
```

Verificar que el usuario se crea en la base de datos con los valores correctos.
`App\Models\User::first()->toArray();` debe mostrar los campos.

### 1.3. Controlador de Usuarios para Administración (`Admin\UserController`)

Crear un controlador resource para la gestión de usuarios en el panel de administración:

```bash
php artisan make:controller Admin/UserController --resource --model=User
```

Esto creará `app/Http/Controllers/Admin/UserController.php` con los métodos `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
**Verificación:** El archivo del controlador existe en la ruta especificada y contiene los métodos esperados.

### 1.4. Rutas para el CRUD de Usuarios (Administración)

Abrir `routes/web.php`.
Definir las rutas resource para el `Admin\UserController`. Por ahora, sin middleware de autenticación o rol para facilitar el desarrollo.

```php

use App\Http\Controllers\Admin\UserController as AdminUserController; // Alias para evitar colisiones si hay otros UserController


// ... otras rutas

// Rutas para la gestión de Usuarios (Admin)
Route::prefix('admin')->name('admin.')->group(function () {
Route::resource('users', AdminUserController::class);
});

```

Nota:\*\* La ruta base será `/admin/users`. Los middlewares de autenticación y rol se aplicarán a este grupo de rutas más adelante.

**Verificación:**
Ejecutar `php artisan route:list`.
Verificar que las rutas para `admin.users.index`, `admin.users.create`, etc., estén listadas y apunten a `Admin\UserController`.

### 1.5. Vista de Listado de Usuarios (`resources/js/Pages/Admin/Users/Index.vue`)

En el método `index` de `Admin\UserController.php`, obtener todos los usuarios y pasarlos a una vista Inertia.

```php
// En Admin/UserController.php
use App\Models\User;
use Inertia\Inertia;

public function index()
{
$users = User::latest()->paginate(10); // Paginado
return Inertia::render('Admin/Users/Index', [
'users' => $users,
]);
}
```

Crear el directorio `resources/js/Pages/Admin/Users/`.
Crear el archivo `resources/js/Pages/Admin/Users/Index.vue`.
Implementar una tabla básica para mostrar los usuarios (ID, Nombre, Email, Rol, Status). Usar Tailwind CSS para estilos básicos. (El código Vue se proporcionó en la conversación anterior, puedes copiarlo desde allí).
**Verificación:**
Asegúrate de que `npm run dev` esté ejecutándose.
Navegar a `/admin/users` en tu navegador.
Deberías ver la tabla con los usuarios (si creaste alguno en el paso 1.2) o "No users found".
La paginación debería funcionar si tienes más de 10 usuarios.
El botón "Create User" debería estar visible y llevar a `admin.users.create` (aunque la página de creación aún no exista).

---

<!-- Siguientes pasos: Crear formulario, validaciones, lógica de store, edit, update, delete para Usuarios  Geminis_Tareas_02 -->

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en completar el CRUD para el módulo de Usuarios en el panel de administración.

### 1.6. Vista y Formulario de Creación de Usuarios (`resources/js/Pages/Admin/Users/Create.vue`)

En `Admin/UserController.php`, implementar el método `create()`:

```php
// En app/Http/Controllers/Admin/UserController.php
// Asegúrate de que Inertia esté importado: use Inertia\Inertia;

public function create()
{
// Podríamos pasar datos adicionales si fueran necesarios (ej: listas para selects)
// Por ejemplo, si los roles, países, etc., vinieran de la BD o de un config:
// $roles = [['value' => 'admin', 'label' => 'Admin'], /* ... */];
return Inertia::render('Admin/Users/Create'/*, ['roles' => $roles]*/);
}
```

Crear el archivo `resources/js/Pages/Admin/Users/Create.vue`.
Implementar el formulario de creación en `Create.vue`. Incluir campos para: `name`, `email`, `password`, `password_confirmation`, `role` (select), `reseller_id` (input numérico, podría ser un select de revendedores más adelante), `company_name`, `phone_number`, `address_line1`, `address_line2`, `city`, `state_province`, `postal_code`, `country_code` (select o input), `status` (select), `language_code` (select o input), `currency_code` (select o input).
Usar el componente `useForm` de Inertia para manejar el estado del formulario y los envíos.
Aplicar estilos básicos con Tailwind CSS.

```vue
// resources/js/Pages/Admin/Users/Create.vue
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
// Si tienes un layout de Admin, impórtalo:
// import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumiendo que lo crearás
// Importar componentes de formulario reutilizables si los tienes (ej: InputLabel, TextInput, PrimaryButton, SelectInput)
// Por ahora, usaremos elementos HTML estándar con clases de Tailwind.

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "client", // Default role
  reseller_id: null,
  company_name: "",
  phone_number: "",
  address_line1: "",
  address_line2: "",
  city: "",
  state_province: "",
  postal_code: "",
  country_code: "+58", // Default country (ej. +58 es Venezuela, agregar los codigo de todos los paises)
  status: "active", // Default status
  language_code: "es", // Default language
  currency_code: "USD", // Default currency
});

const submit = () => {
  form.post(route("admin.users.store"), {
    onFinish: () => {
      // No reseteamos todo el form para que los errores se mantengan si los hay
      // Solo reseteamos campos sensibles como password si el envío fue exitoso (se maneja mejor con onSuccess)
    },
    onSuccess: () => {
      form.reset("password", "password_confirmation");
    },
  });
};

// Opciones para selects (ejemplos, podrías obtenerlos de props o constantes globales)
const roleOptions = [
  { value: "admin", label: "Admin" },
  { value: "client", label: "Client" },
  { value: "reseller", label: "Reseller" },
];
const statusOptions = [
  { value: "active", label: "Active" },
  { value: "inactive", label: "Inactive" },
  { value: "suspended", label: "Suspended" },
];
// TODO: Considerar cargar listas de países, idiomas, monedas dinámicamente o desde un archivo de configuración.
</script>

<template>
  <!-- <AdminLayout> -->
  <Head title="Create User" />

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 md:p-8">
          <h1 class="mb-6 text-2xl font-semibold">Create New User</h1>
          <form @submit.prevent="submit">
            <!-- Name -->
            <div class="mb-4">
              <label
                for="name"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Name <span class="text-red-500">*</span></label
              >
              <input
                type="text"
                v-model="form.name"
                id="name"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
              </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
              <label
                for="email"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Email <span class="text-red-500">*</span></label
              >
              <input
                type="email"
                v-model="form.email"
                id="email"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-2">
              <div>
                <label
                  for="password"
                  class="block mb-1 text-sm font-medium text-gray-700"
                  >Password <span class="text-red-500">*</span></label
                >
                <input
                  type="password"
                  v-model="form.password"
                  id="password"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                <div
                  v-if="form.errors.password"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ form.errors.password }}
                </div>
              </div>
              <div>
                <label
                  for="password_confirmation"
                  class="block mb-1 text-sm font-medium text-gray-700"
                  >Confirm Password <span class="text-red-500">*</span></label
                >
                <input
                  type="password"
                  v-model="form.password_confirmation"
                  id="password_confirmation"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>

            <!-- Role -->
            <div class="mb-4">
              <label
                for="role"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Role <span class="text-red-500">*</span></label
              >
              <select
                v-model="form.role"
                id="role"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option
                  v-for="option in roleOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                {{ form.errors.role }}
              </div>
            </div>

            <!-- Reseller ID (Conditional) -->
            <div class="mb-4" v-if="form.role === 'client'">
              <label
                for="reseller_id"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Reseller ID (if client of a reseller)</label
              >
              <input
                type="number"
                v-model="form.reseller_id"
                id="reseller_id"
                placeholder="Leave empty if direct client"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div
                v-if="form.errors.reseller_id"
                class="mt-1 text-sm text-red-600"
              >
                {{ form.errors.reseller_id }}
              </div>
            </div>

            <!-- Company Name -->
            <div class="mb-4">
              <label
                for="company_name"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Company Name</label
              >
              <input
                type="text"
                v-model="form.company_name"
                id="company_name"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div
                v-if="form.errors.company_name"
                class="mt-1 text-sm text-red-600"
              >
                {{ form.errors.company_name }}
              </div>
            </div>

            {/* TODO: Add other fields: phone_number, address_line1,
            address_line2, city, state_province, postal_code, country_code,
            language_code, currency_code, siguiendo el mismo patrón */}

            <!-- Status -->
            <div class="mb-4">
              <label
                for="status"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Status <span class="text-red-500">*</span></label
              >
              <select
                v-model="form.status"
                id="status"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option
                  v-for="option in statusOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                {{ form.errors.status }}
              </div>
            </div>

            <div
              class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200"
            >
              <Link
                :href="route('admin.users.index')"
                class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50"
                >Cancel</Link
              >
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ form.processing ? "Creating..." : "Create User" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- </AdminLayout> -->
</template>
```

**Verificación:**
Navegar a `/admin/users/create` en tu navegador.
Deberías ver el formulario de creación de usuarios con todos los campos visibles (o condicionalmente visibles como `reseller_id`).
El botón "Create User" debería estar visible.
El botón "Cancel" debería llevar de vuelta a `/admin/users`.

### 1.7. Lógica de Almacenamiento de Usuarios (`store` en `Admin\UserController`) y Validación

Crear un Form Request para la validación de la creación de usuarios:

```bash
php artisan make:request Admin/StoreUserRequest
```

En `app/Http/Requests/Admin/StoreUserRequest.php`:
Poner `authorize()` a `true` (o implementar lógica de autorización si es necesario más adelante, por ejemplo, `return auth()->user()->isAdmin();` si tuvieras un método `isAdmin()` en el modelo User o usaras roles de Spatie).
Definir las reglas de validación en el método `rules()` para todos los campos del formulario, según `Geminis_Estructura.md`.

```php
// app/Http/Requests/Admin/StoreUserRequest.php
namespace App\Http\Requests\Admin; // Asegurar el namespace correcto

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules; // Para Password rule
use Illuminate\Validation\Rule; // Para Rule::in

class StoreUserRequest extends FormRequest
{
public function authorize(): bool
{
return true; // TODO: Implementar autorización real con Policies
}

public function rules(): array
{
return [
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
'password' => ['required', 'confirmed', Rules\Password::defaults()],
'role' => ['required', Rule::in(['admin', 'client', 'reseller'])],
'reseller_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(function ($query) {
// Opcional: asegurar que el reseller_id es realmente un reseller si se proporciona
// return $query->where('role', 'reseller');
})],
'company_name' => ['nullable', 'string', 'max:255'],
'phone_number' => ['nullable', 'string', 'max:255'],
'address_line1' => ['nullable', 'string', 'max:255'],
'address_line2' => ['nullable', 'string', 'max:255'],
'city' => ['nullable', 'string', 'max:255'],
'state_province' => ['nullable', 'string', 'max:255'],
'postal_code' => ['nullable', 'string', 'max:255'],
'country_code' => ['nullable', 'string', 'max:2'], // Podría ser Rule::in si tienes una lista fija de países
'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
'language_code' => ['nullable', 'string', 'max:10'], // Podría ser Rule::in
'currency_code' => ['nullable', 'string', 'max:3'], // Podría ser Rule::in
];
}
}
```

En `Admin/UserController.php`, actualizar el método `store()` para usar `StoreUserRequest` y crear el usuario:

```php
// En app/Http/Controllers/Admin/UserController.php
// Asegúrate de importar:
// use App\Models\User; // Ya debería estar
use App\Http\Requests\Admin\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

// ...

public function store(StoreUserRequest $request): RedirectResponse
{
$validatedData = $request->validated();

User::create([
'name' => $validatedData['name'],
'email' => $validatedData['email'],
'password' => Hash::make($validatedData['password']),
'role' => $validatedData['role'],
'reseller_id' => $validatedData['reseller_id'] ?? null,
'company_name' => $validatedData['company_name'] ?? null,
'phone_number' => $validatedData['phone_number'] ?? null,
'address_line1' => $validatedData['address_line1'] ?? null,
'address_line2' => $validatedData['address_line2'] ?? null,
'city' => $validatedData['city'] ?? null,
'state_province' => $validatedData['state_province'] ?? null,
'postal_code' => $validatedData['postal_code'] ?? null,
'country_code' => $validatedData['country_code'] ?? null,
'status' => $validatedData['status'],
'language_code' => $validatedData['language_code'] ?? 'es', // Default si es nullable y no se envía
'currency_code' => $validatedData['currency_code'] ?? 'USD', // Default si es nullable y no se envía
// 'email_verified_at' se puede manejar con un evento de 'registered' o manualmente si es necesario.
// 'last_login_at' se actualiza al hacer login.
]);

return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
}
```

**Verificación:**
Intentar enviar el formulario de creación vacío. Deberían aparecer los mensajes de error de validación debajo de cada campo correspondiente en la vista `Create.vue`.
Intentar crear un usuario con un email que ya existe. Debería mostrar el error de validación de email único.
Intentar crear un usuario con contraseñas que no coinciden. Debería mostrar el error de `password_confirmation`.
Crear un usuario válido. Debería ser redirigido a `/admin/users` con un mensaje de éxito.
Nota:\*\* Para ver el mensaje flash de éxito, necesitarás añadir lógica en tu layout principal o en `Index.vue` para mostrar los mensajes flash de Inertia. Ejemplo básico en `Index.vue` (o en un layout):

```vue
// En el <script setup> de Index.vue o tu layout principal
import { usePage } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

const page = usePage();
const successMessage = ref('');

watch(() => page.props.flash.success, (newMessage) => {
successMessage.value = newMessage;
if (newMessage) {
setTimeout(() => successMessage.value = '', 3000); // Ocultar después de 3s
}
}, { immediate: true });
```

Y en el `<template>`:

```html
<div
  v-if="successMessage"
  class="p-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded"
>
  {{ successMessage }}
</div>
```

Verificar que el nuevo usuario existe en la base de datos con la contraseña hasheada y todos los datos correctos.

---

<!-- Siguientes pasos: Formulario y lógica de Edición, Actualización y Eliminación para Usuarios -->

### 1.8. Vista y Formulario de Edición de Usuarios (`resources/js/Pages/Admin/Users/Edit.vue`)

En `Admin/UserController.php`, implementar el método `edit()`:

```php
// En app/Http/Controllers/Admin/UserController.php
// El modelo User e Inertia ya deberían estar importados.

public function edit(User $user) // Route-Model Binding
{
// Podríamos pasar datos adicionales si fueran necesarios (ej: listas para selects)
// $roles = [['value' => 'admin', 'label' => 'Admin'], /* ... */];
return Inertia::render('Admin/Users/Edit', [
'user' => $user, // Pasar el usuario completo a la vista
// 'roles' => $roles,
]);
}
```

Crear el archivo `resources/js/Pages/Admin/Users/Edit.vue`.
Implementar el formulario de edición en `Edit.vue`. Este formulario será muy similar al de `Create.vue`, pero se inicializará con los datos del usuario que se está editando.
Los campos de contraseña serán opcionales (solo se actualizan si se rellenan).
Usar el componente `useForm` de Inertia.
Aplicar estilos básicos con Tailwind CSS.

```vue
// resources/js/Pages/Admin/Users/Edit.vue
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
// import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumiendo que lo crearás

const props = defineProps({
  user: Object, // El usuario que se está editando, pasado desde el controlador
  // roles: Array, // Si pasas opciones de roles desde el controlador
});

const form = useForm({
  _method: "PUT", // Necesario para el envío de formularios de actualización con Inertia
  name: props.user.name,
  email: props.user.email,
  password: "", // Dejar vacío, solo se actualiza si se ingresa algo
  password_confirmation: "",
  role: props.user.role,
  reseller_id: props.user.reseller_id,
  company_name: props.user.company_name,
  phone_number: props.user.phone_number,
  address_line1: props.user.address_line1,
  address_line2: props.user.address_line2,
  city: props.user.city,
  state_province: props.user.state_province,
  postal_code: props.user.postal_code,
  country_code: props.user.country_code,
  status: props.user.status,
  language_code: props.user.language_code,
  currency_code: props.user.currency_code,
});

const submit = () => {
  form.post(route("admin.users.update", props.user.id), {
    // Usar form.post con _method: 'PUT'
    onSuccess: () => {
      form.reset("password", "password_confirmation");
    },
  });
};

// Opciones para selects (ejemplos, podrías obtenerlos de props o constantes globales)
const roleOptions = [
  { value: "admin", label: "Admin" },
  { value: "client", label: "Client" },
  { value: "reseller", label: "Reseller" },
];
const statusOptions = [
  { value: "active", label: "Active" },
  { value: "inactive", label: "Inactive" },
  { value: "suspended", label: "Suspended" },
];
// TODO: Considerar cargar listas de países, idiomas, monedas dinámicamente o desde un archivo de configuración.
</script>

<template>
  <!-- <AdminLayout> -->
  <Head :title="'Edit User - ' + user.name" />

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200 md:p-8">
          <h1 class="mb-6 text-2xl font-semibold">
            Edit User: {{ user.name }}
          </h1>
          <form @submit.prevent="submit">
            <!-- Name -->
            <div class="mb-4">
              <label
                for="name"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Name <span class="text-red-500">*</span></label
              >
              <input
                type="text"
                v-model="form.name"
                id="name"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
              </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
              <label
                for="email"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Email <span class="text-red-500">*</span></label
              >
              <input
                type="email"
                v-model="form.email"
                id="email"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Password (Optional) -->
            <div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-2">
              <div>
                <label
                  for="password"
                  class="block mb-1 text-sm font-medium text-gray-700"
                  >New Password (leave blank to keep current)</label
                >
                <input
                  type="password"
                  v-model="form.password"
                  id="password"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                <div
                  v-if="form.errors.password"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ form.errors.password }}
                </div>
              </div>
              <div>
                <label
                  for="password_confirmation"
                  class="block mb-1 text-sm font-medium text-gray-700"
                  >Confirm New Password</label
                >
                <input
                  type="password"
                  v-model="form.password_confirmation"
                  id="password_confirmation"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>

            <!-- Role -->
            <div class="mb-4">
              <label
                for="role"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Role <span class="text-red-500">*</span></label
              >
              <select
                v-model="form.role"
                id="role"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option
                  v-for="option in roleOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                {{ form.errors.role }}
              </div>
            </div>

            <!-- Reseller ID (Conditional) -->
            <div class="mb-4" v-if="form.role === 'client'">
              <label
                for="reseller_id"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Reseller ID (if client of a reseller)</label
              >
              <input
                type="number"
                v-model="form.reseller_id"
                id="reseller_id"
                placeholder="Leave empty if direct client"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div
                v-if="form.errors.reseller_id"
                class="mt-1 text-sm text-red-600"
              >
                {{ form.errors.reseller_id }}
              </div>
            </div>

            <!-- Company Name -->
            <div class="mb-4">
              <label
                for="company_name"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Company Name</label
              >
              <input
                type="text"
                v-model="form.company_name"
                id="company_name"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div
                v-if="form.errors.company_name"
                class="mt-1 text-sm text-red-600"
              >
                {{ form.errors.company_name }}
              </div>
            </div>

            {/* TODO: Add other fields: phone_number, address_line1,
            address_line2, city, state_province, postal_code, country,
            language_code, currency_code, siguiendo el mismo patrón que en
            Create.vue pero inicializados con props.user */}

            <!-- Status -->
            <div class="mb-4">
              <label
                for="status"
                class="block mb-1 text-sm font-medium text-gray-700"
                >Status <span class="text-red-500">*</span></label
              >
              <select
                v-model="form.status"
                id="status"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option
                  v-for="option in statusOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                {{ form.errors.status }}
              </div>
            </div>

            <div
              class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200"
            >
              <Link
                :href="route('admin.users.index')"
                class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50"
                >Cancel</Link
              >
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ form.processing ? "Updating..." : "Update User" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- </AdminLayout> -->
</template>
```

**Verificación:**
Desde la tabla de listado de usuarios (`/admin/users`), hacer clic en el enlace "Edit" de un usuario.
Deberías ser redirigido a `/admin/users/{id}/edit`.
El formulario de edición debería mostrarse con los datos del usuario seleccionado.
El botón "Update User" debería estar visible.

---

<!-- Siguientes pasos: Lógica de Actualización y Eliminación para Usuarios -->

### 1.9. Lógica de Actualización de Usuarios (`update` en `Admin\UserController`) y Validación

Crear un Form Request para la validación de la actualización de usuarios:

```bash
php artisan make:request Admin/UpdateUserRequest
```

En `app/Http/Requests/Admin/UpdateUserRequest.php`:
Poner `authorize()` a `true` (o implementar lógica de autorización).
Definir las reglas de validación en `rules()`. Serán similares a `StoreUserRequest`, pero el email debe ser único ignorando al usuario actual, y la contraseña será opcional.

```php
// app/Http/Requests/Admin/UpdateUserRequest.php
namespace App\Http\Requests\Admin; // Asegurar el namespace correcto

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
public function authorize(): bool
{
return true; // TODO: Implementar autorización real con Policies
}

public function rules(): array
{
$userId = $this->route('user')->id; // Obtener el ID del usuario de la ruta

return [
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password es opcional
'role' => ['required', Rule::in(['admin', 'client', 'reseller'])],
'reseller_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(function ($query) {
// return $query->where('role', 'reseller');
})],
'company_name' => ['nullable', 'string', 'max:255'],
'phone_number' => ['nullable', 'string', 'max:255'],
// ... (resto de campos igual que en StoreUserRequest, pero sin 'required' si pueden ser opcionales al editar)
'address_line1' => ['nullable', 'string', 'max:255'],
'address_line2' => ['nullable', 'string', 'max:255'],
'city' => ['nullable', 'string', 'max:255'],
'state_province' => ['nullable', 'string', 'max:255'],
'postal_code' => ['nullable', 'string', 'max:255'],
'country_code' => ['nullable', 'string', 'max:2'],
'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
'language_code' => ['nullable', 'string', 'max:10'],
'currency_code' => ['nullable', 'string', 'max:3'],
];
}
}
```

En `Admin/UserController.php`, actualizar el método `update()` para usar `UpdateUserRequest` y actualizar el usuario:

```php
// En app/Http/Controllers/Admin/UserController.php
// Asegúrate de importar:
// use App\Models\User; // Ya debería estar
use App\Http\Requests\Admin\UpdateUserRequest;
// use Illuminate\Support\Facades\Hash; // Ya debería estar
// use Illuminate\Http\RedirectResponse; // Ya debería estar

public function update(UpdateUserRequest $request, User $user): RedirectResponse
{
$validatedData = $request->validated();

$updateData = $validatedData;
if (!empty($validatedData['password'])) {
$updateData['password'] = Hash::make($validatedData['password']);
} else {
unset($updateData['password']); // No actualizar la contraseña si está vacía
}

$user->update($updateData);

return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
}
```

**Verificación:**
Intentar enviar el formulario de edición con datos inválidos (ej: email vacío, email duplicado de otro usuario). Deberían aparecer los errores de validación.
Editar un usuario sin cambiar la contraseña. La contraseña no debería cambiar en la BD.
Editar un usuario y cambiar la contraseña. La contraseña debería actualizarse (hasheada) en la BD.
Actualizar un usuario con datos válidos. Debería ser redirigido a `/admin/users` con un mensaje de éxito, y los cambios deberían reflejarse en la tabla de listado y en la BD.

### 1.10. Lógica de Eliminación de Usuarios (`destroy` en `Admin\UserController`)

En `Admin/UserController.php`, implementar el método `destroy()`:

```php
// En app/Http/Controllers/Admin/UserController.php
public function destroy(User $user): RedirectResponse
{
// TODO: Añadir lógica de autorización (Policy) para asegurar que el usuario autenticado puede eliminar a este usuario.
// Por ejemplo, no permitir que un admin se elimine a sí mismo, o que un admin de menor nivel elimine a uno de mayor nivel.

$user->delete(); // Soft delete si el modelo usa SoftDeletes

return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
}
```

En `resources/js/Pages/Admin/Users/Index.vue`, añadir un botón de "Delete" para cada usuario y la lógica para enviar la solicitud DELETE.
Importante:\*\* Usar un diálogo de confirmación antes de eliminar.

```vue
// En el <script setup> de Index.vue
import { router } from '@inertiajs/vue3'; // Importar router

const deleteUser = (userId, userName) => {
if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
router.delete(route('admin.users.destroy', userId), {
preserveScroll: true, // Para mantener la posición del scroll después de la redirección
onSuccess: () => {
// El mensaje flash ya debería manejarse como en el store
},
onError: (errors) => {
// Manejar errores si la eliminación falla (ej: por políticas de autorización)
alert(Object.values(errors).join('\n'));
}
});
}
};
```

Añadir el botón en la tabla dentro del `<td>` de acciones:

```html
<!-- Dentro del bucle v-for="user in users.data" en Index.vue -->
<button
  @click="deleteUser(user.id, user.name)"
  class="text-red-600 hover:text-red-900"
>
  Delete
</button>
```

**Verificación:**
Hacer clic en el botón "Delete" de un usuario en la lista. Debería aparecer un diálogo de confirmación.
Cancelar la eliminación. No debería pasar nada.
Confirmar la eliminación. El usuario debería desaparecer de la lista (y ser marcado como `deleted_at` en la BD si se usa SoftDeletes). Debería aparecer un mensaje de éxito.
Intentar eliminar un usuario que no debería poder ser eliminado (ej: el propio admin logueado, si implementas esa lógica). La acción debería fallar o ser prevenida.

---

¡CRUD Básico de Usuarios Completado!\*\*
En este punto, deberías tener una gestión funcional de usuarios desde el "panel de administración" (aunque aún sea público).
Los siguientes pasos podrían incluir:

- Implementación de Layouts de Administración.
- Aplicación de Middlewares de Autenticación y Roles.
- Mejoras en la UI/UX (componentes de formulario reutilizables, notificaciones más elegantes, etc.).
- Implementación de la funcionalidad "Ver Usuario" (método `show` y vista `Show.vue`).
- Filtros y búsqueda en la tabla de listado de usuarios.

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la creación de un Layout para el panel de administración y la implementación de middlewares de autenticación y roles.



Objetivo:\*\* Crear una estructura de navegación consistente para el panel de administración y proteger las rutas administrativas.

### 2.1. Creación de un Layout Básico para el Panel de Administración (`AdminLayout.vue`)

Contexto:\*\* Actualmente, nuestras páginas de administración de usuarios (`Index.vue`, `Create.vue`, `Edit.vue`) no tienen una estructura de navegación común (como un sidebar o un navbar). Vamos a crear un layout reutilizable.
Crear el directorio `resources/js/Layouts/` si no existe (Breeze ya debería haberlo creado con `AuthenticatedLayout.vue` y `GuestLayout.vue`).
Crear el archivo `resources/js/Layouts/AdminLayout.vue`.
Implementar una estructura básica en `AdminLayout.vue`. Incluirá un slot para el contenido de la página, y placeholders para un futuro sidebar y un header/navbar.

```vue
// resources/js/Layouts/AdminLayout.vue
<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
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
  router.put(
    route("current-team.update"),
    {
      team_id: team.id,
    },
    {
      preserveState: false,
    }
  );
};

const logout = () => {
  router.post(route("logout"));
};
</script>

<template>
  <div>
    <Head :title="title" />

    <!-- <Banner /> -->
    {/* Si usas Jetstream Banner */}

    <div class="flex min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="hidden w-64 p-6 space-y-6 text-white bg-gray-800 md:block">
        <div class="text-2xl font-semibold text-center">
          <Link :href="route('admin.users.index')">
            {/* O a un dashboard de admin */} Admin Panel {/*
            <ApplicationMark class="block w-auto h-9" /> Si tienes un logo */}
          </Link>
        </div>

        <nav class="mt-10">
          <Link
            :href="route('admin.users.index')"
            :class="{
              'bg-gray-900 text-white': route().current('admin.users.*'),
            }"
            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white"
          >
            Manage Users
          </Link>
          {/*
          <!-- Otros enlaces del sidebar aquí -->
          */}
          <Link
            href="#"
            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2"
          >
            Products (Placeholder)
          </Link>
          <Link
            href="#"
            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2"
          >
            Settings (Placeholder)
          </Link>
        </nav>
      </aside>

      <!-- Main content -->
      <div class="flex flex-col flex-1 overflow-hidden">
        <header class="bg-white shadow">
          <div
            class="flex items-center justify-between px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8"
          >
            <h2
              v-if="$slots.header"
              class="text-xl font-semibold leading-tight text-gray-800"
            >
              <slot name="header" />
            </h2>
            <div
              v-else
              class="text-xl font-semibold leading-tight text-gray-800"
            >
              {{ title }}
            </div>

            <!-- Settings Dropdown (adaptado de AuthenticatedLayout de Breeze/Jetstream) -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <div class="relative ml-3">
                <!-- <Dropdown align="right" width="48"> -->
                {/* Si usas el componente Dropdown */}
                <button
                  v-if="
                    $page.props.jetstream?.canCreateTeams ||
                    $page.props.auth.user
                  "
                  type="button"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50"
                >
                  {{ $page.props.auth.user?.name }}

                  <svg
                    class="ml-2 -mr-0.5 h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                    />
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
                <button
                  @click="logout"
                  class="inline-flex items-center px-3 py-2 ml-4 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50"
                >
                  Log Out (Placeholder)
                </button>
              </div>
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto bg-gray-200">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
```

Nota:** Este layout es una mezcla de un sidebar simple y un header que podría tener un dropdown de usuario similar al de Breeze/Jetstream. Puedes simplificarlo o adaptarlo según tus necesidades. El botón de "Log Out" es un placeholder por ahora.
**Verificación:\*\* El archivo `AdminLayout.vue` existe y tiene la estructura básica.

### 2.2. Integrar `AdminLayout.vue` en las Vistas de Usuarios

Modificar `resources/js/Pages/Admin/Users/Index.vue`:
Importar `AdminLayout`.
Envolver el contenido del `<template>` con `<AdminLayout>`.
Pasar el `title` como prop a `AdminLayout`.

```vue
// resources/js/Pages/Admin/Users/Index.vue
<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue"; // IMPORTAR
// ... (resto del script setup)

defineProps({
  users: Object,
  // flash: Object, // Si manejas flash messages aquí
});
</script>

<template>
  <AdminLayout title="Manage Users">
    {/* USAR LAYOUT Y PASAR TÍTULO */} <Head title="Manage Users" /> {/* Head
    puede seguir aquí o solo en el Layout */} {/* El contenido anterior de
    Index.vue va aquí, dentro del slot por defecto de AdminLayout */}
    <div class="py-0">
      {/* Ajustar padding si el layout ya lo maneja */}
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        {/* ... (resto del contenido de la tabla de usuarios, etc.) ... */} {/*
        Asegúrate de que el botón "Create User" y la tabla estén dentro de este
        div o del slot del layout */}
      </div>
    </div>
  </AdminLayout>
</template>
```

Modificar `resources/js/Pages/Admin/Users/Create.vue` de forma similar:

```vue
// resources/js/Pages/Admin/Users/Create.vue
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue"; // IMPORTAR
// ... (resto del script setup)
</script>

<template>
  <AdminLayout title="Create User">
    {/* USAR LAYOUT Y PASAR TÍTULO */}
    <Head title="Create User" />
    {/* ... (contenido del formulario de creación aquí) ... */}
  </AdminLayout>
</template>
```

Modificar `resources/js/Pages/Admin/Users/Edit.vue` de forma similar:

```vue
// resources/js/Pages/Admin/Users/Edit.vue
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue"; // IMPORTAR
// ... (resto del script setup)
</script>

<template>
  <AdminLayout :title="'Edit User - ' + user.name">
    {/* USAR LAYOUT Y PASAR TÍTULO */}
    <Head :title="'Edit User - ' + user.name" />
    {/* ... (contenido del formulario de edición aquí) ... */}
  </AdminLayout>
</template>
```

**Verificación:**
Navegar a `/admin/users`, `/admin/users/create`, y `/admin/users/{id}/edit`.
Todas estas páginas deberían ahora mostrar la estructura del `AdminLayout` (el sidebar y el header placeholders) con el contenido específico de la página dentro del área principal.
Los enlaces del sidebar (ej: "Manage Users") deberían funcionar y resaltar la sección activa (si implementaste la clase activa).

---

<!-- Siguientes pasos: Middlewares de Autenticación y Roles -->

````

<!-- Siguientes pasos: Middlewares de Autenticación y Roles -->

### 2.3. Aplicar Middleware de Autenticación a las Rutas de Administración
Contexto:** Actualmente, las rutas `/admin/*` son públicas. Necesitamos asegurar que solo los usuarios autenticados puedan acceder.
En `routes/web.php`, modificar el grupo de rutas del administrador para incluir el middleware `auth` de Laravel (Breeze ya lo configura).
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
````

El middleware `verified` es opcional pero recomendado si quieres que los usuarios verifiquen su email antes de acceder al panel. Breeze también lo configura.
**Verificación:**
Cerrar sesión si estabas logueado.
Intentar acceder a `/admin/users`. Deberías ser redirigido a la página de login (`/login`).
Iniciar sesión con un usuario existente. Ahora deberías poder acceder a `/admin/users`.

### 2.4. Crear y Aplicar Middleware de Rol de Administrador (`EnsureUserIsAdmin`)

Contexto:\*\* Solo los usuarios con `role = 'admin'` deberían poder acceder al panel de administración.
Crear el middleware:

```bash
php artisan make:middleware EnsureUserIsAdmin
```

Esto creará `app/Http/Middleware/EnsureUserIsAdmin.php`.
Implementar la lógica en `app/Http/Middleware/EnsureUserIsAdmin.php`:

```php
// app/Http/Middleware/EnsureUserIsAdmin.php
namespace App\Http\Middleware;



Registrar el middleware en `app/Http/Kernel.php` dentro del array `$routeMiddleware` (o `$middlewareAliases` en Laravel 10+):

php
// app/Http/Kernel.php
protected $routeMiddleware = [ // o $middlewareAliases en L10+
// ... otros middlewares
'auth' => \App\Http\Middleware\Authenticate::class, // Ejemplo, ya debería estar
'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class, // AÑADIR ESTA LÍNEA
// ...
];
```

Aplicar el middleware `admin` al grupo de rutas de administración en `routes/web.php`:

```php
// routes/web.php
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () { // AÑADIR MIDDLEWARE 'admin'
Route::resource('users', AdminUserController::class);
// ...
});
```

**Verificación:**
Crear (o modificar un usuario existente en la BD) para que tenga `role = 'client'` o `role = 'reseller'`.
Iniciar sesión con este usuario no administrador.
Intentar acceder a `/admin/users`. Deberías ver un error 403 Forbidden (o ser redirigido, según lo que hayas configurado en el middleware).
Modificar el usuario en la BD para que tenga `role = 'admin'`.
Cerrar sesión y volver a iniciar sesión con este usuario administrador.
Ahora deberías poder acceder a `/admin/users` sin problemas.

---

¡Panel de Administración con Layout y Seguridad Básica Implementados!\*\*
En este punto, el panel de administración tiene una estructura visual consistente y está protegido para que solo usuarios autenticados con el rol de 'admin' puedan acceder.
Los siguientes pasos podrían incluir:

- Creación de un Dashboard para el Admin.
- Implementación de Políticas de Laravel para un control de acceso más granular dentro del CRUD de usuarios (ej: un admin no puede eliminarse a sí mismo).
- Desarrollo de los módulos de Productos, Órdenes, etc., para el panel de administración.
- Creación de los Layouts y rutas para los paneles de Cliente y Revendedor.

````

Con esto, `Geminis_Tareas_03.md` estaría completo, cubriendo la creación del layout de administración y la seguridad básica de sus rutas. ¡Avísame cuando quieras continuar con el siguiente archivo o tema!



Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la creación de un Dashboard para el Administrador y la implementación de Políticas de Laravel para un control de acceso más granular.



### 2.5. Creación de un Dashboard Básico para el Administrador
Contexto:** El panel de administración necesita una página de inicio o dashboard.
Crear un controlador para el Dashboard del Admin:
```bash
php artisan make:controller Admin/AdminDashboardController
````

En `app/Http/Controllers/Admin/AdminDashboardController.php`, crear un método `index()`:

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

Definir la ruta para el dashboard del admin en `routes/web.php` dentro del grupo de administración:

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

Crear la vista del Dashboard en `resources/js/Pages/Admin/Dashboard.vue`:

```vue
// resources/js/Pages/Admin/Dashboard.vue
<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";

// defineProps({
//   userCount: Number,
//   activeServicesCount: Number,
// });
</script>

<template>
  <AdminLayout title="Admin Dashboard">
    <Head title="Admin Dashboard" />

    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Admin Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">Welcome to the Admin Dashboard!</div>
        </div>

        <!-- Ejemplo de cómo mostrar estadísticas si las pasas desde el controlador -->
        <!--
<div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2 lg:grid-cols-3">
<div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
<h3 class="text-lg font-medium text-gray-900">Total Users</h3>
<p class="mt-1 text-3xl font-semibold text-indigo-600">{{ userCount }}</p>
</div>
<div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
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

Actualizar el enlace principal del "Admin Panel" en `resources/js/Layouts/AdminLayout.vue` para que apunte al nuevo dashboard:

```diff
--- a/resources/js/Layouts/AdminLayout.vue
b/resources/js/Layouts/AdminLayout.vue
<!-- Sidebar -->
<aside class="hidden w-64 p-6 space-y-6 text-white bg-gray-800 md:block">
<div class="text-2xl font-semibold text-center">
-                        <Link :href="route('admin.users.index')"> {/* O a un dashboard de admin */}
<Link :href="route('admin.dashboard')">
Admin Panel
{/* <ApplicationMark class="block w-auto h-9" />  Si tienes un logo */}
</Link>

<nav class="mt-10">
<Link :href="route('admin.users.index')"
-                              :class="{ 'bg-gray-900 text-white': route().current('admin.users.*') }"
:class="{ 'bg-gray-900 text-white': route().current('admin.users.*') || route().current('admin.dashboard') && route().current().uri.includes('users') }"
class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
Manage Users
</Link>
```

Nota sobre la clase activa del sidebar:** La lógica para la clase activa del enlace "Manage Users" puede necesitar ajustarse si el dashboard es la página principal. Una forma simple es que el dashboard tenga su propio enlace o que "Manage Users" se active si la ruta actual contiene `admin.users`.
**Verificación:\*\*
Iniciar sesión como administrador.
Navegar a `/admin/dashboard`. Deberías ver la página del dashboard con el mensaje de bienvenida.
El logo/título "Admin Panel" en el sidebar ahora debería enlazar a `/admin/dashboard`.

---

<!-- Siguientes pasos: Implementación de Políticas de Laravel para Usuarios -->

### 2.6. Implementación de Políticas de Laravel para Usuarios (`UserPolicy`)

Contexto:\*\* Queremos un control más granular sobre quién puede ver, crear, actualizar o eliminar usuarios. Por ejemplo, un administrador no debería poder eliminarse a sí mismo.
Generar una Policy para el modelo `User`:

```bash
php artisan make:policy UserPolicy --model=User
```

Esto creará `app/Policies/UserPolicy.php`.
Registrar la `UserPolicy` en `app/Providers/AuthServiceProvider.php`:

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
The policy mappings for the application.

@var array<class-string, class-string>
/
protected $policies = [
User::class => UserPolicy::class, // AÑADIR ESTA LÍNEA
];

/**
Register any authentication / authorization services.
/
public function boot(): void
{
$this->registerPolicies();

//
}
}
```

Implementar los métodos de la Policy en `app/Policies/UserPolicy.php`. Empezaremos con `viewAny`, `create`, `update`, y `delete`.

```php
// app/Policies/UserPolicy.php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // O Response en L10+

class UserPolicy
{
use HandlesAuthorization; // O sin esto y retornar Response::allow/deny en L10+

/**
Determine whether the user can view any models.
El usuario autenticado ($adminUser) puede ver la lista de usuarios ($targetUser no se usa aquí).
/
public function viewAny(User $adminUser): bool
{
return $adminUser->role === 'admin';
}

/**
Determine whether the user can view the model.
El usuario autenticado ($adminUser) puede ver el perfil de otro usuario ($targetUser).
/
public function view(User $adminUser, User $targetUser): bool
{
return $adminUser->role === 'admin';
// Podrías añadir lógica más compleja, ej: un admin solo puede ver usuarios de su 'nivel' o 'departamento'.
}

/**
Determine whether the user can create models.
/
public function create(User $adminUser): bool
{
return $adminUser->role === 'admin';
}

/**
Determine whether the user can update the model.
El usuario autenticado ($adminUser) puede actualizar a $targetUser.
/
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
Determine whether the user can delete the model.
/
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
Determine whether the user can restore the model.
/
public function restore(User $adminUser, User $targetUser): bool
{
return $adminUser->role === 'admin';
}

/**
Determine whether the user can permanently delete the model.
/
public function forceDelete(User $adminUser, User $targetUser): bool
{
return $adminUser->role === 'admin'; // Y quizás solo un superadmin
}
}
```

**Verificación (Conceptual):** Las políticas están definidas. Ahora necesitamos aplicarlas en los controladores.

### 2.7. Aplicar `UserPolicy` en `Admin\UserController`

En `app/Http/Controllers/Admin/UserController.php`, usar los métodos de la policy para autorizar acciones.
En `index()`:\*\*

```php
// Dentro de AdminUserController.php
public function index()
{
$this->authorize('viewAny', User::class); // AÑADIR ESTO
$users = User::latest()->paginate(10);
return Inertia::render('Admin/Users/Index', ['users' => $users]);
}
```

En `create()`:\*\*

```php
public function create()
{
$this->authorize('create', User::class); // AÑADIR ESTO
return Inertia::render('Admin/Users/Create');
}
```

En `store()`:\*\* (La autorización se maneja en el FormRequest `StoreUserRequest` o se puede duplicar aquí si se prefiere)

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

En `edit(User $user)`:\*\*

```php
public function edit(User $user)
{
$this->authorize('update', $user); // AÑADIR ESTO (o 'view' si es solo para ver el form)
return Inertia::render('Admin/Users/Edit', ['user' => $user]);
}
```

En `update(UpdateUserRequest $request, User $user)`:\*\* (La autorización se maneja en `UpdateUserRequest` o se duplica)

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

En `destroy(User $user)`:\*\*

```php
public function destroy(User $user): RedirectResponse
{
$this->authorize('delete', $user); // AÑADIR ESTO
$user->delete();
return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
}
```

Actualizar los métodos `authorize()` en `StoreUserRequest.php` y `UpdateUserRequest.php`:

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

**Verificación:**
Intentar acceder a `/admin/users` como un usuario no administrador (si el middleware `admin` fallara por alguna razón, la policy `viewAny` debería impedirlo).
Como administrador, intentar eliminar tu propia cuenta de usuario. La acción debería ser denegada por la policy `delete` (recibirás un 403 Forbidden).
(Si implementas más lógica en las policies) Probar otros escenarios, como un admin intentando editar su propio rol (si lo restringes).
Nota:\*\* Los botones de "Edit" y "Delete" en la vista `Index.vue` podrían ocultarse condicionalmente usando `$page.props.auth.user.can('update', user)` y `can('delete', user)` si pasas el usuario completo a la vista y tienes las policies bien configuradas. Esto es una mejora de UX.

---

¡Políticas de Acceso para Usuarios Implementadas!\*\*
Ahora tienes un control más fino sobre las acciones del CRUD de usuarios, basado en el usuario autenticado y el usuario objetivo.
Los siguientes pasos podrían incluir:

- Desarrollo de los módulos de Productos, Órdenes, etc., para el panel de administración, aplicando policies similares.
- Creación de los Layouts y rutas para los paneles de Cliente y Revendedor, con sus propios middlewares y policies.
- Implementación de la funcionalidad "Ver Usuario" (método `show` y vista `Show.vue`), protegida por la policy `view`.
<!-- Siguientes pasos: Implementación de Políticas de Laravel para Usuarios -->

````



Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en completar la funcionalidad "Ver Usuario" y comenzando el módulo de Productos para el panel de administración.



### 2.8. Implementación de la Funcionalidad "Ver Usuario" (`show` en `Admin\UserController`)
Contexto:** Actualmente no tenemos una vista dedicada para ver los detalles completos de un usuario.
En `app/Http/Controllers/Admin/UserController.php`, implementar el método `show(User $user)`:
```php
// En app/Http/Controllers/Admin/UserController.php
// Asegúrate de que User e Inertia estén importados.

public function show(User $user)
{
$this->authorize('view', $user); // Usar la UserPolicy

// Podrías querer cargar relaciones aquí si son necesarias para la vista de detalle
// $user->load('reseller', 'clients'); // Ejemplo

return Inertia::render('Admin/Users/Show', [
'user' => $user,
]);
}
````

Crear la vista `resources/js/Pages/Admin/Users/Show.vue`:

```vue
// resources/js/Pages/Admin/Users/Show.vue
<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
  user: Object,
});
</script>

<template>
  <AdminLayout :title="'View User - ' + user.name">
    <Head :title="'View User - ' + user.name" />

    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          User Details: {{ user.name }}
        </h2>
        <Link
          :href="route('admin.users.edit', user.id)"
          class="px-4 py-2 text-white bg-yellow-500 rounded hover:bg-yellow-600"
        >
          Edit User
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 space-y-6 bg-white border-b border-gray-200 md:p-8">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                User Information
              </h3>
              <dl class="mt-2 divide-y divide-gray-200">
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.name }}
                  </dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">
                    Email Address
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.email }}
                  </dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Role</dt>
                  <dd
                    class="mt-1 text-sm text-gray-900 capitalize sm:mt-0 sm:col-span-2"
                  >
                    {{ user.role }}
                  </dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd
                    class="mt-1 text-sm text-gray-900 capitalize sm:mt-0 sm:col-span-2"
                  >
                    {{ user.status }}
                  </dd>
                </div>
                <div
                  v-if="user.role === 'client' && user.reseller_id"
                  class="py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                >
                  <dt class="text-sm font-medium text-gray-500">
                    Belongs to Reseller ID
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.reseller_id }}
                  </dd>
                  {/* TODO: Podríamos mostrar el nombre del revendedor si
                  cargamos la relación */}
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">
                    Registered On
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ new Date(user.created_at).toLocaleDateString() }}
                  </dd>
                </div>
                <div
                  v-if="user.last_login_at"
                  class="py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                >
                  <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ new Date(user.last_login_at).toLocaleString() }}
                  </dd>
                </div>
              </dl>
            </div>

            <div v-if="user.company_name || user.phone_number">
              <h3 class="text-lg font-medium text-gray-900">
                Contact Information
              </h3>
              <dl class="mt-2 divide-y divide-gray-200">
                <div
                  v-if="user.company_name"
                  class="py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                >
                  <dt class="text-sm font-medium text-gray-500">
                    Company Name
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.company_name }}
                  </dd>
                </div>
                <div
                  v-if="user.phone_number"
                  class="py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                >
                  <dt class="text-sm font-medium text-gray-500">
                    Phone Number
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.phone_number }}
                  </dd>
                </div>
              </dl>
            </div>

            <div v-if="user.address_line1">
              <h3 class="text-lg font-medium text-gray-900">Address</h3>
              <dl class="mt-2 divide-y divide-gray-200">
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Address</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ user.address_line1 }}<br />
                    <span v-if="user.address_line2"
                      >{{ user.address_line2 }}<br
                    /></span>
                    {{ user.city }}, {{ user.state_province }}
                    {{ user.postal_code }}<br />
                    {{ user.country_code }}
                  </dd>
                </div>
              </dl>
            </div>

            <div class="flex justify-end mt-6">
              <Link
                :href="route('admin.users.index')"
                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50"
              >
                Back to Users List
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
```

En `resources/js/Pages/Admin/Users/Index.vue`, añadir un enlace "View" para cada usuario en la tabla que apunte a la ruta `admin.users.show`.

```diff
--- a/resources/js/Pages/Admin/Users/Index.vue
b/resources/js/Pages/Admin/Users/Index.vue
<td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ user.role }}</td>
<td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ user.status }}</td>
<td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
-                        <Link :href="route('admin.users.edit', user.id)" class="mr-3 text-indigo-600 hover:text-indigo-900">Edit</Link>
<Link :href="route('admin.users.show', user.id)" class="mr-3 text-blue-600 hover:text-blue-900">View</Link>
<Link :href="route('admin.users.edit', user.id)" class="mr-3 text-indigo-600 hover:text-indigo-900">Edit</Link>
<!-- Botón de eliminar vendrá después -->
<button @click="deleteUser(user.id, user.name)" class="text-red-600 hover:text-red-900">Delete</button>
</td>
```

**Verificación:**
En la lista de usuarios (`/admin/users`), hacer clic en el enlace "View" de un usuario.
Deberías ser redirigido a `/admin/users/{id}`.
La página debería mostrar los detalles del usuario seleccionado, utilizando el `AdminLayout`.
El botón "Edit User" en la página de detalles debería llevar al formulario de edición.
El botón "Back to Users List" debería llevar de vuelta a `/admin/users`.
Intentar acceder a la URL de `show` de un usuario como un usuario no administrador (si el middleware `admin` o la policy `view` fallan). Debería ser denegado.

---

<!-- Siguientes pasos: Inicio del Módulo de Productos -->

## Fase 3: Módulo de Productos (CRUD Básico - Panel de Administración)

Objetivo:\*\* Crear la estructura básica para la gestión de productos (servicios de hosting, dominios, etc.) desde el panel de administración.

### 3.1. Migración de la Tabla `products`

Contexto:\*\* Necesitamos una tabla para almacenar la información de los productos ofrecidos.
Crear la migración para la tabla `products`:

```bash
php artisan make:migration create_products_table
```

En el archivo de migración recién creado (ej: `database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php`), definir la estructura de la tabla `products` según `Geminis_Estructura.md`.

```php
// En database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::create('products', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->enum('type', ['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])->index();
$table->string('module_name')->nullable()->index(); // Para integración con cPanel, Plesk, etc.

$table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade'); // FK a users.id (puede ser NULL para productos de plataforma, o ID de reseller)

$table->boolean('is_publicly_available')->default(true);
$table->boolean('is_resellable_by_default')->default(true); // Para productos de plataforma

// $table->foreignId('welcome_email_template_id')->nullable()->constrained('email_templates')->onDelete('set null'); // Descomentar cuando exista la tabla email_templates
$table->unsignedBigInteger('welcome_email_template_id')->nullable(); // Placeholder hasta crear email_templates

$table->enum('status', ['active', 'inactive', 'hidden'])->default('active')->index();
$table->integer('display_order')->default(0);

$table->timestamps();
$table->softDeletes();
});
}

public function down(): void
{
Schema::dropIfExists('products');
}
};
```

Nota sobre `welcome_email_template_id`:\*\* He comentado la constraint por ahora, asumiendo que la tabla `email_templates` se creará más adelante. Si ya existe, puedes descomentarla.
Ejecutar la migración:

```bash
php artisan migrate
```

**Verificación:**
El comando `php artisan migrate` se ejecuta sin errores.
Inspeccionar la estructura de la tabla `products` en tu cliente de base de datos. Debe tener todos los campos definidos.

### 3.2. Modelo `Product`

Crear el modelo `Product`:

```bash
php artisan make:model Product -mfs # El -mfs crea migración, factory y seeder (ya creamos la migración manualmente)
# Si ya creaste la migración, solo: php artisan make:model Product -fs
# O solo: php artisan make:model Product
```

En `app/Models/Product.php`:
Añadir `SoftDeletes`.
Definir la propiedad `$fillable` con los campos de la tabla `products`.
Definir la propiedad `$casts` si es necesario (ej: para los ENUM si quieres que se comporten de manera específica, aunque no es estrictamente necesario para ENUMs de BD).
Definir relaciones (ej: `owner()` para el `users.id` del propietario, `pricings()` para `product_pricing`).

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany; // Para product_pricing

class Product extends Model
{
use HasFactory, SoftDeletes;

protected $fillable = [
'name',
'slug',
'description',
'type',
'module_name',
'owner_id',
'is_publicly_available',
'is_resellable_by_default',
'welcome_email_template_id',
'status',
'display_order',
];

// protected $casts = [
//     'is_publicly_available' => 'boolean',
//     'is_resellable_by_default' => 'boolean',
// ];

public function owner(): BelongsTo
{
return $this->belongsTo(User::class, 'owner_id');
}

// public function pricings(): HasMany
// {
//     return $this->hasMany(ProductPricing::class); // Cuando se cree ProductPricing
// }
}
```

**Verificación:**
Abrir `php artisan tinker`.
Intentar crear un producto: `App\Models\Product::create(['name' => 'Test Product', 'slug' => 'test-product', 'type' => 'shared_hosting', 'status' => 'active']);`
Verificar que se crea en la base de datos.

---

<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->

<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->

### 3.3. Controlador de Productos para Administración (`Admin\ProductController`)

Crear un controlador resource para la gestión de productos en el panel de administración:

```bash
php artisan make:controller Admin/ProductController --resource --model=Product
```

**Verificación:** El archivo `app/Http/Controllers/Admin/ProductController.php` existe y contiene los métodos CRUD (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).

### 3.4. Rutas para el CRUD de Productos (Administración)

En `routes/web.php`, definir las rutas resource para `Admin\ProductController` dentro del grupo de administración:

```php
// routes/web.php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController; // IMPORTAR

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
Route::resource('users', AdminUserController::class);
Route::resource('products', AdminProductController::class); // AÑADIR ESTA LÍNEA
});
```

**Verificación:**
Ejecutar `php artisan route:list`.
Verificar que las rutas para `admin.products.index`, `admin.products.create`, etc., estén listadas y apunten a `Admin\ProductController`.

### 3.5. Vista de Listado de Productos (`resources/js/Pages/Admin/Products/Index.vue`)

En el método `index` de `Admin\ProductController.php`, obtener todos los productos y pasarlos a una vista Inertia.

```php
// En app/Http/Controllers/Admin/ProductController.php
namespace App\Http\Controllers\Admin; // Asegurar namespace

use App\Http\Controllers\Controller; // Importar Controller base
use App\Models\Product;
use Illuminate\Http\Request; // Importar Request
use Inertia\Inertia;
use Inertia\Response; // Importar Response

class ProductController extends Controller // Extender de Controller
{
public function index(): Response
{
// $this->authorize('viewAny', Product::class); // Descomentar cuando se cree ProductPolicy
$products = Product::with('owner')->latest()->paginate(10); // Cargar relación owner si se quiere mostrar el nombre del revendedor
return Inertia::render('Admin/Products/Index', [
'products' => $products,
]);
}
// ... otros métodos del resource controller
}
```

Crear el directorio `resources/js/Pages/Admin/Products/`.
Crear el archivo `resources/js/Pages/Admin/Products/Index.vue`.
Implementar una tabla básica para mostrar los productos (ID, Nombre, Tipo, Propietario (si es de revendedor), Estado). Usar Tailwind CSS.

```vue
// resources/js/Pages/Admin/Products/Index.vue
<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
// Importar componente de paginación si se tiene uno reutilizable

defineProps({
  products: Object,
});

// const deleteProduct = (productId, productName) => { ... } // Lógica de eliminación similar a la de usuarios
</script>

<template>
  <AdminLayout title="Manage Products">
    <Head title="Manage Products" />

    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Manage Products
        </h2>
        <Link
          :href="route('admin.products.create')"
          class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600"
        >
          Create Product
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- TODO: Añadir tabla para listar productos -->
            <p>Product listing table will go here.</p>
            <p>
              Fields to show: ID, Name, Slug, Type, Owner (if reseller), Status,
              Actions (View, Edit, Delete)
            </p>
            <!-- TODO: Añadir paginación -->
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
```

Nota:\*\* El contenido de la tabla es un placeholder. Se completará en los siguientes pasos.
Actualizar el sidebar en `resources/js/Layouts/AdminLayout.vue` para incluir un enlace a "Manage Products":

```diff
--- a/resources/js/Layouts/AdminLayout.vue
b/resources/js/Layouts/AdminLayout.vue
class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
Manage Users
</Link>
-                        {/* <!-- Otros enlaces del sidebar aquí --> */}
-                        <Link href="#"
<Link :href="route('admin.products.index')"
:class="{ 'bg-gray-900 text-white': route().current('admin.products.*') }"
class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2">
Products (Placeholder)
</Link>
```

**Verificación:**
Asegúrate de que `npm run dev` esté ejecutándose.
Navegar a `/admin/products` en tu navegador.
Deberías ver la página con el `AdminLayout` y el placeholder "Product listing table will go here."
El enlace "Products" en el sidebar debería estar activo y llevar a esta página.
El botón "Create Product" debería estar visible.

---

<!-- Siguientes pasos: Completar la tabla de listado de productos, formularios de creación/edición, validaciones y lógica para Productos. -->

````

Con esto, `Geminis_Tareas_05.md` estaría completo. Hemos finalizado la vista de "Ver Usuario" y hemos sentado las bases para el módulo de Productos.

<!-- Siguientes pasos: Inicio del Módulo de Productos -->



Objetivo:** Crear la estructura básica para la gestión de productos (servicios de hosting, dominios, etc.) desde el panel de administración.

### 3.6. Migración de la Tabla `products`
Contexto:** Necesitamos una tabla para almacenar la información de los productos ofrecidos.
Crear la migración para la tabla `products`:
```bash
php artisan make:migration create_products_table
````

En el archivo de migración recién creado (ej: `database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php`), definir la estructura de la tabla `products` según `Geminis_Estructura.md`.

```php
// En database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::create('products', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->enum('type', ['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])->index();
$table->string('module_name')->nullable()->index(); // Para integración con cPanel, Plesk, etc.

$table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade'); // FK a users.id (puede ser NULL para productos de plataforma, o ID de reseller)

$table->boolean('is_publicly_available')->default(true);
$table->boolean('is_resellable_by_default')->default(true); // Para productos de plataforma

// $table->foreignId('welcome_email_template_id')->nullable()->constrained('email_templates')->onDelete('set null'); // Descomentar cuando exista la tabla email_templates
$table->unsignedBigInteger('welcome_email_template_id')->nullable(); // Placeholder hasta crear email_templates

$table->enum('status', ['active', 'inactive', 'hidden'])->default('active')->index();
$table->integer('display_order')->default(0);

$table->timestamps();
$table->softDeletes();
});
}

public function down(): void
{
Schema::dropIfExists('products');
}
};
```

Nota sobre `welcome_email_template_id`:\*\* He comentado la constraint por ahora, asumiendo que la tabla `email_templates` se creará más adelante. Si ya existe, puedes descomentarla.
Ejecutar la migración:

```bash
php artisan migrate
```

**Verificación:**
El comando `php artisan migrate` se ejecuta sin errores.
Inspeccionar la estructura de la tabla `products` en tu cliente de base de datos. Debe tener todos los campos definidos.

### 3.7. Modelo `Product`

Crear el modelo `Product`:

```bash
php artisan make:model Product -fs # -fs crea factory y seeder. La migración ya la hicimos.
# O solo: php artisan make:model Product
```

En `app/Models/Product.php`:
Añadir `SoftDeletes`.
Definir la propiedad `$fillable` con los campos de la tabla `products`.
Definir la propiedad `$casts` si es necesario.
Definir relaciones (ej: `owner()` para `users.id`, `pricings()` para `product_pricing`).

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany; // Para product_pricing

class Product extends Model
{
use HasFactory, SoftDeletes;

protected $fillable = [
'name',
'slug',
'description',
'type',
'module_name',
'owner_id',
'is_publicly_available',
'is_resellable_by_default',
'welcome_email_template_id',
'status',
'display_order',
];

// protected $casts = [
//     'is_publicly_available' => 'boolean',
//     'is_resellable_by_default' => 'boolean',
// ];

public function owner(): BelongsTo
{
return $this->belongsTo(User::class, 'owner_id');
}

// public function pricings(): HasMany
// {
//     return $this->hasMany(ProductPricing::class); // Cuando se cree ProductPricing
// }
}
```

**Verificación:**
Abrir `php artisan tinker`.
Intentar crear un producto: `App\Models\Product::create(['name' => 'Test Product', 'slug' => 'test-product', 'type' => 'shared_hosting', 'status' => 'active']);`
Verificar que se crea en la base de datos.

---

<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->



Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la gestión de precios para los productos.



### 3.8. Migración de la Tabla `product_pricing`

Contexto:\*\* Necesitamos una tabla para almacenar los diferentes precios y ciclos de facturación para cada producto.
Crear la migración para la tabla `product_pricing`:

```bash
php artisan make:migration create_product_pricing_table
```

Modificar el método `up()` de la migración para que coincida con la definición de la tabla `product_pricing` en `Geminis_Estructura.md`.

```php
// database/migrations/xxxx_xx_xx_xxxxxx_create_product_pricing_table.php
Schema::create('product_pricing', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products')->onDelete('cascade');
$table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially', 'one_time'])->index();
$table->decimal('price', 10, 2);
$table->decimal('setup_fee', 10, 2)->default(0.00);
$table->string('currency_code', 3)->index();
$table->boolean('is_active')->default(true);
$table->timestamps();
// Constraint: Índice único en (product_id, billing_cycle, currency_code)
$table->unique(['product_id', 'billing_cycle', 'currency_code'], 'product_cycle_currency_unique');
});
```

Ejecutar la migración:

```bash
php artisan migrate
```

**Verificación:** La tabla `product_pricing` existe en la base de datos con las columnas y el índice único correctos.

### 3.9. Modelo `ProductPricing`

Crear el modelo `ProductPricing`:

```bash
php artisan make:model ProductPricing
```

En `app/Models/ProductPricing.php`, configurar la propiedad `$fillable`:

```php
// app/Models/ProductPricing.php
protected $fillable = [
'product_id',
'billing_cycle',
'price',
'setup_fee',
'currency_code',
'is_active',
];
// Especificar el nombre de la tabla si no sigue la convención plural
// protected $table = 'product_pricing';
```

Definir la relación `product()` en `ProductPricing.php`:

```php
public function product()
{
return $this->belongsTo(Product::class);
}
```

Definir la relación `pricings()` en `app/Models/Product.php`:

```php
// app/Models/Product.php
public function pricings()
{
return $this->hasMany(ProductPricing::class);
}
```

**Verificación:** Puedes crear y asociar precios a productos usando Tinker.

### 3.10. Controlador para `ProductPricing` (Integrado en `AdminProductController`)

Contexto:\*\* La gestión de precios se hará anidada dentro de la gestión de productos. No crearemos un controlador separado para `ProductPricing` en el admin, sino que añadiremos métodos a `AdminProductController` o manejaremos la lógica directamente en las vistas de edición de productos.
Por simplicidad inicial, podríamos añadir una sección en la vista `Admin/Products/Edit.vue` para listar, añadir, editar y eliminar precios asociados a ese producto.

### 3.11. Rutas para Precios de Productos (Anidadas o Acciones en `AdminProductController`)

Decisión:\*\* Para mantenerlo simple, las acciones de precios se manejarán a través de nuevos métodos en `AdminProductController` y no como un resource anidado completo.
Añadir rutas en `routes/web.php` para las acciones de precios (ejemplos):

```php
// routes/web.php (dentro del grupo admin)
// ...
Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');
Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');
Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');
// La edición podría ser un modal en la vista de edición del producto, o una vista separada si es compleja.
// Route::get('products/{product}/pricing/{pricing}/edit', [AdminProductController::class, 'editPricing'])->name('products.pricing.edit');
```

### 3.12. Vistas para Precios de Productos (Integradas en `Admin/Products/Edit.vue`)

Modificar `resources/js/Pages/Admin/Products/Edit.vue`:
Añadir una sección para mostrar una tabla de los precios existentes para el producto actual (`props.product.pricings`).
Incluir un formulario (quizás un modal) para añadir un nuevo precio a ese producto.
Permitir editar/eliminar precios existentes.
Campos del formulario de precios: `billing_cycle` (select), `price` (number), `setup_fee` (number), `currency_code` (select), `is_active` (checkbox).
**Verificación:**
Al editar un producto, se pueden ver, añadir, editar y eliminar sus precios.
Las validaciones para los precios funcionan correctamente.

### 3.13. Lógica en `AdminProductController` para Precios

Implementar el método `storePricing(Request $request, Product $product)` en `AdminProductController.php`:
Validar los datos del nuevo precio.
Crear el `ProductPricing` asociado al `$product`.
Redirigir de vuelta a la página de edición del producto con un mensaje de éxito.
Implementar el método `updatePricing(Request $request, Product $product, ProductPricing $pricing)`:
Validar los datos.
Actualizar el `$pricing`.
Redirigir.
Implementar el método `destroyPricing(Product $product, ProductPricing $pricing)`:
Eliminar el `$pricing`.
Redirigir.
**Verificación:** Las operaciones CRUD para los precios funcionan desde la página de edición del producto.

### 3.14. Políticas de Acceso para `ProductPricing` (Opcional, o integrada en `ProductPolicy`)

Contexto:\*\* Si la lógica de quién puede gestionar precios es la misma que quién puede gestionar productos, la `ProductPolicy` existente podría ser suficiente. Si es más granular, se podría crear una `ProductPricingPolicy`.
(Si se crea) Generar `ProductPricingPolicy`:

```bash
php artisan make:policy ProductPricingPolicy --model=ProductPricing
```

(Si se crea) Registrarla en `AuthServiceProvider`.
(Si se crea) Implementar métodos y aplicarlos en `AdminProductController` para las acciones de precios.

### 3.15. FormRequests para Precios (Opcional, o validación en controlador)

Contexto:\*\* Para validaciones más complejas o reutilizables.
(Si se crea) `php artisan make:request Admin/StoreProductPricingRequest`
(Si se crea) `php artisan make:request Admin/UpdateProductPricingRequest`
Implementar `authorize()` y `rules()` en estos FormRequests.
Usarlos en los métodos `storePricing` y `updatePricing` del `AdminProductController`.

---

¡Gestión de Precios de Productos Implementada!\*\*
Ahora los productos pueden tener diferentes opciones de precios y ciclos de facturación.
Los siguientes pasos se enfocarán en módulos más complejos como la gestión de clientes, órdenes y facturación.

````

Y ahora, para guardar tu progreso en GitHub, aquí tienes los comandos que debes ejecutar en tu terminal, desde la raíz de tu proyecto (`e:\herd\geminis`):

1.  **Verificar el estado de tus archivos (opcional pero recomendado):**
```bash
git status
````

Esto te mostrará una lista de los cambios, incluyendo el nuevo archivo `Geminis_Tareas_06.md`.

2.  **Añadir los archivos al "staging area":**

```bash
git add .
```

3.  **Hacer "commit" de los cambios:**

```bash
git commit -m "Finalización Tarea 05 (CRUD Productos) e inicio Tarea 06 (Precios de Productos)"
```

(O un mensaje más específico si lo prefieres, como "CRUD de Productos completo, AdminLayout actualizado, creado archivo Tarea 06")

4.  **Subir los cambios a GitHub (push):**

```bash
git push origin main
```

(O el nombre de tu rama si estás usando una diferente).

¡Listo! Con esto, tu progreso estará seguro y tendrás el esqueleto para la siguiente fase de desarrollo. ¡Gran trabajo!



Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en los perfiles de revendedor y los grupos de opciones configurables para los productos.

## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos

### 4.1. Migración de la Tabla `reseller_profiles`

Contexto:\*\* Necesitamos una tabla para almacenar información y configuraciones específicas de los usuarios con rol 'reseller'.
Crear la migración para la tabla `reseller_profiles`:

```bash
php artisan make:migration create_reseller_profiles_table
```

Modificar el método `up()` de la migración para que coincida con la definición de la tabla `reseller_profiles` en `Geminis_Estructura.md`.

```php
// database/migrations/xxxx_xx_xx_xxxxxx_create_reseller_profiles_table.php
Schema::create('reseller_profiles', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
$table->string('brand_name')->nullable();
$table->string('custom_domain')->nullable()->unique();
$table->string('logo_url')->nullable();
$table->string('support_email')->nullable();
$table->string('terms_url')->nullable();
$table->boolean('allow_custom_products')->default(false);
$table->timestamps();
});
```

Ejecutar la migración:

```bash
php artisan migrate
```

**Verificación:** La tabla `reseller_profiles` existe en la base de datos con las columnas correctas.

### 4.2. Modelo `ResellerProfile`

Crear el modelo `ResellerProfile`:

```bash
php artisan make:model ResellerProfile
```

En `app/Models/ResellerProfile.php`, configurar la propiedad `$fillable`:

```php
// app/Models/ResellerProfile.php
protected $fillable = [
'user_id',
'brand_name',
'custom_domain',
'logo_url',
'support_email',
'terms_url',
'allow_custom_products',
];
```

Definir la relación `user()` en `ResellerProfile.php` (un perfil pertenece a un usuario):

```php
public function user()
{
return $this->belongsTo(User::class);
}
```

Definir la relación `resellerProfile()` en `app/Models/User.php` (un usuario revendedor tiene un perfil):

```php
// app/Models/User.php
public function resellerProfile()
{
return $this->hasOne(ResellerProfile::class);
}
```

**Verificación:** Puedes crear y asociar perfiles a usuarios revendedores usando Tinker.

### 4.3. CRUD Básico para `ResellerProfile` (Integrado en `Admin\UserController`)

Contexto:** La gestión de perfiles de revendedor se podría integrar en la vista de edición del usuario (`Admin/Users/Edit.vue`) si el usuario tiene el rol 'reseller'.
Modificar `resources/js/Pages/Admin/Users/Edit.vue`:
Añadir una sección que solo se muestre si `props.user.role === 'reseller'`.
En esta sección, mostrar un formulario para editar los campos de `ResellerProfile` (brand_name, custom_domain, allow_custom_products, etc.).
El `useForm` principal podría extenderse para incluir estos campos, o manejar un segundo `useForm` para el perfil.
Modificar `app/Http/Controllers/Admin/UserController.php` (método `edit`):
Si el usuario es un revendedor, cargar su `resellerProfile` (ej. `$user->load('resellerProfile');`) y pasarlo a la vista.
Modificar `app/Http/Controllers/Admin/UserController.php` (método `update`):
Si el usuario es un revendedor y se envían datos del perfil, validar y actualizar/crear el `ResellerProfile` asociado.
Actualizar `app/Http/Requests/Admin/UpdateUserRequest.php` para incluir reglas de validación para los campos de `ResellerProfile` (ej. `reseller_profile.brand_name`).
**Verificación:\*\* Al editar un usuario revendedor, se pueden ver y modificar los datos de su perfil.

### 4.4. Migración de la Tabla `configurable_option_groups`

Contexto:\*\* Los productos pueden tener opciones configurables (ej: Sistema Operativo para un VPS). Estas opciones se agrupan.
Crear la migración para la tabla `configurable_option_groups`:

```bash
php artisan make:migration create_configurable_option_groups_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
// database/migrations/xxxx_xx_xx_xxxxxx_create_configurable_option_groups_table.php
Schema::create('configurable_option_groups', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade'); // Puede ser global (NULL) o específico de un producto
$table->string('name');
$table->string('description')->nullable();
$table->integer('display_order')->default(0);
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `configurable_option_groups` existe.

---

¡Perfiles de Revendedor y Grupos de Opciones Configurables (Base) Implementados!\*\*
El siguiente paso será crear el modelo para `ConfigurableOptionGroup`, sus relaciones, y empezar a construir la interfaz para gestionarlos.

````

Y, por supuesto, antes de sumergirte en la Tarea 07, vamos a guardar todo el excelente trabajo que has hecho hasta ahora en GitHub:

1.  **Verificar el estado de tus archivos:**
```bash
git status
````

2.  **Añadir los archivos al "staging area":**

```bash
git add .
```

3.  **Hacer "commit" de los cambios:**

```bash
git commit -m "Tarea 06 completada: Gestión de precios de productos implementada"
```

4.  **Subir los cambios a GitHub (push):**

```bash
git push origin main
```

(O el nombre de tu rama si es diferente).

¡Listo! Tu progreso está seguro y ya tienes el camino trazado para la siguiente etapa. ¡Sigamos adelante cuando estés listo!

# Geminis - Plan de Tareas Detallado - Parte 08

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en las opciones configurables individuales, sus precios, y la vinculación de grupos de opciones a los productos.



### 4.5. Modelo `ConfigurableOptionGroup`

Contexto:\*\* Necesitamos el modelo para la tabla `configurable_option_groups` creada en la Tarea 07.
Crear el modelo `ConfigurableOptionGroup`:

```bash
php artisan make:model ConfigurableOptionGroup
```

En `app/Models/ConfigurableOptionGroup.php`, configurar `$fillable`:

```php
protected $fillable = ['product_id', 'name', 'description', 'display_order'];
```

Definir relación `product()` (un grupo puede pertenecer a un producto específico, o ser global si `product_id` es NULL):

```php
public function product()
{
return $this->belongsTo(Product::class);
}
```

Definir relación `options()` (un grupo tiene muchas opciones):

```php
public function options()
{
return $this->hasMany(ConfigurableOption::class, 'group_id')->orderBy('display_order');
}
```

**Verificación:** Se puede crear y consultar grupos mediante Tinker.

### 4.6. Migración de la Tabla `configurable_options`

Contexto:\*\* Define las opciones individuales dentro de un grupo (Ej: "CentOS", "Ubuntu" para el grupo "Sistema Operativo").
Crear la migración para la tabla `configurable_options`:

```bash
php artisan make:migration create_configurable_options_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('configurable_options', function (Blueprint $table) {
$table->id();
$table->foreignId('group_id')->constrained('configurable_option_groups')->onDelete('cascade');
$table->string('name'); // Nombre visible (ej: "CentOS 7")
$table->string('value')->nullable(); // Valor interno para aprovisionamiento (ej: "centos7")
$table->integer('display_order')->default(0);
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `configurable_options` existe.

### 4.7. Modelo `ConfigurableOption`

Crear el modelo `ConfigurableOption`:

```bash
php artisan make:model ConfigurableOption
```

En `app/Models/ConfigurableOption.php`, configurar `$fillable`:

```php
protected $fillable = ['group_id', 'name', 'value', 'display_order'];
```

Definir relación `group()` (una opción pertenece a un grupo):

```php
public function group()
{
return $this->belongsTo(ConfigurableOptionGroup::class, 'group_id');
}
```

Definir relación `pricings()` (una opción puede tener múltiples precios según el ciclo de facturación del producto base):

```php
public function pricings()
{
return $this->hasMany(ConfigurableOptionPricing::class);
}
```

**Verificación:** Se pueden crear opciones y asociarlas a grupos.

### 4.8. Migración de la Tabla `configurable_option_pricing`

Contexto:\*\* Define los precios para cada opción configurable, vinculados a un ciclo de facturación del producto base.
Crear la migración para la tabla `configurable_option_pricing`:

```bash
php artisan make:migration create_configurable_option_pricing_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('configurable_option_pricing', function (Blueprint $table) {
$table->id();
$table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
$table->foreignId('product_pricing_id')->constrained('product_pricings')->onDelete('cascade'); // Vincula al ciclo de facturación del producto base
$table->decimal('price', 10, 2); // Precio adicional de la opción para el ciclo vinculado
$table->decimal('setup_fee', 10, 2)->default(0.00);
$table->timestamps();
// Constraint: Una opción no puede tener múltiples precios para el mismo ciclo de facturación de producto
$table->unique(['configurable_option_id', 'product_pricing_id'], 'option_product_pricing_unique');
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `configurable_option_pricing` existe.

### 4.9. Modelo `ConfigurableOptionPricing`

Crear el modelo `ConfigurableOptionPricing`:

```bash
php artisan make:model ConfigurableOptionPricing
```

En `app/Models/ConfigurableOptionPricing.php`, configurar `$fillable`:

```php
protected $fillable = ['configurable_option_id', 'product_pricing_id', 'price', 'setup_fee'];
// protected $table = 'configurable_option_pricing'; // Si no sigue la convención plural
```

Definir relación `option()`:

```php
public function option()
{
return $this->belongsTo(ConfigurableOption::class, 'configurable_option_id');
}
```

Definir relación `productPricing()`:

```php
public function productPricing()
{
return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
}
```

**Verificación:** Se pueden crear precios para las opciones configurables.

### 4.10. Migración Tabla Pivote `product_configurable_option_group`

Contexto:\*\* Un producto puede tener múltiples grupos de opciones configurables, y un grupo de opciones configurable (especialmente los globales) puede estar asociado a múltiples productos.
Crear la migración para la tabla pivote `product_configurable_option_group`:

```bash
php artisan make:migration create_product_configurable_option_group_table
```

Modificar el método `up()`:

```php
Schema::create('product_configurable_option_group', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products')->onDelete('cascade');
$table->foreignId('configurable_option_group_id', 'pcog_id_foreign')->constrained('configurable_option_groups')->onDelete('cascade'); // Alias para el nombre de la FK
$table->integer('display_order')->default(0); // Orden de este grupo para este producto específico
$table->timestamps();
$table->unique(['product_id', 'configurable_option_group_id'], 'product_group_unique');
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla pivote existe.

---

¡Modelos y Migraciones Base para Opciones Configurables y sus Precios Implementados!\*\*
La siguiente tarea se centrará en construir las interfaces de administración (CRUDs) para estos nuevos modelos y en cómo se asignan los grupos de opciones a los productos.

### 4.11. CRUD para `ConfigurableOptionGroup` (Admin)
Contexto:** Los administradores necesitan gestionar los grupos de opciones configurables (ej. "Sistema Operativo", "Ubicación del Servidor").
Crear controlador `Admin\ConfigurableOptionGroupController`:
```bash
php artisan make:controller Admin/ConfigurableOptionGroupController --resource --model=ConfigurableOptionGroup
````

Definir rutas resource para `configurable-option-groups` en `routes/web.php` (dentro del grupo `admin`):

```php
Route::resource('configurable-option-groups', AdminConfigurableOptionGroupController::class);
```

Implementar método `index()` en `AdminConfigurableOptionGroupController`:
Listar grupos, paginados. Permitir filtrar por nombre o si son globales/específicos de producto.
Pasar datos a la vista `Admin/ConfigurableOptionGroups/Index.vue`.
Crear vista `resources/js/Pages/Admin/ConfigurableOptionGroups/Index.vue`:
Tabla para mostrar grupos (Nombre, Descripción, ¿Producto asociado?, Orden).
Enlaces para Crear, Editar, Eliminar.
Implementar métodos `create()` y `store()`:
Vista `Admin/ConfigurableOptionGroups/Create.vue` con formulario (nombre, descripción, product_id (opcional, select con productos), display_order).
Validación (usar FormRequest `StoreConfigurableOptionGroupRequest`).
Implementar métodos `edit()` y `update()`:
Vista `Admin/ConfigurableOptionGroups/Edit.vue`.
Validación (usar FormRequest `UpdateConfigurableOptionGroupRequest`).
Implementar método `destroy()`.
Crear `StoreConfigurableOptionGroupRequest` y `UpdateConfigurableOptionGroupRequest`.
(Opcional) Crear `ConfigurableOptionGroupPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue` para "Grupos de Opciones".
**Verificación:** CRUD completo para grupos de opciones funciona.

### 4.12. CRUD para `ConfigurableOption` (Admin - Anidado o en vista de Grupo)

Contexto:** Dentro de cada grupo, se deben poder gestionar las opciones individuales (ej. "CentOS", "Ubuntu").
Decisión:** Se gestionarán desde la vista de edición del `ConfigurableOptionGroup`.
Modificar `Admin/ConfigurableOptionGroups/Edit.vue`:
Añadir una sección para listar las `configurable_options` del grupo actual (`props.configurable_option_group.options`).
Formulario (modal o en línea) para añadir/editar opciones (nombre, valor, orden).
Añadir rutas y métodos en `AdminConfigurableOptionGroupController` para gestionar opciones anidadas:
`storeOption(Request $request, ConfigurableOptionGroup $configurable_option_group)`
`updateOption(Request $request, ConfigurableOptionGroup $configurable_option_group, ConfigurableOption $option)`
`destroyOption(ConfigurableOptionGroup $configurable_option_group, ConfigurableOption $option)`
Implementar lógica en estos métodos (validación, creación, actualización, eliminación de `ConfigurableOption`).
**Verificación:** Se pueden añadir, editar y eliminar opciones dentro de un grupo.

### 4.13. Asignación de Grupos de Opciones Configurables a Productos

Contexto:\*\* Los productos deben poder tener asociados uno o más grupos de opciones configurables.
Modificar `app/Models/Product.php`:
Definir relación `configurableOptionGroups()` (muchos a muchos con `configurable_option_groups` usando la tabla pivote `product_configurable_option_group`).

```php
public function configurableOptionGroups()
{
return $this->belongsToMany(ConfigurableOptionGroup::class, 'product_configurable_option_group')
->withTimestamps()
->withPivot('display_order') // Si quieres acceder al orden de la tabla pivote
->orderBy('pivot_display_order', 'asc'); // Ordenar por el campo en la tabla pivote
}
```

Modificar `app/Models/ConfigurableOptionGroup.php`:
Definir relación `products()` (muchos a muchos con `products`).

```php
public function products()
{
return $this->belongsToMany(Product::class, 'product_configurable_option_group')
->withTimestamps()
->withPivot('display_order');
}
```

Modificar `Admin/Products/Edit.vue`:
Añadir una sección para asignar/desasignar grupos de opciones configurables al producto.
Podría ser un listado de todos los grupos disponibles (globales y los específicos de otros productos, si se permite) con checkboxes.
O un multiselect.
Modificar `AdminProductController@update`:
Procesar la lista de `configurable_option_group_ids` seleccionados.
Usar el método `sync()` en la relación `configurableOptionGroups()` del producto para actualizar las asociaciones.
Si se maneja `display_order` en la tabla pivote, la lógica de `sync()` puede ser más compleja o requerir iterar y usar `attach`/`detach`/`updateExistingPivot`.
Modificar `AdminProductController@edit`:
Cargar los grupos de opciones asignados al producto (`$product->load('configurableOptionGroups')`).
Cargar todos los grupos de opciones disponibles para la selección.
**Verificación:** Se pueden asignar y desasignar grupos de opciones a un producto.

---

¡Gestión de Grupos de Opciones y Opciones (Admin) Implementada!\*\*
El siguiente paso será la gestión de precios para las opciones configurables individuales.

````



Se enfocará en cómo se definen y gestionan los precios para las `ConfigurableOption` individuales, vinculándolos a los ciclos de facturación del producto base.

```diff
# Geminis - Plan de Tareas Detallado - Parte 10

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en la gestión de precios para las opciones configurables individuales.



### 4.14. Gestión de Precios para `ConfigurableOption`
Contexto:** Cada opción configurable (ej. "CentOS 7", "1GB RAM Adicional") puede tener un precio diferente que se suma al precio base del producto, y este precio puede variar según el ciclo de facturación del producto.
Decisión:** La gestión de precios de las opciones se hará desde la vista de edición del `ConfigurableOptionGroup`, anidada bajo cada opción.
Modificar `Admin/ConfigurableOptionGroups/Edit.vue`:
Para cada `ConfigurableOption` listada, añadir una sub-sección o un botón para "Gestionar Precios".
Al gestionar precios para una opción, mostrar una tabla de sus `configurable_option_pricing`.
Formulario (modal o en línea) para añadir/editar precios de la opción:
`product_pricing_id`: Un select que muestre los ciclos de facturación y precios del producto al que pertenece este grupo (o de todos los productos si el grupo es global y se está asignando). Esto es complejo si el grupo es global.
Alternativa más simple para grupos globales:** Los precios de las opciones se definen sin `product_pricing_id` directo, y se asume que se aplican a cualquier ciclo. O se definen precios por ciclo ('monthly', 'annually') directamente en `configurable_option_pricing` sin FK a `product_pricing`.
Decisión para MVP:** Vincular a `product_pricing_id`. Esto significa que un grupo de opciones específico de un producto tendrá opciones cuyos precios se definen para los ciclos de ESE producto. Los grupos globales son más complejos de preciar de esta manera universal.
Reconsideración:** Para que los grupos globales sean reutilizables, la tabla `configurable_option_pricing` podría tener `billing_cycle` y `currency_code` directamente, en lugar de `product_pricing_id`. Luego, al calcular el precio total de un servicio, se buscaría el precio de la opción que coincida con el ciclo del servicio.
Decisión Final (Compromiso):** Mantener `product_pricing_id` por ahora. Los "grupos globales" serán plantillas que, al asignarse a un producto, requerirán que se definan/copien los precios de sus opciones para los ciclos de ese producto específico. O, más simple, los grupos globales no tienen precios predefinidos y se establecen al vincularlos a un producto.
Simplificación Máxima para MVP:** Los precios de las opciones se definen por opción, y se asocian a un `product_pricing_id` del producto al que está vinculado el grupo. Si el grupo es global, esta sección de precios solo se activa cuando el grupo se asocia a un producto.
`price`: Precio adicional de la opción.
`setup_fee`: Tarifa de configuración adicional de la opción.
Modificar `AdminConfigurableOptionGroupController`:
Al editar un grupo (`edit` method), si el grupo está asociado a un producto (`$configurableOptionGroup->product_id` no es NULL), cargar los `product.pricings` de ese producto y pasarlos a la vista para el select de `product_pricing_id`.
Añadir rutas y métodos en `AdminConfigurableOptionGroupController` para gestionar precios de opciones:
`storeOptionPricing(Request $request, ConfigurableOptionGroup $group, ConfigurableOption $option)`
`updateOptionPricing(Request $request, ConfigurableOptionGroup $group, ConfigurableOption $option, ConfigurableOptionPricing $pricing)`
`destroyOptionPricing(ConfigurableOptionGroup $group, ConfigurableOption $option, ConfigurableOptionPricing $pricing)`
Implementar lógica en estos métodos (validación, creación, actualización, eliminación de `ConfigurableOptionPricing`).
Asegurar que `product_pricing_id` pertenezca al producto del grupo (si el grupo es específico de producto).
**Verificación:**
Se pueden añadir, editar y eliminar precios para las opciones configurables.
La validación de unicidad (`configurable_option_id`, `product_pricing_id`) en `configurable_option_pricing` funciona.

### 4.15. (Opcional) Políticas de Acceso para Opciones Configurables y sus Precios
Considerar si se necesitan policies separadas para `ConfigurableOption` y `ConfigurableOptionPricing`, o si la policy de `ConfigurableOptionGroup` (o `ProductPolicy`) es suficiente.
(Si se crean) Generar, registrar e implementar policies.

---
¡Gestión de Precios para Opciones Configurables Implementada!**
Los productos ahora pueden tener una estructura de precios más compleja con opciones adicionales. El siguiente paso es empezar a trabajar con los servicios del cliente.
````

Migración, modelo y CRUD básico (Admin) para `ClientService`.

````diff


Este documento se enfoca en la creación de la entidad `ClientService`, que representa las instancias de productos/servicios contratados por los clientes.



### Fase 5.1. Migración de la Tabla `client_services`
Contexto:** Esta tabla es fundamental, ya que registra cada servicio activo (o inactivo) que un cliente ha contratado.
Crear la migración para la tabla `client_services`:
```bash
php artisan make:migration create_client_services_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('client_services', function (Blueprint $table) {
$table->id();
$table->foreignId('client_id')->constrained('users')->comment('FK a users.id del cliente');
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('FK a users.id del revendedor, si aplica');
$table->foreignId('order_id')->nullable()->constrained('orders')->comment('FK a la orden que originó este servicio');
$table->foreignId('product_id')->constrained('products');
$table->foreignId('product_pricing_id')->constrained('product_pricings')->comment('Ciclo de facturación elegido');
$table->string('domain_name')->nullable()->index();
$table->string('username')->nullable();
$table->text('password_encrypted')->nullable(); // Considerar encriptación real
$table->foreignId('server_id')->nullable()->constrained('servers');
$table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud'])->default('pending')->index();
$table->date('registration_date');
$table->date('next_due_date')->index();
$table->date('termination_date')->nullable();
$table->decimal('billing_amount', 10, 2); // Monto recurrente actual (puede incluir opciones)
$table->text('notes')->nullable();
$table->timestamps();
$table->softDeletes();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `client_services` existe con las columnas correctas.

### 5.2. Modelo `ClientService`

Crear el modelo `ClientService`:

```bash
php artisan make:model ClientService
```

En `app/Models/ClientService.php`, configurar `$fillable` y `$dates` (para `registration_date`, `next_due_date`, `termination_date`).

```php
protected $fillable = [
'client_id', 'reseller_id', 'order_id', 'product_id', 'product_pricing_id',
'domain_name', 'username', 'password_encrypted', 'server_id', 'status',
'registration_date', 'next_due_date', 'termination_date', 'billing_amount', 'notes',
];
protected $casts = [
'registration_date' => 'date',
'next_due_date' => 'date',
'termination_date' => 'date',
'password_encrypted' => 'encrypted', // Si usas el cast 'encrypted' de Laravel
];
// protected $dates = ['registration_date', 'next_due_date', 'termination_date']; // Alternativa a $casts para fechas
```

Definir relaciones: `client()` (belongsTo User), `reseller()` (belongsTo User), `product()` (belongsTo Product), `productPricing()` (belongsTo ProductPricing), `server()` (belongsTo Server), `order()` (belongsTo Order).
Definir relación `configurableOptionsSelected()` (muchos a muchos con `configurable_options` a través de `client_service_configurable_options`).
**Verificación:** Se pueden crear servicios y asociarlos mediante Tinker.

### 5.3. CRUD Básico para `ClientService` (Admin)

Contexto:\*\* Los administradores deben poder ver y gestionar (al menos inicialmente de forma manual) los servicios de los clientes.
Crear controlador `Admin\ClientServiceController`:

```bash
php artisan make:controller Admin/ClientServiceController --resource --model=ClientService
```

Definir rutas resource para `client-services` en `routes/web.php` (admin).
Implementar `index()`: Listar servicios, paginados, con filtros (cliente, producto, estado). Vista `Admin/ClientServices/Index.vue`.
Implementar `create()` y `store()`: Formulario para creación manual de servicios. Vista `Admin/ClientServices/Create.vue`. (Seleccionar cliente, producto, ciclo, etc.).
Implementar `edit()` y `update()`: Formulario para edición. Vista `Admin/ClientServices/Edit.vue`.
Implementar `destroy()` (probablemente soft delete).
Crear FormRequests (`StoreClientServiceRequest`, `UpdateClientServiceRequest`).
(Opcional) Crear `ClientServicePolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** CRUD básico de servicios funciona desde el panel de admin.

---

¡Gestión Base de Servicios de Cliente (Admin) Implementada!\*\*
El siguiente paso será detallar la lógica de estados de los servicios y cómo se visualizan y gestionan desde el panel del cliente.

````



Se enfocará en la lógica de los diferentes estados de un servicio y cómo el cliente comienza a ver sus servicios.

```diff
# Geminis - Plan de Tareas Detallado - Parte 12

Este documento se enfoca en la lógica de estados para los `ClientService` y en comenzar la visualización de estos servicios en el panel del cliente.



### 5.4. Lógica de Estados para `ClientService`
Contexto:** Los servicios pasan por diferentes estados (`pending`, `active`, `suspended`, `terminated`, `cancelled`, `fraud`). Necesitamos definir cómo y cuándo cambian estos estados.
En `app/Models/ClientService.php`, añadir métodos para cambiar de estado (ej. `activate()`, `suspend()`, `terminate()`).
Estos métodos podrían también disparar eventos (ej. `ServiceActivated`, `ServiceSuspended`) para futuras automatizaciones (aprovisionamiento, emails).
```php
// Ejemplo en ClientService.php
public function activate() {
$this->status = 'active';
// Podría ajustar next_due_date si es la primera activación
$this->save();
// event(new ServiceActivated($this));
}
// ... otros métodos de cambio de estado
````

En `Admin/ClientServices/Edit.vue`, añadir botones o acciones para cambiar el estado del servicio manualmente por un administrador.
Actualizar `AdminClientServiceController@update` para manejar cambios de estado si se envían desde el formulario de edición, o crear métodos específicos para acciones de estado.
**Verificación:** Un administrador puede cambiar el estado de un servicio.

### 5.5. Migración Tabla `client_service_configurable_options`

Contexto:\*\* Tabla pivote para registrar qué opciones configurables específicas (y con qué precio de opción) ha seleccionado un cliente para un servicio particular.
Crear la migración:

```bash
php artisan make:migration create_client_service_configurable_options_table
```

Modificar el método `up()` según `Geminis_Estructura.md`.

```php
Schema::create('client_service_configurable_options', function (Blueprint $table) {
$table->id();
$table->foreignId('client_service_id')->constrained('client_services')->onDelete('cascade');
$table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
// Almacenamos el precio de la opción en el momento de la contratación/renovación
// Esto podría venir de configurable_option_pricing o ser un precio ad-hoc si la estructura de precios de opciones cambia.
// Por ahora, asumimos que se selecciona una 'configurable_option_pricing_id' si existe.
$table->foreignId('configurable_option_pricing_id')->nullable()->constrained('configurable_option_pricing')->onDelete('set null');
$table->decimal('price_override', 10, 2)->nullable()->comment('Precio de la opción si se anula el de configurable_option_pricing');
$table->integer('quantity')->default(1); // Para opciones que pueden tener cantidad (ej. licencias adicionales)
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla existe.

### 5.6. Panel de Cliente: Listado de Servicios (Básico)

Contexto:** Los clientes necesitan ver los servicios que han contratado.
Crear `Client/DashboardController.php` o `Client/ServiceController.php` si no existen.
En el controlador del cliente, método `index` o `services`, obtener los `client_services` del usuario autenticado.
Filtrar por `client_id = auth()->id()`.
Cargar relaciones necesarias (producto, precios).
Crear vista `resources/js/Pages/Client/Services/Index.vue`.
Mostrar una tabla/lista de los servicios del cliente (Nombre del producto, Dominio, Próxima Fecha de Vencimiento, Estado, Precio).
Definir rutas en `routes/web.php` para el panel de cliente (ej. `/client/services`).
Asegurar middleware de autenticación y que el rol sea 'client' (o que `EnsureUserIsClient` exista y funcione).
Añadir enlace en `ClientLayout.vue` (si existe) o en `AppLayout.vue` (si es el layout general para usuarios autenticados).
**Verificación:\*\* Un cliente logueado puede ver una lista de sus servicios.

---

¡Lógica de Estados de Servicio y Listado Básico en Panel de Cliente Implementados!\*\*
Los siguientes pasos se enfocarán en el proceso de órdenes y cómo estas generan servicios.

````



Migración y modelos para `Orders` y `OrderItems`.

```diff
# Geminis - Plan de Tareas Detallado - Parte 13

Este documento se enfoca en la creación de las entidades `Order` y `OrderItem`, que son el primer paso en el proceso de compra de un cliente.

## Fase 6: Proceso de Compra y Facturación

### 6.1. Migración de la Tabla `orders`
Contexto:** Registra la intención de compra de un cliente antes de que se genere una factura o se aprovisione un servicio.
Crear la migración para la tabla `orders`:
```bash
php artisan make:migration create_orders_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('orders', function (Blueprint $table) {
$table->id();
$table->foreignId('client_id')->constrained('users');
$table->foreignId('reseller_id')->nullable()->constrained('users');
$table->string('order_number')->unique();
$table->foreignId('invoice_id')->nullable()->unique()->constrained('invoices')->onDelete('set null'); // Se llenará después de generar la factura
$table->timestamp('order_date');
$table->enum('status', ['pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled'])->default('pending_payment')->index();
$table->decimal('total_amount', 10, 2);
$table->string('currency_code', 3);
$table->string('payment_gateway_slug')->nullable()->index();
$table->ipAddress('ip_address')->nullable();
$table->text('notes')->nullable();
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `orders` existe.

### 6.2. Modelo `Order`

Crear el modelo `Order`:

```bash
php artisan make:model Order
```

En `app/Models/Order.php`, configurar `$fillable` y `$casts` (para `order_date`).

```php
protected $fillable = [
'client_id', 'reseller_id', 'order_number', 'invoice_id', 'order_date', 'status',
'total_amount', 'currency_code', 'payment_gateway_slug', 'ip_address', 'notes',
];
protected $casts = ['order_date' => 'datetime'];
```

Definir relaciones: `client()` (belongsTo User), `reseller()` (belongsTo User), `invoice()` (belongsTo Invoice), `items()` (hasMany OrderItem).
**Verificación:** Se pueden crear órdenes mediante Tinker.

### 6.3. Migración de la Tabla `order_items`

Contexto:\*\* Detalla cada producto o servicio incluido en una orden.
Crear la migración para la tabla `order_items`:

```bash
php artisan make:migration create_order_items_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('order_items', function (Blueprint $table) {
$table->id();
$table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
$table->foreignId('product_id')->constrained('products');
$table->foreignId('product_pricing_id')->constrained('product_pricings'); // Ciclo de facturación elegido
$table->enum('item_type', ['product', 'addon', 'domain_registration', 'domain_renewal', 'domain_transfer', 'configurable_option'])->index();
$table->string('description'); // Ej: "Web Hosting - Plan Básico (Mensual)"
$table->integer('quantity')->default(1);
$table->decimal('unit_price', 10, 2);
$table->decimal('setup_fee', 10, 2)->default(0.00);
$table->decimal('total_price', 10, 2); // (unit_price * quantity) + setup_fee
$table->string('domain_name')->nullable(); // Si el ítem es un dominio
$table->integer('registration_period_years')->nullable(); // Para dominios
$table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null'); // Se llenará después de aprovisionar
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `order_items` existe.

### 6.4. Modelo `OrderItem`

Crear el modelo `OrderItem`: `php artisan make:model OrderItem`
Configurar `$fillable`.
Definir relaciones: `order()` (belongsTo Order), `product()` (belongsTo Product), `productPricing()` (belongsTo ProductPricing), `clientService()` (belongsTo ClientService).
**Verificación:** Se pueden crear ítems de orden y asociarlos a órdenes.

---

¡Modelos Base para Órdenes Implementados!\*\*
La siguiente tarea se centrará en el proceso de creación de órdenes por parte del cliente y su listado.

````



Cómo los clientes crean órdenes y cómo se listan para admins y clientes.

```diff
# Geminis - Plan de Tareas Detallado - Parte 14

Este documento se enfoca en el proceso de creación de órdenes por parte del cliente y el listado de estas órdenes tanto para clientes como para administradores.



### 6.5. Proceso de Creación de Órdenes (Cliente)
Contexto:** Los clientes deben poder seleccionar productos y añadirlos a un "carrito" o directamente generar una orden.
Decisión MVP:** Simplificar, no habrá un carrito persistente. El cliente seleccionará un producto y sus opciones, y esto generará una orden directamente.
Crear `Client\OrderController.php` (o añadir a un controlador existente como `Client\ProductController.php`).
Método `showOrderForm(Product $product)`:
Muestra una página donde el cliente puede ver el producto, seleccionar un ciclo de facturación (`product_pricing_id`).
Si el producto tiene grupos de opciones configurables (`product->configurableOptionGroups`), listarlos y permitir al cliente seleccionar opciones.
Calcular el precio total preliminar (producto base + opciones seleccionadas).
Vista `Client/Orders/Create.vue`.
Método `placeOrder(Request $request, Product $product)`:
Validar la selección del cliente (ciclo de facturación, opciones configurables válidas).
Crear el registro en la tabla `orders`.
Crear los registros correspondientes en `order_items` (uno para el producto base, y uno por cada opción configurable seleccionada con su precio).
Generar un `order_number` único.
Calcular `total_amount` final.
Redirigir al cliente a una página de confirmación de orden o directamente a la pasarela de pago (futuro). Por ahora, a una página de "Orden Recibida".
Crear rutas en `routes/web.php` para el cliente (ej. `/order/product/{product}`, `/order/place/{product}`).
**Verificación:** Un cliente puede seleccionar un producto, elegir ciclo/opciones y generar una orden con sus ítems.

### 6.6. Listado de Órdenes (Admin)
Crear `Admin\OrderController.php`:
```bash
php artisan make:controller Admin/OrderController --resource --model=Order
````

Definir rutas resource para `orders` en `routes/web.php` (admin).
Implementar `index()`:
Listar todas las órdenes, paginadas.
Permitir filtros (por cliente, estado, fecha).
Mostrar información clave (Número de Orden, Cliente, Fecha, Total, Estado).
Vista `Admin/Orders/Index.vue`.
Implementar `show()`:
Mostrar detalles de una orden, incluyendo sus `order_items`.
Cargar relaciones `client`, `items.product`, `items.productPricing`.
Vista `Admin/Orders/Show.vue`.
(Opcional) Implementar `edit()` y `update()` para permitir a los admins modificar ciertos aspectos de una orden (ej. estado, notas).
(Opcional) Implementar `destroy()` (cancelar/eliminar orden).
(Opcional) Crear `OrderPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** Los administradores pueden ver y gestionar órdenes.

### 6.7. Listado de Órdenes (Cliente)

En `Client\OrderController.php` (o similar), método `index()`:
Obtener las órdenes del cliente autenticado (`client_id = auth()->id()`).
Paginadas y ordenadas por fecha.
Vista `Client/Orders/Index.vue`.
En `Client\OrderController.php`, método `show(Order $order)`:
Asegurar que la orden pertenezca al cliente autenticado.
Mostrar detalles de la orden y sus ítems.
Vista `Client/Orders/Show.vue`.
Definir rutas en `routes/web.php` para el cliente (ej. `/client/orders`, `/client/orders/{order}`).
Añadir enlace en `ClientLayout.vue` o `AppLayout.vue`.
**Verificación:** Un cliente puede ver su historial de órdenes y los detalles de cada una.

---

¡Proceso Básico de Órdenes Implementado!\*\*
El siguiente paso es la generación de facturas a partir de estas órdenes.

````



Migración y modelos para `Invoices` y `InvoiceItems`.

```diff
# Geminis - Plan de Tareas Detallado - Parte 15

Este documento se enfoca en la creación de las entidades `Invoice` e `InvoiceItem`, que representan las obligaciones de pago formales.



### 6.8. Migración de la Tabla `invoices`
Contexto:** Almacena las facturas generadas para los clientes, ya sea a partir de una orden o para renovaciones de servicios.
Crear la migración para la tabla `invoices`:
```bash
php artisan make:migration create_invoices_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('invoices', function (Blueprint $table) {
$table->id();
$table->foreignId('client_id')->constrained('users');
$table->foreignId('reseller_id')->nullable()->constrained('users');
// $table->foreignId('order_id')->nullable()->unique()->constrained('orders')->onDelete('set null'); // Ya está en orders.invoice_id
$table->string('invoice_number')->unique();
$table->date('issue_date');
$table->date('due_date')->index();
$table->date('paid_date')->nullable();
$table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections'])->default('unpaid')->index();
$table->decimal('subtotal', 10, 2);
$table->string('tax1_name')->nullable();
$table->decimal('tax1_rate', 5, 2)->nullable(); // Ej: 21.00 para 21%
$table->decimal('tax1_amount', 10, 2)->nullable();
$table->string('tax2_name')->nullable();
$table->decimal('tax2_rate', 5, 2)->nullable();
$table->decimal('tax2_amount', 10, 2)->nullable();
$table->decimal('total_amount', 10, 2);
$table->string('currency_code', 3);
$table->text('notes_to_client')->nullable();
$table->text('admin_notes')->nullable();
$table->timestamps();
$table->softDeletes();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `invoices` existe.

### 6.9. Modelo `Invoice`

Crear el modelo `Invoice`: `php artisan make:model Invoice`
Configurar `$fillable`, `$casts` (para fechas).
Definir relaciones: `client()`, `reseller()`, `order()` (hasOne o belongsTo, dependiendo de la FK principal), `items()` (hasMany InvoiceItem), `transactions()` (hasMany Transaction).
**Verificación:** Se pueden crear facturas mediante Tinker.

### 6.10. Migración de la Tabla `invoice_items`

Contexto:\*\* Detalla los conceptos facturados en cada factura.
Crear la migración para la tabla `invoice_items`:

```bash
php artisan make:migration create_invoice_items_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('invoice_items', function (Blueprint $table) {
$table->id();
$table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
$table->foreignId('client_service_id')->nullable()->constrained('client_services')->onDelete('set null')->comment('Para ítems de renovación');
$table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null')->comment('Para ítems originados de una orden');
$table->string('description'); // Ej: "Web Hosting - Plan Básico (Renovación Mensual)"
$table->integer('quantity')->default(1);
$table->decimal('unit_price', 10, 2);
$table->decimal('total_price', 10, 2); // unit_price * quantity
$table->boolean('taxable')->default(true);
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `invoice_items` existe.

### 6.11. Modelo `InvoiceItem`

Crear el modelo `InvoiceItem`: `php artisan make:model InvoiceItem`
Configurar `$fillable`.
Definir relaciones: `invoice()` (belongsTo Invoice), `clientService()` (belongsTo ClientService), `orderItem()` (belongsTo OrderItem).
**Verificación:** Se pueden crear ítems de factura.

---

¡Modelos Base para Facturas Implementados!\*\*
La siguiente tarea se centrará en la lógica de generación de facturas y su listado.

````



Lógica para generar facturas desde órdenes y para renovaciones. Listados para admin y cliente.

```diff
# Geminis - Plan de Tareas Detallado - Parte 16

Este documento se enfoca en la lógica de generación de facturas y su visualización.



### 6.12. Generación de Facturas a partir de Órdenes
Contexto:** Cuando una orden es creada (o marcada para facturar), se debe generar una factura correspondiente.
Crear un servicio o un método en `OrderService` o `InvoiceService` para `generateInvoiceFromOrder(Order $order)`.
Este método creará un registro en `invoices`.
Copiará los `order_items` a `invoice_items`, ajustando descripciones si es necesario.
Calculará subtotal, impuestos (si aplica), y total para la factura.
Generará un `invoice_number` único.
Actualizará `orders.invoice_id` con el ID de la nueva factura.
Establecer `issue_date` y `due_date` (ej. `due_date` podría ser X días desde `issue_date`).
Modificar `Client\OrderController@placeOrder` (o donde se complete la orden) para llamar a este servicio/método de generación de factura.
**Verificación:** Al completar una orden, se genera una factura asociada con sus ítems.

### 6.13. Generación de Facturas para Renovaciones (Conceptual - Job Futuro)
Contexto:** Los servicios (`client_services`) con `next_due_date` próxima necesitan que se les genere una factura de renovación.
(Conceptual) Planificar un Job de Laravel (`GenerateRenewalInvoicesJob`) que se ejecute diariamente.
Buscará `client_services` activos cuya `next_due_date` esté dentro de un umbral (ej. en los próximos X días).
Para cada servicio, generará una nueva factura en estado 'unpaid'.
Los `invoice_items` se basarán en el `product_pricing_id` del servicio y las `client_service_configurable_options` activas.
Actualizará `client_services.next_due_date` para el siguiente ciclo después de generar la factura (o después de que se pague).
Nota:** La implementación completa de este Job puede ser una tarea posterior, pero es bueno tenerla en mente.

### 6.14. Listado de Facturas (Admin)
Crear `Admin\InvoiceController.php`:
```bash
php artisan make:controller Admin/InvoiceController --resource --model=Invoice
````

Definir rutas resource para `invoices` en `routes/web.php` (admin).
Implementar `index()`: Listar facturas, paginadas, con filtros (cliente, estado, rango de fechas). Vista `Admin/Invoices/Index.vue`.
Implementar `show()`: Mostrar detalles de una factura, incluyendo `invoice_items`. Vista `Admin/Invoices/Show.vue`.
(Opcional) Implementar `edit()` y `update()` para modificar facturas (con restricciones, ej. solo si no está pagada).
(Opcional) Crear `InvoicePolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** Administradores pueden ver y gestionar facturas.

### 6.15. Listado de Facturas (Cliente)

En `Client\InvoiceController.php` (o similar), método `index()`:
Obtener las facturas del cliente autenticado.
Vista `Client/Invoices/Index.vue`.
En `Client\InvoiceController.php`, método `show(Invoice $invoice)`:
Asegurar que la factura pertenezca al cliente.
Mostrar detalles. Vista `Client/Invoices/Show.vue`.
Definir rutas en `routes/web.php` para el cliente.
Añadir enlace en `ClientLayout.vue` o `AppLayout.vue`.
**Verificación:** Clientes pueden ver su historial de facturas.

### 6.16. (Opcional) Descarga de Factura en PDF

Contexto:** Los clientes (y administradores) a menudo necesitan una versión en PDF de la factura.
Investigar un paquete de generación de PDF para Laravel (ej. `barryvdh/laravel-dompdf` o `spatie/laravel-pdf`).
Crear un método en `InvoiceController` (tanto Admin como Client) `downloadPdf(Invoice $invoice)`.
Generar una vista Blade simple para el formato de la factura.
Usar el paquete de PDF para renderizar esa vista como PDF y ofrecerla para descarga.
Añadir botón "Descargar PDF" en las vistas `Show.vue` de facturas.
**Verificación:\*\* Se puede descargar una factura en formato PDF.

---

¡Generación y Listado de Facturas Implementado!\*\*
El siguiente paso es registrar los pagos para estas facturas.

````



Migración, modelo y registro manual de transacciones.

```diff
# Geminis - Plan de Tareas Detallado - Parte 17

Este documento se enfoca en la entidad `Transaction`, que registra todos los movimientos de dinero.



### 6.17. Migración de la Tabla `transactions`
Contexto:** Registra todos los pagos, reembolsos u otras transacciones financieras.
Crear la migración para la tabla `transactions`:
```bash
php artisan make:migration create_transactions_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('transactions', function (Blueprint $table) {
$table->id();
$table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
$table->foreignId('client_id')->constrained('users');
$table->foreignId('reseller_id')->nullable()->constrained('users');
$table->string('gateway_slug')->index()->comment('Ej: paypal, stripe, manual_credit');
$table->string('gateway_transaction_id')->nullable()->index()->comment('ID de la transacción en la pasarela');
$table->enum('type', ['payment', 'refund', 'chargeback', 'credit_added', 'credit_used'])->index();
$table->decimal('amount', 10, 2);
$table->string('currency_code', 3);
$table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed')->index();
$table->decimal('fees_amount', 10, 2)->nullable()->comment('Comisiones de la pasarela');
$table->string('description')->nullable();
$table->timestamp('transaction_date');
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `transactions` existe.

### 6.18. Modelo `Transaction`

Crear el modelo `Transaction`: `php artisan make:model Transaction`
Configurar `$fillable`, `$casts` (para `transaction_date`).
Definir relaciones: `invoice()` (belongsTo Invoice), `client()` (belongsTo User), `reseller()` (belongsTo User).
**Verificación:** Se pueden crear transacciones mediante Tinker.

### 6.19. Registro Manual de Pagos (Admin)

Contexto:** Los administradores deben poder registrar pagos manualmente (ej. transferencia bancaria).
En `Admin\InvoiceController@show` (o en `Admin/Invoices/Show.vue`), añadir un botón "Registrar Pago Manual".
Este botón podría abrir un modal o llevar a un formulario simple para registrar una transacción.
Campos: Monto, Fecha de transacción, Gateway (manual), Descripción/Referencia.
Crear un método en `Admin\InvoiceController` (ej. `addManualPayment(Request $request, Invoice $invoice)`).
Validar los datos.
Crear un registro en `transactions` asociado a la factura.
Actualizar el estado de la `invoice` a 'paid' si el monto cubre el total.
Actualizar `invoice.paid_date`.
Si el pago activa servicios, cambiar el estado de los `client_services` asociados a la orden de la factura (si aplica).
**Verificación:\*\* Un administrador puede registrar un pago manual para una factura, y el estado de la factura se actualiza.

### 6.20. Listado de Transacciones (Admin)

Crear `Admin\TransactionController.php` (solo método `index` por ahora).
Ruta para `admin.transactions.index`.
Implementar `index()`: Listar todas las transacciones, paginadas, con filtros. Vista `Admin/Transactions/Index.vue`.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** Los administradores pueden ver un listado de todas las transacciones.

---

¡Gestión Básica de Transacciones Implementada!\*\*
El siguiente paso es comenzar con el sistema de soporte. La integración con pasarelas de pago reales será una fase posterior.

````



Migraciones y modelos para `SupportDepartments` y `SupportTickets`.

```diff
# Geminis - Plan de Tareas Detallado - Parte 18

Este documento inicia la implementación del sistema de soporte, comenzando con los departamentos y la estructura base de los tickets.

## Fase 7: Sistema de Soporte

### 7.1. Migración de la Tabla `support_departments`
Contexto:** Los tickets de soporte se organizan en departamentos.
Crear la migración para la tabla `support_departments`:
```bash
php artisan make:migration create_support_departments_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('support_departments', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('email_address')->nullable()->unique()->comment('Para crear tickets por email');
$table->boolean('is_public')->default(true)->comment('Visible para clientes');
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para deptos. globales');
$table->foreignId('auto_assign_user_id')->nullable()->constrained('users')->comment('Agente asignado por defecto');
$table->integer('display_order')->default(0);
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `support_departments` existe.

### 7.2. Modelo `SupportDepartment`

Crear el modelo `SupportDepartment`: `php artisan make:model SupportDepartment`
Configurar `$fillable`.
Definir relaciones: `reseller()` (belongsTo User, opcional), `autoAssignUser()` (belongsTo User, opcional), `tickets()` (hasMany SupportTicket).
**Verificación:** Se pueden crear departamentos mediante Tinker.

### 7.3. Migración de la Tabla `support_tickets`

Contexto:\*\* Almacena los tickets de soporte creados por los clientes o el personal.
Crear la migración para la tabla `support_tickets`:

```bash
php artisan make:migration create_support_tickets_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('support_tickets', function (Blueprint $table) {
$table->id();
$table->string('ticket_number')->unique();
$table->foreignId('client_id')->constrained('users');
$table->foreignId('reseller_id')->nullable()->constrained('users');
$table->foreignId('department_id')->constrained('support_departments');
$table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->comment('Agente asignado');
$table->foreignId('client_service_id')->nullable()->constrained('client_services')->comment('Servicio relacionado, si aplica');
$table->string('subject');
$table->enum('status', ['open', 'client_reply', 'staff_reply', 'on_hold', 'in_progress', 'closed'])->default('open')->index();
$table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
$table->timestamp('last_reply_at')->nullable();
$table->string('last_replier_name')->nullable(); // O FK a users si siempre es un usuario del sistema
$table->timestamps();
$table->softDeletes();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `support_tickets` existe.

### 7.4. Modelo `SupportTicket`

Crear el modelo `SupportTicket`: `php artisan make:model SupportTicket`
Configurar `$fillable`, `$casts` (para `last_reply_at`).
Definir relaciones: `client()`, `reseller()`, `department()`, `assignedToUser()`, `clientService()`, `replies()` (hasMany SupportTicketReply).
En el evento `creating` del modelo, generar un `ticket_number` único.
**Verificación:** Se pueden crear tickets mediante Tinker.

---

¡Modelos Base para Departamentos y Tickets de Soporte Implementados!\*\*
La siguiente tarea se centrará en el CRUD para departamentos y la creación/listado de tickets.

````



CRUD para `SupportDepartments` (Admin). Creación y listado de `SupportTickets` (Cliente/Admin).

```diff
# Geminis - Plan de Tareas Detallado - Parte 19

Este documento se enfoca en la gestión de Departamentos de Soporte y la funcionalidad inicial para que los clientes y administradores manejen los Tickets de Soporte.



### 7.5. CRUD para `SupportDepartment` (Admin)
Contexto:** Los administradores necesitan gestionar los departamentos de soporte.
Crear `Admin\SupportDepartmentController`:
```bash
php artisan make:controller Admin/SupportDepartmentController --resource --model=SupportDepartment
````

Definir rutas resource para `support-departments` en `routes/web.php` (admin).
Implementar `index()`: Listar departamentos. Vista `Admin/SupportDepartments/Index.vue`.
Implementar `create()` y `store()`: Formulario para crear departamentos (nombre, email, público, revendedor (opcional), auto-asignar (opcional)). Vista `Admin/SupportDepartments/Create.vue`.
Implementar `edit()` y `update()`: Formulario para editar. Vista `Admin/SupportDepartments/Edit.vue`.
Implementar `destroy()`.
Crear FormRequests (`StoreSupportDepartmentRequest`, `UpdateSupportDepartmentRequest`).
(Opcional) Crear `SupportDepartmentPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** CRUD de departamentos de soporte funciona.

### 7.6. Creación de Tickets de Soporte (Cliente)

Contexto:** Los clientes deben poder abrir nuevos tickets de soporte.
Crear `Client\SupportTicketController.php` (o añadir a un controlador de cliente existente).
Método `create()`:
Obtener los departamentos de soporte públicos (y los del revendedor del cliente, si aplica).
Obtener los servicios activos del cliente (`client_services`) para el select "Servicio Relacionado".
Pasar datos a la vista `Client/SupportTickets/Create.vue`.
Vista `Client/SupportTickets/Create.vue`:
Formulario con campos: Departamento (select), Servicio Relacionado (select, opcional), Asunto, Prioridad (select), Mensaje (textarea), Adjuntos (opcional, futuro).
Método `store(Request $request)`:
Validar datos.
Crear el `SupportTicket` (asignar `client_id`, `reseller_id` si aplica, `department_id`, etc.).
Crear el primer `SupportTicketReply` con el mensaje del cliente.
Actualizar `last_reply_at` y `last_replier_name` en el ticket.
(Futuro) Enviar notificaciones por email.
Redirigir al cliente a la vista del ticket recién creado.
Definir rutas en `routes/web.php` para el cliente (ej. `/client/tickets/create`, `/client/tickets`).
**Verificación:\*\* Un cliente puede crear un nuevo ticket de soporte.

### 7.7. Listado de Tickets de Soporte (Cliente)

En `Client\SupportTicketController.php`, método `index()`:
Obtener los tickets del cliente autenticado.
Paginados y ordenados (ej. por última respuesta).
Vista `Client/SupportTickets/Index.vue` (Tabla: Número Ticket, Asunto, Departamento, Estado, Última Actualización).
Añadir enlace "Mis Tickets" en `ClientLayout.vue` o `AppLayout.vue`.
**Verificación:** Un cliente puede ver una lista de sus tickets.

### 7.8. Listado de Tickets de Soporte (Admin)

Crear `Admin\SupportTicketController.php`:

```bash
php artisan make:controller Admin/SupportTicketController --resource --model=SupportTicket
```

Definir rutas resource para `support-tickets` en `routes/web.php` (admin).
Implementar `index()`:
Listar todos los tickets, paginados.
Filtros (departamento, estado, prioridad, cliente, agente asignado).
Vista `Admin/SupportTickets/Index.vue`.
(Opcional) Crear `SupportTicketPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** Administradores pueden ver todos los tickets.

---

¡Gestión Base de Tickets de Soporte (Creación y Listados) Implementada!\*\*
La siguiente tarea se centrará en las respuestas a los tickets y la visualización detallada de los mismos.

````



Migraciones y modelos para `SupportTicketReplies` y `SupportTicketAttachments`. Funcionalidad de respuesta.

```diff
# Geminis - Plan de Tareas Detallado - Parte 20

Este documento se enfoca en la funcionalidad de respuestas para los tickets de soporte y la gestión de archivos adjuntos.



### 7.9. Migración de la Tabla `support_ticket_replies`
Contexto:** Almacena cada respuesta individual a un ticket de soporte.
Crear la migración:
```bash
php artisan make:migration create_support_ticket_replies_table
````

Modificar el método `up()` según `Geminis_Estructura.md`.

```php
Schema::create('support_ticket_replies', function (Blueprint $table) {
$table->id();
$table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
$table->foreignId('user_id')->nullable()->constrained('users')->comment('Usuario que respondió (cliente o staff)');
$table->text('message');
$table->ipAddress('ip_address')->nullable();
$table->boolean('is_staff_reply')->default(false); // True si la respuesta es de un admin/reseller staff
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `support_ticket_replies` existe.

### 7.10. Modelo `SupportTicketReply`

Crear el modelo `SupportTicketReply`: `php artisan make:model SupportTicketReply`
Configurar `$fillable`.
Definir relaciones: `ticket()` (belongsTo SupportTicket), `user()` (belongsTo User, el autor de la respuesta).
**Verificación:** Se pueden crear respuestas mediante Tinker.

### 7.11. Migración de la Tabla `support_ticket_attachments`

Contexto:\*\* Almacena los archivos adjuntos a los tickets o a sus respuestas.
Crear la migración:

```bash
php artisan make:migration create_support_ticket_attachments_table
```

Modificar el método `up()` según `Geminis_Estructura.md`.

```php
Schema::create('support_ticket_attachments', function (Blueprint $table) {
$table->id();
$table->foreignId('reply_id')->nullable()->constrained('support_ticket_replies')->onDelete('cascade');
$table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('cascade')->comment('Si se adjunta al crear ticket, antes de la primera respuesta');
$table->string('file_name_original');
$table->string('file_path_stored'); // Usar Laravel File Storage
$table->string('mime_type');
$table->unsignedInteger('file_size_bytes');
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `support_ticket_attachments` existe.

### 7.12. Modelo `SupportTicketAttachment`

Crear el modelo `SupportTicketAttachment`: `php artisan make:model SupportTicketAttachment`
Configurar `$fillable`.
Definir relaciones: `reply()` (belongsTo SupportTicketReply), `ticket()` (belongsTo SupportTicket).
**Verificación:** Se pueden crear registros de adjuntos.

### 7.13. Funcionalidad de Respuesta a Tickets (Cliente y Admin)

Modificar `Client\SupportTicketController@show(SupportTicket $ticket)`:
Cargar el ticket con sus respuestas (`$ticket->load('replies.user', 'replies.attachments')`).
Pasar datos a la vista `Client/SupportTickets/Show.vue`.
Vista `Client/SupportTickets/Show.vue`:
Mostrar detalles del ticket y el historial de respuestas.
Formulario para que el cliente añada una nueva respuesta (mensaje, adjuntos opcionales).
Método `Client\SupportTicketController@addReply(Request $request, SupportTicket $ticket)`:
Validar respuesta. Crear `SupportTicketReply`. Actualizar estado y `last_reply_at` del ticket.
Repetir lógica similar para `Admin\SupportTicketController` y vistas `Admin/SupportTickets/Show.vue`.
Los admins/staff pueden cambiar el estado del ticket, asignarlo a otro agente.
Implementar subida de archivos adjuntos (usar `Storage` de Laravel).
**Verificación:** Clientes y administradores pueden ver tickets y responder. Se pueden adjuntar archivos.

---

¡Funcionalidad de Respuestas a Tickets Implementada!\*\*
El sistema de soporte ahora es interactivo. Los siguientes pasos se enfocarán en módulos adicionales como dominios y servidores.

````



Migración, modelo y CRUD básico (Admin) para `Domains`.

```diff
# Geminis - Plan de Tareas Detallado - Parte 21

Este documento se enfoca en la entidad `Domain`, para la gestión de nombres de dominio.

## Fase 8: Módulos Adicionales

### 8.1. Migración de la Tabla `domains`
Contexto:** Almacena información sobre los dominios registrados o gestionados a través de la plataforma.
Crear la migración para la tabla `domains`:
```bash
php artisan make:migration create_domains_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('domains', function (Blueprint $table) {
$table->id();
$table->foreignId('client_id')->constrained('users');
$table->foreignId('reseller_id')->nullable()->constrained('users');
$table->foreignId('order_id')->nullable()->constrained('orders')->comment('Orden de compra/renovación');
$table->foreignId('client_service_id')->nullable()->unique()->constrained('client_services')->onDelete('set null')->comment('Si se gestiona como un servicio facturable');
$table->string('domain_name')->unique();
$table->string('registrar_module_slug')->nullable()->index()->comment('Módulo de registrador usado');
$table->date('registration_date');
$table->date('expiry_date')->index();
$table->date('next_due_date')->index()->comment('Próxima fecha de pago para renovación');
$table->enum('status', ['pending_registration', 'pending_transfer', 'active', 'expired', 'cancelled', 'fraud'])->default('pending_registration')->index();
$table->boolean('auto_renew_enabled')->default(false);
$table->boolean('id_protection_enabled')->default(false);
$table->text('epp_code_encrypted')->nullable(); // Encriptar
$table->string('nameserver1')->nullable();
$table->string('nameserver2')->nullable();
$table->string('nameserver3')->nullable();
$table->string('nameserver4')->nullable();
$table->text('admin_notes')->nullable();
$table->timestamps();
$table->softDeletes();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `domains` existe.

### 8.2. Modelo `Domain`

Crear el modelo `Domain`: `php artisan make:model Domain`
Configurar `$fillable`, `$casts` (fechas, epp_code_encrypted).
Definir relaciones: `client()`, `reseller()`, `order()`, `clientService()`.
**Verificación:** Se pueden crear dominios mediante Tinker.

### 8.3. CRUD Básico para `Domain` (Admin)

Contexto:\*\* Los administradores deben poder gestionar los registros de dominios.
Crear `Admin\DomainController.php`:

```bash
php artisan make:controller Admin/DomainController --resource --model=Domain
```

Definir rutas resource para `domains` en `routes/web.php` (admin).
Implementar `index()`: Listar dominios, paginados, con filtros. Vista `Admin/Domains/Index.vue`.
Implementar `create()` y `store()`: Formulario para registrar un dominio manualmente. Vista `Admin/Domains/Create.vue`.
Implementar `edit()` y `update()`: Formulario para editar detalles del dominio (fechas, estado, nameservers, etc.). Vista `Admin/Domains/Edit.vue`.
Implementar `destroy()`.
Crear FormRequests (`StoreDomainRequest`, `UpdateDomainRequest`).
(Opcional) Crear `DomainPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** CRUD básico de dominios funciona.

---

¡Gestión Base de Dominios (Admin) Implementada!\*\*
La integración real con registradores de dominios será una fase avanzada. El siguiente paso es la gestión de servidores.

````



Migraciones y modelos para `Servers` y `ServerGroups`. CRUD básico (Admin).

```diff
# Geminis - Plan de Tareas Detallado - Parte 22

Este documento se enfoca en la gestión de Servidores y Grupos de Servidores, fundamentales para el aprovisionamiento de servicios de hosting.



### 8.4. Migración de la Tabla `server_groups`
Contexto:** Agrupa servidores para facilitar la asignación automática de nuevas cuentas de hosting.
Crear la migración para la tabla `server_groups`:
```bash
php artisan make:migration create_server_groups_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('server_groups', function (Blueprint $table) {
$table->id();
$table->string('name')->unique();
$table->enum('fill_type', ['fill_sequentially', 'fill_until_full_then_next', 'random'])->default('fill_until_full_then_next')->comment('Estrategia de asignación');
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `server_groups` existe.

### 8.5. Modelo `ServerGroup`

Crear el modelo `ServerGroup`: `php artisan make:model ServerGroup`
Configurar `$fillable`.
Definir relación `servers()` (hasMany Server).
**Verificación:** Se pueden crear grupos de servidores.

### 8.6. Migración de la Tabla `servers`

Contexto:\*\* Almacena la información de los servidores físicos o virtuales.
Crear la migración para la tabla `servers`:

```bash
php artisan make:migration create_servers_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('servers', function (Blueprint $table) {
$table->id();
$table->string('name')->unique();
$table->string('hostname_or_ip');
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('Si es un servidor de un revendedor');
$table->foreignId('server_group_id')->nullable()->constrained('server_groups')->onDelete('set null');
$table->string('module_slug')->index()->comment('Ej: cpanel, plesk');
$table->string('api_username')->nullable();
$table->text('api_password_or_key_encrypted')->nullable(); // Encriptar
$table->unsignedInteger('api_port')->nullable();
$table->boolean('api_use_ssl')->default(true);
$table->string('status_url')->nullable()->comment('URL para verificar estado del servidor');
$table->unsignedInteger('max_accounts')->nullable();
$table->unsignedInteger('current_accounts_count')->default(0);
$table->boolean('is_active')->default(true)->index();
$table->text('notes')->nullable();
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `servers` existe.

### 8.7. Modelo `Server`

Crear el modelo `Server`: `php artisan make:model Server`
Configurar `$fillable`, `$casts` (para `api_password_or_key_encrypted`).
Definir relaciones: `reseller()`, `group()` (belongsTo ServerGroup), `clientServices()` (hasMany ClientService).
**Verificación:** Se pueden crear servidores.

### 8.8. CRUD Básico para `ServerGroup` y `Server` (Admin)

Crear controladores `Admin\ServerGroupController` y `Admin\ServerController` (resources).
Definir rutas resource para `server-groups` y `servers` (admin).
Implementar CRUDs completos para ambos, con sus vistas y FormRequests.
Para `Server`, el `server_group_id` será un select.
(Opcional) Crear Policies y aplicarlas.
Añadir enlaces en `AdminLayout.vue`.
**Verificación:** CRUDs para grupos de servidores y servidores funcionan.

---

¡Gestión Base de Servidores y Grupos Implementada!\*\*
La integración con módulos de aprovisionamiento (cPanel, Plesk) será una fase avanzada.

````



Migración, modelo y CRUD básico (Admin) para `Promotions`.



Este documento se enfoca en la gestión de Promociones y Cupones.



### 8.9. Migración de la Tabla `promotions`
Contexto:** Gestiona descuentos, cupones y ofertas especiales.
Crear la migración para la tabla `promotions`:
```bash
php artisan make:migration create_promotions_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('promotions', function (Blueprint $table) {
$table->id();
$table->string('name')->comment('Nombre interno descriptivo');
$table->string('code')->unique()->nullable()->comment('Código para el cliente, NULL si es automática');
$table->text('description')->nullable()->comment('Visible para el cliente');
$table->enum('type', ['percentage', 'fixed_amount']);
$table->decimal('value', 10, 2)->comment('Valor del descuento (ej. 20.00 para 20% o $20)');
$table->enum('applies_to', ['order', 'product', 'category', 'client_group'])->default('order'); // client_group es futuro
$table->json('product_ids')->nullable()->comment('Si applies_to es product');
// $table->json('category_ids')->nullable(); // Si aplica a categorías de productos (necesitaríamos tabla product_categories)
$table->decimal('min_order_amount', 10, 2)->nullable();
$table->unsignedInteger('max_uses')->nullable()->comment('Usos totales máximos');
$table->unsignedInteger('max_uses_per_client')->nullable();
$table->unsignedInteger('current_uses')->default(0);
$table->dateTime('start_date')->nullable();
$table->dateTime('end_date')->nullable();
$table->boolean('requires_code')->default(true);
$table->boolean('is_active')->default(true)->index();
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL si es de plataforma');
$table->timestamps();
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `promotions` existe.

### 8.10. Modelo `Promotion`

Crear el modelo `Promotion`: `php artisan make:model Promotion`
Configurar `$fillable`, `$casts` (fechas, `product_ids` a `array` o `json`).
Definir relación `reseller()` (belongsTo User, opcional).
**Verificación:** Se pueden crear promociones.

### 8.11. CRUD Básico para `Promotion` (Admin)

Contexto:\*\* Los administradores (y revendedores en su panel) deben poder crear y gestionar promociones.
Crear `Admin\PromotionController.php`:

```bash
php artisan make:controller Admin/PromotionController --resource --model=Promotion
```

Definir rutas resource para `promotions` en `routes/web.php` (admin).
Implementar CRUD completo:
`index()`: Listar promociones. Vista `Admin/Promotions/Index.vue`.
`create()`/`store()`: Formulario para crear promociones. Vista `Admin/Promotions/Create.vue`.
Campos: nombre, código (generar si está vacío), descripción, tipo, valor, a qué aplica (productos específicos con un multi-select de productos), fechas, usos, etc.
`edit()`/`update()`: Formulario para editar. Vista `Admin/Promotions/Edit.vue`.
`destroy()`.
Crear FormRequests (`StorePromotionRequest`, `UpdatePromotionRequest`).
(Opcional) Crear `PromotionPolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** CRUD básico de promociones funciona.

---

¡Gestión Base de Promociones (Admin) Implementada!\*\*
La lógica de aplicación de promociones a órdenes y facturas se implementará más adelante.

````



Migración, modelo y CRUD básico (Admin) para `EmailTemplates`.


Este documento se enfoca en la gestión de Plantillas de Correo Electrónico.



### 8.12. Migración de la Tabla `email_templates`
Contexto:** Almacena las plantillas de correo para diversas notificaciones del sistema.
Crear la migración para la tabla `email_templates`:
```bash
php artisan make:migration create_email_templates_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('email_templates', function (Blueprint $table) {
$table->id();
$table->string('name')->unique()->comment('Nombre descriptivo, ej: "Bienvenida Hosting Compartido"');
$table->string('slug')->unique()->index()->comment('Identificador interno, ej: welcome.shared_hosting');
$table->enum('type', ['general', 'product', 'support', 'invoice', 'domain', 'auth'])->index();
$table->string('subject');
$table->text('body_html');
$table->text('body_text')->nullable();
$table->string('language_code', 10)->default('es')->index();
$table->boolean('is_customizable_by_reseller')->default(false);
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para plantillas globales/base');
$table->timestamps();
// Unique constraint for reseller-specific overrides of a global template
$table->unique(['slug', 'language_code', 'reseller_id'], 'template_slug_lang_reseller_unique');
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `email_templates` existe.

### 8.13. Modelo `EmailTemplate`

Crear el modelo `EmailTemplate`: `php artisan make:model EmailTemplate`
Configurar `$fillable`.
Definir relación `reseller()` (belongsTo User, opcional).
**Verificación:** Se pueden crear plantillas de correo.

### 8.14. CRUD Básico para `EmailTemplate` (Admin)

Contexto:\*\* Los administradores deben poder gestionar las plantillas de correo globales y las personalizaciones de revendedores.
Crear `Admin\EmailTemplateController.php`:

```bash
php artisan make:controller Admin/EmailTemplateController --resource --model=EmailTemplate
```

Definir rutas resource para `email-templates` en `routes/web.php` (admin).
Implementar CRUD completo:
`index()`: Listar plantillas. Vista `Admin/EmailTemplates/Index.vue`.
`create()`/`store()`: Formulario para crear plantillas. Vista `Admin/EmailTemplates/Create.vue`.
Campos: nombre, slug (sugerir basado en nombre), tipo, asunto, cuerpo HTML (usar un editor WYSIWYG simple o textarea), cuerpo texto, idioma, personalizable por revendedor.
`edit()`/`update()`: Formulario para editar. Vista `Admin/EmailTemplates/Edit.vue`.
`destroy()`.
Crear FormRequests (`StoreEmailTemplateRequest`, `UpdateEmailTemplateRequest`).
(Opcional) Crear `EmailTemplatePolicy` y aplicarla.
Añadir enlace en `AdminLayout.vue`.
**Verificación:** CRUD básico de plantillas de correo funciona.

---

¡Gestión Base de Plantillas de Correo (Admin) Implementada!\*\*
La lógica para enviar correos usando estas plantillas se integrará en los módulos correspondientes (órdenes, servicios, soporte, etc.).

````



Migraciones y modelos para `ActivityLogs` y `Settings`. Implementación básica.

```diff
# Geminis - Plan de Tareas Detallado - Parte 25

Este documento se enfoca en la creación de las tablas para Logs de Actividad y Configuraciones del Sistema.



### 8.15. Migración de la Tabla `activity_logs`
Contexto:** Registra acciones importantes realizadas en el sistema para auditoría.
Crear la migración para la tabla `activity_logs`:
```bash
php artisan make:migration create_activity_logs_table
````

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('activity_logs', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que realizó la acción, NULL si es del sistema');
$table->foreignId('reseller_context_id')->nullable()->constrained('users')->onDelete('set null')->comment('Contexto del revendedor, si aplica');
$table->string('loggable_type')->nullable()->index()->comment('Modelo relacionado (polimórfico)');
$table->unsignedBigInteger('loggable_id')->nullable()->index()->comment('ID del modelo relacionado (polimórfico)');
$table->string('action')->index()->comment('Ej: created_client, updated_service_status');
$table->text('description');
$table->json('details')->nullable()->comment('Datos adicionales en formato JSON');
$table->ipAddress('ip_address')->nullable();
$table->text('user_agent')->nullable();
$table->timestamp('created_at')->useCurrent(); // Solo created_at, no updated_at
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `activity_logs` existe.

### 8.16. Modelo `ActivityLog`

Crear el modelo `ActivityLog`: `php artisan make:model ActivityLog`
Configurar `$fillable`. Definir que no use `updated_at` (`const UPDATED_AT = null;`).
Definir relaciones polimórficas `loggable()` y relaciones `user()`, `resellerContext()`.
**Verificación:** Se pueden crear logs.

### 8.17. Migración de la Tabla `settings`

Contexto:\*\* Almacén flexible clave-valor para configuraciones de la plataforma y revendedores.
Crear la migración para la tabla `settings`:

```bash
php artisan make:migration create_settings_table
```

Modificar el método `up()` de la migración según `Geminis_Estructura.md`.

```php
Schema::create('settings', function (Blueprint $table) {
$table->id();
$table->string('group_slug')->default('general')->index()->comment('Ej: general, billing, mail');
$table->string('key')->index();
$table->text('value')->nullable();
$table->boolean('is_encrypted')->default(false);
$table->foreignId('reseller_id')->nullable()->constrained('users')->onDelete('cascade')->comment('NULL para config global');
$table->timestamps();
$table->unique(['group_slug', 'key', 'reseller_id']);
});
```

Ejecutar la migración: `php artisan migrate`.
**Verificación:** La tabla `settings` existe.

### 8.18. Modelo `Setting`

Crear el modelo `Setting`: `php artisan make:model Setting`
Configurar `$fillable`, `$casts` (para `value` si se almacena JSON, o `is_encrypted`).
Definir relación `reseller()` (belongsTo User, opcional).
**Verificación:** Se pueden crear configuraciones.

### 8.19. Implementación Básica de Logs y Settings (Admin)

(ActivityLog) Crear un Trait o Service para registrar logs fácilmente desde otros controladores/servicios.
(ActivityLog) Implementar el registro de logs para acciones clave (ej. creación/actualización de usuarios, productos).
(ActivityLog) Crear `Admin\ActivityLogController` con método `index()` para listar logs. Vista `Admin/ActivityLogs/Index.vue`.
(Settings) Crear `Admin\SettingController` con métodos `index()` (listar settings agrupadas) y `update()` (para actualizar valores). Vista `Admin/Settings/Index.vue`.
Añadir enlaces en `AdminLayout.vue`.
**Verificación:** Se registran logs y se pueden ver. Se pueden ver y modificar configuraciones básicas.

---

¡Logs de Actividad y Configuraciones Base Implementados!\*\*
Con esto se cubren varios módulos de soporte administrativo. Las siguientes tareas se enfocarán en los paneles de Revendedor y Cliente.

````

Estos archivos cubren hasta la Tarea 25. Cuando lleguemos a ese punto, podemos definir las siguientes.

¿Qué te parece esta tanda de archivos de tareas?** Si estás de acuerdo, puedes crear estos archivos en tu directorio `e:\herd\geminis\` (o `e:\herd\hosting\`) y luego, cuando quieras que trabajemos en una tarea específica, simplemente me muestras el contenido de ese archivo.


b/E:\herd\geminis\Tareas\Geminis_Tareas_26.md
# Geminis - Plan de Tareas Detallado - Parte 26

Este documento inicia la Fase 9, enfocada en el desarrollo del Panel de Revendedor.

## Fase 9: Panel de Revendedor

### 9.1. Middleware para Revendedores (`EnsureUserIsReseller`)
Contexto:** Necesitamos proteger las rutas del panel de revendedor para que solo usuarios con rol 'reseller' puedan acceder.
Crear el middleware:
```bash
php artisan make:middleware EnsureUserIsReseller
````

En `app/Http/Middleware/EnsureUserIsReseller.php`, implementar la lógica:

```php
public function handle(Request $request, Closure $next): Response
{
if (!Auth::check() || Auth::user()->role !== 'reseller') {
// O redirigir a login, o a una página de "acceso denegado" si está logueado pero no es reseller
return redirect('/');
}
return $next($request);
}
```

Registrar el alias del middleware en `app/Http/Kernel.php` (en `$middlewareAliases` o `$routeMiddleware`):

```php
// En protected $middlewareAliases o $routeMiddleware
'reseller' => \App\Http\Middleware\EnsureUserIsReseller::class,
```

**Verificación:** El middleware funciona y protege rutas.

### 9.2. Layout para el Panel de Revendedor (`ResellerLayout.vue`)

Contexto:** Similar al `AdminLayout`, pero específico para revendedores.
Crear `resources/js/Layouts/ResellerLayout.vue`.
Puede ser una copia modificada de `AdminLayout.vue` o `AuthenticatedLayout.vue` (el que Breeze instala).
Debe tener una barra lateral/navegación con enlaces a las futuras secciones del panel de revendedor (Dashboard, Mis Clientes, Mis Servicios, Mis Facturas, Soporte, Configuración).
Mostrar el nombre del revendedor/marca (del `reseller_profile` o `user`).
**Verificación:\*\* El layout se renderiza correctamente.

### 9.3. Dashboard Básico del Revendedor

Crear `Reseller/ResellerDashboardController.php`:

```bash
php artisan make:controller Reseller/ResellerDashboardController
```

En el controlador, método `index()`:
Cargar datos relevantes para el revendedor (ej. conteo de sus clientes, servicios activos de sus clientes, tickets abiertos de sus clientes).
Usar `Auth::id()` para filtrar los datos pertenecientes al revendedor logueado.
Pasar datos a la vista `Reseller/Dashboard.vue`.
Crear vista `resources/js/Pages/Reseller/Dashboard.vue`:
Usar `ResellerLayout.vue`.
Mostrar las estadísticas/información cargada.
Definir rutas en `routes/web.php` para el panel de revendedor:

```php
use App\Http\Controllers\Reseller\ResellerDashboardController;

Route::prefix('reseller')->name('reseller.')->middleware(['auth', 'verified', 'reseller'])->group(function () {
Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');
// Aquí irán más rutas del panel de revendedor
});
```

Añadir enlace "Panel de Revendedor" en `AuthenticatedLayout.vue` o `AppLayout.vue` (visible solo si `auth()->user()->role === 'reseller'`).
**Verificación:** Un revendedor logueado puede acceder a su dashboard y ver información básica.

---

¡Panel de Revendedor (Base) Implementado!\*\*
Las siguientes tareas se enfocarán en las funcionalidades específicas del panel de revendedor, como la gestión de sus clientes.

````



CRUD para que los revendedores gestionen sus propios clientes.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_27.md
# Geminis - Plan de Tareas Detallado - Parte 27

Este documento se enfoca en permitir a los revendedores gestionar sus propios clientes.



### 9.4. Gestión de Clientes por Revendedor (CRUD)
Contexto:** Los revendedores necesitan crear, ver, editar y (posiblemente) suspender/eliminar a sus propios clientes.
Crear `Reseller/ClientController.php`:
```bash
php artisan make:controller Reseller/ClientController --resource --model=User
// El modelo es User, pero la lógica filtrará por reseller_id
````

Definir rutas resource para `clients` dentro del grupo `reseller` en `routes/web.php`:

```php
// Dentro del grupo Route::prefix('reseller')...
Route::resource('clients', App\Http\Controllers\Reseller\ClientController::class);
```

Implementar método `index()` en `Reseller\ClientController`:
Listar usuarios (`User`) donde `role = 'client'` Y `reseller_id = Auth::id()`.
Paginado y con filtros (nombre, email, estado del cliente).
Pasar datos a la vista `Reseller/Clients/Index.vue`.
Crear vista `resources/js/Pages/Reseller/Clients/Index.vue`:
Usar `ResellerLayout.vue`.
Tabla para mostrar clientes del revendedor (Nombre, Email, Empresa, Estado, Fecha de Registro).
Enlaces para Crear, Editar, Ver (opcional), Eliminar/Suspender.
Implementar métodos `create()` y `store()`:
Vista `Reseller/Clients/Create.vue`. Formulario similar al de Admin para crear usuarios, pero `role` será 'client' por defecto y `reseller_id` se asignará automáticamente al ID del revendedor logueado.
Validación (usar un FormRequest `StoreResellerClientRequest` o adaptar `StoreUserRequest`).
En `store()`, asegurar que `reseller_id` se establezca a `Auth::id()`.
Implementar métodos `edit(User $client)` y `update(Request $request, User $client)`:
Importante:** En `edit` y `update`, verificar que `$client->reseller_id === Auth::id()`. Si no, abortar con 403/404. Esto se puede hacer con una Policy o directamente en el controlador.
Vista `Reseller/Clients/Edit.vue`. Formulario similar al de Admin para editar usuarios.
Validación (usar `UpdateResellerClientRequest` o adaptar `UpdateUserRequest`).
Implementar método `destroy(User $client)`:
Verificar que `$client->reseller_id === Auth::id()`.
Decidir si es borrado lógico (soft delete) o cambio de estado a 'inactive'/'suspended'.
Crear FormRequests: `StoreResellerClientRequest` y `UpdateResellerClientRequest`.
En `authorize()`, verificar que `Auth::user()->role === 'reseller'`.
En `UpdateResellerClientRequest@authorize()`, también verificar que el cliente que se intenta editar pertenezca al revendedor autenticado.
(Opcional pero recomendado) Crear `ResellerClientPolicy` o usar `UserPolicy` con lógica adaptada:
`viewAny()`: `Auth::user()->role === 'reseller'`.
`view()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
`create()`: `Auth::user()->role === 'reseller'`.
`update()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
`delete()`: `Auth::user()->role === 'reseller' && $targetUser->reseller_id === Auth::id()`.
Aplicar esta policy en `Reseller\ClientController`.
Añadir enlace "Mis Clientes" en `ResellerLayout.vue`.
**Verificación:\*\* Un revendedor puede gestionar sus clientes. No puede ver ni gestionar clientes de otros revendedores o clientes directos de la plataforma.

---

¡Gestión de Clientes por Revendedor Implementada!\*\*
El siguiente paso es permitir a los revendedores ver y gestionar los servicios de sus clientes.

````



Permitir a los revendedores ver y gestionar los servicios de sus propios clientes.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_28.md
# Geminis - Plan de Tareas Detallado - Parte 28

Este documento se enfoca en permitir a los revendedores gestionar los servicios de sus clientes.



### 9.5. Gestión de Servicios de Clientes por Revendedor
Contexto:** Los revendedores deben poder ver los servicios de sus clientes, su estado, y posiblemente realizar acciones básicas (suspender, terminar, etc., si se les da ese permiso).
Crear `Reseller/ClientServiceController.php`:
```bash
php artisan make:controller Reseller/ClientServiceController --resource --model=ClientService
````

Definir rutas resource para `client-services` dentro del grupo `reseller` en `routes/web.php`.
Podrían ser rutas anidadas bajo clientes: `reseller/clients/{client}/services` o rutas de nivel superior filtradas. Por simplicidad, usar de nivel superior y filtrar.

```php
// Dentro del grupo Route::prefix('reseller')...
Route::resource('client-services', App\Http\Controllers\Reseller\ClientServiceController::class)->names('clientServices');
// Se usa names() para evitar colisión con admin.client-services si existe.
```

Implementar método `index()` en `Reseller\ClientServiceController`:
Listar `ClientService` donde `reseller_id = Auth::id()`.
Cargar relaciones (`client`, `product`).
Paginado y con filtros (por cliente del revendedor, producto, estado del servicio).
Pasar datos a la vista `Reseller/ClientServices/Index.vue`.
Crear vista `resources/js/Pages/Reseller/ClientServices/Index.vue`:
Usar `ResellerLayout.vue`.
Tabla para mostrar servicios (Cliente, Producto, Dominio, Próxima Vencimiento, Estado, Precio).
Enlaces para Ver detalles del servicio.
Implementar método `show(ClientService $clientService)`:
Verificar que `$clientService->reseller_id === Auth::id()`.
Mostrar detalles del servicio, incluyendo opciones configurables seleccionadas.
Vista `Reseller/ClientServices/Show.vue`.
(Opcional Avanzado) Implementar `edit()` y `update()` si los revendedores pueden modificar servicios (ej. cambiar estado, notas).
Verificar pertenencia del servicio al revendedor.
Validación adecuada.
(Opcional pero recomendado) Crear `ResellerClientServicePolicy` o adaptar `ClientServicePolicy`:
`viewAny()`: `Auth::user()->role === 'reseller'`.
`view()`: `Auth::user()->role === 'reseller' && $clientService->reseller_id === Auth::id()`.
`update()`: `Auth::user()->role === 'reseller' && $clientService->reseller_id === Auth::id()`.
Aplicar esta policy en `Reseller\ClientServiceController`.
Añadir enlace "Servicios de Clientes" en `ResellerLayout.vue`.
**Verificación:**
Un revendedor puede ver una lista de todos los servicios pertenecientes a sus clientes.
Un revendedor puede ver los detalles de un servicio específico de su cliente.
Un revendedor NO puede ver servicios de clientes de otros revendedores ni servicios directos de la plataforma.

---

¡Gestión de Servicios de Clientes por Revendedor Implementada!\*\*
El panel de revendedor está tomando forma. Las siguientes tareas se enfocarán en el panel del cliente final.

````



Inicio del panel de cliente, con su layout, dashboard básico y el middleware para proteger sus rutas.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_29.md
# Geminis - Plan de Tareas Detallado - Parte 29

Este documento inicia la Fase 10, enfocada en el desarrollo del Panel de Cliente.

## Fase 10: Panel de Cliente

### 10.1. Middleware para Clientes (`EnsureUserIsClient`)
Contexto:** Proteger las rutas del portal del cliente para que solo usuarios con rol 'client' (o también 'reseller' si un revendedor puede actuar como cliente de la plataforma) puedan acceder.
Crear el middleware:
```bash
php artisan make:middleware EnsureUserIsClient
````

En `app/Http/Middleware/EnsureUserIsClient.php`, implementar la lógica:

```php
public function handle(Request $request, Closure $next): Response
{
// Permite acceso a 'client' y 'reseller' (un reseller puede ser cliente de la plataforma)
// Si solo quieres 'client', ajusta la condición.
if (!Auth::check() || !in_array(Auth::user()->role, ['client', 'reseller'])) {
return redirect('/');
}
return $next($request);
}
```

Registrar el alias del middleware en `app/Http/Kernel.php`:

```php
// En protected $middlewareAliases o $routeMiddleware
'client' => \App\Http\Middleware\EnsureUserIsClient::class,
```

**Verificación:** El middleware funciona.

### 10.2. Layout para el Panel de Cliente (`ClientLayout.vue`)

Contexto:** Layout específico para el portal del cliente.
Crear `resources/js/Layouts/ClientLayout.vue`.
Puede basarse en `AuthenticatedLayout.vue` de Breeze.
Navegación: Dashboard, Mis Servicios, Mis Dominios (futuro), Mis Facturas, Soporte, Mi Perfil.
**Verificación:\*\* El layout se renderiza.

### 10.3. Dashboard Básico del Cliente

Crear `Client/ClientDashboardController.php`:

```bash
php artisan make:controller Client/ClientDashboardController
```

En el controlador, método `index()`:
Cargar datos para el cliente: conteo de servicios activos, facturas pendientes, tickets abiertos.
Filtrar por `Auth::id()`.
Pasar datos a la vista `Client/Dashboard.vue`.
Crear vista `resources/js/Pages/Client/Dashboard.vue`:
Usar `ClientLayout.vue`.
Mostrar la información cargada.
Definir rutas en `routes/web.php` para el panel de cliente:

```php
use App\Http\Controllers\Client\ClientDashboardController;

Route::prefix('client')->name('client.')->middleware(['auth', 'verified', 'client'])->group(function () {
Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
// Aquí irán más rutas del panel de cliente
});
```

El enlace "Dashboard" que Breeze crea (si se usa `AuthenticatedLayout`) ya debería funcionar o se ajusta para apuntar a `client.dashboard`.
**Verificación:** Un cliente logueado puede acceder a su dashboard.

---

¡Panel de Cliente (Base) Implementado!\*\*
Las siguientes tareas se enfocarán en las funcionalidades específicas del panel de cliente.

````



Permitir a los clientes ver sus servicios contratados y su historial de facturas.



Este documento se enfoca en permitir a los clientes visualizar sus servicios y facturas.



### 10.4. Visualización de Servicios del Cliente
Contexto:** Los clientes deben poder ver una lista de sus servicios contratados y los detalles de cada uno.
Crear `Client/ServiceController.php` (o añadir a `ClientDashboardController`):
```bash
php artisan make:controller Client/ServiceController --resource --model=ClientService
````

Definir rutas resource para `services` dentro del grupo `client` en `routes/web.php`.

```php
// Dentro del grupo Route::prefix('client')...
Route::resource('services', App\Http\Controllers\Client\ServiceController::class)->only(['index', 'show']);
// Solo index y show por ahora para el cliente
```

Implementar método `index()` en `Client\ServiceController`:
Listar `ClientService` donde `client_id = Auth::id()`.
Cargar relaciones (`product`, `productPricing`).
Paginado.
Pasar datos a la vista `Client/Services/Index.vue`.
Crear vista `resources/js/Pages/Client/Services/Index.vue`:
Usar `ClientLayout.vue`.
Tabla/lista de servicios (Producto, Dominio, Próxima Vencimiento, Estado, Precio).
Enlace para ver detalles.
Implementar método `show(ClientService $service)`:
Verificar que `$service->client_id === Auth::id()`.
Mostrar detalles del servicio, opciones configurables, etc.
Vista `Client/Services/Show.vue`.
Añadir enlace "Mis Servicios" en `ClientLayout.vue`.
**Verificación:** El cliente puede ver sus servicios y detalles.

### 10.5. Visualización de Facturas del Cliente

Contexto:\*\* Los clientes deben poder ver su historial de facturas y los detalles de cada una.
Crear `Client/InvoiceController.php`:

```bash
php artisan make:controller Client/InvoiceController --resource --model=Invoice
```

Definir rutas resource para `invoices` dentro del grupo `client` en `routes/web.php`.

```php
// Dentro del grupo Route::prefix('client')...
Route::resource('invoices', App\Http\Controllers\Client\InvoiceController::class)->only(['index', 'show']);
```

Implementar método `index()` en `Client\InvoiceController`:
Listar `Invoice` donde `client_id = Auth::id()`. Paginado.
Pasar datos a la vista `Client/Invoices/Index.vue`.
Crear vista `resources/js/Pages/Client/Invoices/Index.vue`:
Usar `ClientLayout.vue`. Tabla/lista de facturas (Número, Fecha Emisión, Fecha Vencimiento, Total, Estado). Enlace para ver/pagar.
Implementar método `show(Invoice $invoice)`:
Verificar que `$invoice->client_id === Auth::id()`.
Mostrar detalles de la factura, ítems, opción de descarga PDF (implementada en Tarea 16).
Vista `Client/Invoices/Show.vue`.
Añadir enlace "Mis Facturas" en `ClientLayout.vue`.
**Verificación:** El cliente puede ver sus facturas y detalles.

---

¡Visualización de Servicios y Facturas por Cliente Implementada!\*\*
El panel de cliente ahora ofrece información útil. Las siguientes tareas pueden incluir la gestión de dominios por el cliente y la interacción con el sistema de soporte.

````



Definir la estructura base (interfaces, servicios) para la futura integración de módulos de aprovisionamiento.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_31.md
# Geminis - Plan de Tareas Detallado - Parte 31

Este documento se enfoca en sentar las bases para la integración de módulos de aprovisionamiento de servicios (ej. cPanel, Plesk).

## Fase 11: Automatización y Módulos Externos

### 11.1. Definición de Interfaces para Módulos de Aprovisionamiento
Contexto:** Para permitir la integración de diferentes sistemas de aprovisionamiento (cPanel, Plesk, etc.), necesitamos una interfaz común.
Crear directorio `app/Modules/Provisioning/Contracts/`.
Crear interfaz `ProvisioningModuleInterface.php` en ese directorio:
```php
// app/Modules/Provisioning/Contracts/ProvisioningModuleInterface.php
namespace App\Modules\Provisioning\Contracts;

use App\Models\ClientService;
use App\Models\User; // Para datos del cliente

interface ProvisioningModuleInterface
{
public function createAccount(ClientService $service, User $client, array $options = []): array; // Retorna array con datos del resultado o error
public function suspendAccount(ClientService $service): bool;
public function unsuspendAccount(ClientService $service): bool;
public function terminateAccount(ClientService $service): bool;
public function changePassword(ClientService $service, string $newPassword): bool;
public function getUsage(ClientService $service): array; // Ej: uso de disco, Bw
// Otros métodos relevantes: changePackage, loginToPanel, etc.
}
````

**Verificación:** La interfaz está definida.

### 11.2. Creación de un Servicio de Aprovisionamiento (Manager)

Contexto:\*\* Un servicio que actúe como "manager" o "factory" para obtener la instancia correcta del módulo de aprovisionamiento basado en `products.module_name`.
Crear `app/Services/ProvisioningService.php`.
En `ProvisioningService.php`, método `getModule(string $moduleName): ?ProvisioningModuleInterface`:
Este método usará un `match` o `if/else` para instanciar y retornar el módulo correcto (ej. `new CpanelModule()`).
Por ahora, puede retornar `null` o lanzar una excepción si el módulo no existe.

```php
// Ejemplo en ProvisioningService.php
// public function getModule(string $moduleName): ?ProvisioningModuleInterface
// {
//     return match (strtolower($moduleName)) {
//         'cpanel' => new \App\Modules\Provisioning\CpanelModule(), // Crear esta clase después
//         'plesk' => new \App\Modules\Provisioning\PleskModule(),   // Crear esta clase después
//         default => null,
//     };
// }
```

(Opcional) Registrar `ProvisioningService` en el contenedor de servicios de Laravel si se va a inyectar.
**Verificación:** El servicio base está creado.

### 11.3. Clases Platzhalter para Módulos Específicos (Ej. CpanelModule)

Contexto:** Crear las clases vacías que implementarán la interfaz, para tener la estructura.
Crear directorio `app/Modules/Provisioning/`.
Crear `app/Modules/Provisioning/CpanelModule.php` que implemente `ProvisioningModuleInterface`.
Dejar los métodos de la interfaz vacíos o retornando valores por defecto (ej. `true` o `[]`).
(Opcional) Crear `PleskModule.php` de forma similar.
**Verificación:\*\* Las clases existen y el `ProvisioningService` podría (conceptualmente) instanciarlas.

---

¡Estructura Base para Módulos de Aprovisionamiento Definida!\*\*
La implementación real de la lógica de cada módulo (conexión a APIs, etc.) será una tarea mucho más grande y específica para cada módulo. Esta tarea solo sienta las bases.

````



Lógica para que los clientes puedan aplicar códigos de promoción durante el proceso de orden.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_32.md
# Geminis - Plan de Tareas Detallado - Parte 32

Este documento se enfoca en implementar la lógica para aplicar promociones a las órdenes.



### 6.21. Aplicación de Códigos de Promoción en el Proceso de Orden
Contexto:** Los clientes deben poder ingresar un código de promoción en el formulario de orden para obtener descuentos.
Modificar `Client/Orders/Create.vue` (o donde se configure la orden):
Añadir un campo de texto para "Código de Promoción".
Añadir un botón "Aplicar Código".
Crear un método en `Client\OrderController` (o donde se maneje la lógica de la orden) `applyPromotionCode(Request $request)`:
Recibe el `product_id`, `product_pricing_id` y el `promotion_code`.
Validar el código:
Buscar la promoción (`Promotion::where('code', $code)->where('is_active', true)->first()`).
Verificar fechas de validez (`start_date`, `end_date`).
Verificar usos máximos (`max_uses`, `current_uses`).
Verificar usos por cliente (necesitaría una tabla `promotion_usages` para rastrear esto).
Verificar si aplica al producto/orden (`applies_to`, `product_ids`, `min_order_amount`).
Si es válida, calcular el descuento (porcentaje o fijo).
Retornar el monto del descuento y los detalles de la promoción (o un error si no es válida). Esta respuesta será usada por el frontend para actualizar el total.
En el frontend (`Client/Orders/Create.vue`):
Al hacer clic en "Aplicar Código", enviar una petición AJAX al método `applyPromotionCode`.
Actualizar el total de la orden mostrado al cliente con el descuento aplicado.
Almacenar el `promotion_id` o `promotion_code` aplicado para enviarlo al crear la orden.
**Verificación:** Un cliente puede ingresar un código, se valida, y el total se actualiza.

### 6.22. Guardar Promoción Aplicada en la Orden
Contexto:** Al crear la orden, se debe registrar qué promoción se aplicó y el monto del descuento.
Añadir campos a la tabla `orders` (si no existen y son necesarios):
`promotion_id` (FK a `promotions`, nullable).
`discount_amount` (DECIMAL, nullable).
Modificar migración de `orders` y ejecutar `php artisan migrate` (o `migrate:fresh --seed`).
Actualizar `$fillable` en el modelo `Order.php`.
Modificar `Client\OrderController@placeOrder`:
Si se aplicó una promoción (se tiene `promotion_id` del paso anterior):
Volver a validar la promoción (para evitar manipulaciones).
Guardar `promotion_id` y `discount_amount` en la nueva orden.
Ajustar `orders.total_amount` restando el `discount_amount`.
Incrementar `promotions.current_uses` (si `max_uses` está definido).
(Opcional) Registrar el uso en `promotion_usages`.
**Verificación:** La promoción aplicada y el descuento se guardan correctamente en la orden.

### 6.23. Mostrar Descuento en Detalles de Orden y Factura
Contexto:** El descuento aplicado debe ser visible en los detalles de la orden y en la factura.
Modificar vistas `Admin/Orders/Show.vue` y `Client/Orders/Show.vue`:
Mostrar el nombre de la promoción aplicada (si existe) y el `discount_amount`.
Modificar lógica de generación de facturas (`generateInvoiceFromOrder`):
Si la orden tiene `promotion_id` y `discount_amount`:
Añadir un `invoice_item` de tipo 'discount' con el monto negativo del descuento.
O ajustar el subtotal/total de la factura directamente.
Modificar vistas `Admin/Invoices/Show.vue` y `Client/Invoices/Show.vue`:
Mostrar el descuento aplicado.
**Verificación:** El descuento es visible en órdenes y facturas.

---
¡Aplicación de Promociones Implementada!**
Los clientes ahora pueden beneficiarse de descuentos.
````

Integrar el envío de emails usando las plantillas creadas para eventos clave del sistema.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_33.md
# Geminis - Plan de Tareas Detallado - Parte 33

Este documento se enfoca en integrar el sistema de plantillas de correo para enviar notificaciones en eventos clave.

## Fase 12: Notificaciones y Comunicaciones

### 12.1. Servicio de Envío de Correos
Contexto:** Centralizar la lógica de envío de correos usando las plantillas.
Crear `app/Services/EmailService.php`.
En `EmailService.php`, método `sendEmail(User $recipient, string $templateSlug, array $data = [], ?User $resellerContext = null)`:
Buscar la plantilla de correo:
Primero, buscar una plantilla específica del revendedor (si `$resellerContext` y `is_customizable_by_reseller` lo permiten).
Si no se encuentra, buscar la plantilla global con ese `slug`.
Si no se encuentra la plantilla, loguear un error y no enviar.
Parsear el `subject` y `body_html` de la plantilla, reemplazando placeholders (ej. `{{ client_name }}`, `{{ service_domain }}`) con los valores de `$data`.
Usar `Mail::send()` o crear un Mailable dinámico para enviar el correo.
Considerar el idioma del destinatario (`$recipient->language_code`) al buscar la plantilla.
**Verificación:** El servicio puede (conceptualmente) encontrar una plantilla y prepararla.

### 12.2. Configuración de Correo en `.env`
Asegurar que las variables de entorno para el correo estén configuradas en `.env` (ej. `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`).
Usar un servicio como Mailtrap.io para pruebas de desarrollo.
**Verificación:** Laravel puede enviar un correo de prueba simple (ej. desde Tinker).

### 12.3. Integración de Notificaciones por Email
Contexto:** Enviar correos en momentos clave.
Ejemplos de Eventos y Plantillas (crear plantillas si no existen):**
Registro de Nuevo Cliente:**
Evento: Después de que un cliente se registra (Breeze ya envía uno, se puede personalizar o reemplazar).
Plantilla: `auth.welcome` o `client.registration`.
Datos: Nombre del cliente, enlace al login.
Integrar en `RegisteredUserController@store` o donde se creen usuarios.
Creación de Nueva Orden:**
Evento: Después de `Client\OrderController@placeOrder`.
Plantilla: `order.confirmation`.
Datos: Número de orden, detalles de la orden, total.
Integrar.
Generación de Nueva Factura:**
Evento: Después de `generateInvoiceFromOrder` o `GenerateRenewalInvoicesJob`.
Plantilla: `invoice.new`.
Datos: Número de factura, monto, fecha de vencimiento, enlace para ver/pagar.
Integrar.
Servicio Activado:**
Evento: Cuando un `ClientService` cambia a estado 'active'.
Plantilla: `service.activated` (puede ser específica por `product.type`).
Datos: Nombre del producto, dominio, detalles de acceso (si aplica y es seguro enviarlos).
Integrar donde se activen servicios (manual admin, o después de pago de orden).
Nuevo Ticket de Soporte Creado (para cliente y admin/staff):**
Evento: Después de `Client\SupportTicketController@store` o `Admin\SupportTicketController@store`.
Plantilla Cliente: `support.ticket.opened.client`.
Plantilla Staff: `support.ticket.opened.staff`.
Datos: Número de ticket, asunto, enlace al ticket.
Integrar.
Nueva Respuesta en Ticket de Soporte (para cliente o staff):**
Evento: Después de `addReply` en controladores de tickets.
Plantilla Cliente: `support.ticket.reply.client` (si respondió staff).
Plantilla Staff: `support.ticket.reply.staff` (si respondió cliente).
Datos: Número de ticket, enlace al ticket, contenido de la respuesta (o un extracto).
Integrar.
Para cada integración:
Identificar el lugar en el código donde ocurre el evento.
Llamar a `EmailService->sendEmail()` con los datos correctos.
Considerar el uso de Jobs para enviar correos y no bloquear la respuesta HTTP (`SendEmailJob` que internamente use `EmailService`).
**Verificación:** Los correos se envían correctamente para cada evento y llegan a Mailtrap (o al destinatario real). Las variables en las plantillas se reemplazan correctamente.

---
¡Notificaciones por Email Integradas!**
El sistema ahora comunica eventos importantes a los usuarios.
```

Funcionalidades adicionales para la gestión de tickets por administradores y revendedores.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_34.md
# Geminis - Plan de Tareas Detallado - Parte 34

Este documento se enfoca en añadir funcionalidades avanzadas al sistema de soporte para administradores y revendedores.



### 7.14. Asignación de Tickets a Agentes (Admin/Reseller)
Contexto:** Los tickets deben poder asignarse a un agente específico (usuario admin o staff del revendedor).
En `Admin/SupportTickets/Show.vue` y `Reseller/SupportTickets/Show.vue` (si los revendedores gestionan sus propios tickets/departamentos):
Añadir un select para "Asignar a" que liste los usuarios administradores (y/o staff del revendedor).
Al cambiar, actualizar `support_tickets.assigned_to_user_id` mediante una petición AJAX.
Modificar `AdminSupportTicketController` (y `ResellerSupportTicketController` si aplica) para tener un método `assignTicket(Request $request, SupportTicket $ticket)`.
Validar `assigned_to_user_id`.
Actualizar el ticket.
(Opcional) Registrar en `activity_logs`.
(Opcional) Enviar email de notificación al agente asignado.
**Verificación:** Se puede asignar un ticket a un agente.

### 7.15. Notas Internas en Tickets
Contexto:** Permitir a los agentes añadir notas privadas a un ticket, no visibles para el cliente.
Añadir campo `is_internal_note` (BOOLEAN, default FALSE) a la tabla `support_ticket_replies`.
Modificar migración y ejecutar. Actualizar `$fillable` en `SupportTicketReply.php`.
En `Admin/SupportTickets/Show.vue` (y `Reseller`):
Al añadir una respuesta, ofrecer un checkbox "Nota interna".
Las respuestas marcadas como internas deben tener un estilo visual diferente en el listado de respuestas (ej. fondo amarillo claro).
Estas notas NO deben ser visibles en `Client/SupportTickets/Show.vue`.
Modificar `SupportTicketReplyController` (o donde se guarden las respuestas) para guardar el valor de `is_internal_note`.
Modificar la carga de respuestas en `Client\SupportTicketController@show` para filtrar `where('is_internal_note', false)`.
**Verificación:** Los agentes pueden añadir notas internas, y estas no son visibles para los clientes.

### 7.16. (Opcional) Respuestas Predefinidas
Contexto:** Para agilizar respuestas comunes.
Crear tabla `predefined_replies` (`id`, `department_id` (nullable), `title`, `content_text`, `created_at`, `updated_at`).
Crear modelo `PredefinedReply` y CRUD básico en Admin para gestionarlas.
En `Admin/SupportTickets/Show.vue` (y `Reseller`):
Añadir un select "Insertar Respuesta Predefinida".
Al seleccionar, el contenido de la respuesta predefinida se inserta en el textarea de respuesta del ticket.
**Verificación:** Se pueden crear y usar respuestas predefinidas.

### 7.17. Filtros Avanzados en Listado de Tickets (Admin)
En `Admin/SupportTickets/Index.vue` y `AdminSupportTicketController@index`:
Añadir más filtros: por agente asignado, por cliente, por prioridad.
**Verificación:** Los filtros funcionan correctamente.

---
¡Mejoras en el Sistema de Soporte Implementadas!**
La gestión de tickets es ahora más robusta para el personal.
```

Implementación del Job para generar facturas de renovación automáticamente.

````diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_35.md
# Geminis - Plan de Tareas Detallado - Parte 35

Este documento se enfoca en implementar la generación automática de facturas de renovación.



### 6.24. Job para Generar Facturas de Renovación (`GenerateRenewalInvoicesJob`)
Contexto:** Automatizar la creación de facturas para servicios que están por vencer.
Crear el Job:
```bash
php artisan make:job GenerateRenewalInvoicesJob
````

En `app/Jobs/GenerateRenewalInvoicesJob.php`:
En el método `handle()`:
Obtener la fecha actual y la fecha X días en el futuro (ej. 15 días, configurable en `settings`).
Buscar `ClientService` que cumplan:
`status` = 'active'.
`next_due_date` <= fecha X días en el futuro.
Que no tengan ya una factura de renovación 'unpaid' para ese `next_due_date`. (Esto requiere una forma de marcar las facturas como "de renovación" o vincularlas al `client_service_id` y `next_due_date`).
Para cada servicio encontrado:
Llamar a un servicio `InvoiceService->generateRenewalInvoice(ClientService $service)`.
**Verificación:** El Job se puede ejecutar manualmente y la lógica de búsqueda de servicios es correcta.

### 6.25. Servicio de Facturación (`InvoiceService`) - Método de Renovación

Contexto:** Lógica para crear una factura específica para la renovación de un servicio.
Crear (o añadir a) `app/Services/InvoiceService.php`.
Método `generateRenewalInvoice(ClientService $service): ?Invoice`:
Determinar el `issue_date` (hoy) y `due_date` (el `service->next_due_date`).
Crear un registro en `invoices`:
`client_id`, `reseller_id` del servicio.
`invoice_number` único.
`status` = 'unpaid'.
`currency_code` del servicio/cliente.
Crear `invoice_items`:
Un ítem principal para el producto base del servicio (`service->product_id`, `service->product_pricing_id`). Descripción: "Renovación de [Nombre Producto] ([Fecha Inicio Ciclo] - [Fecha Fin Ciclo])".
Ítems adicionales para las `client_service_configurable_options` activas del servicio, con sus precios correspondientes.
Calcular subtotal, impuestos (si aplica), y total.
Guardar la factura y sus ítems.
Actualizar `client_services.next_due_date` al siguiente ciclo de facturación (ej. si era mensual, sumar 1 mes).
(Opcional) Enviar email de "Nueva Factura de Renovación" al cliente usando `EmailService`.
Retornar la factura creada.
**Verificación:\*\* El método genera correctamente una factura de renovación con sus ítems y actualiza la fecha del servicio.

### 6.26. Programación del Job (`Kernel.php`)

### 6.26. Programación del Job (`bootstrap/app.php`)

Contexto:\*\* Ejecutar el `GenerateRenewalInvoicesJob` automáticamente.
En `app/Console/Kernel.php`, dentro del método `schedule()`:
En `bootstrap/app.php`, dentro del closure de `withSchedule()` o `->withSchedule(function (Schedule $schedule) { ... })`:

```php
// $schedule->job(new GenerateRenewalInvoicesJob)->daily(); // O dailyAt('01:00');
$schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping(); // Si los jobs se despachan a la cola
$schedule->job(GenerateRenewalInvoicesJob::class)->dailyAt('03:00'); // Despacha el job a la cola diariamente
// Ejemplo en bootstrap/app.php
// return Application::configure(...)
//     ->withRouting(...)
//     ->withMiddleware(...)
//     ->withExceptions(...)
//     ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
//         $schedule->job(new App\Jobs\GenerateRenewalInvoicesJob)->dailyAt('03:00'); // Despacha el job a la cola diariamente
//         // Si los jobs se despachan a la cola, asegúrate que el worker de la cola esté corriendo.
//         // Opcionalmente, para desarrollo o si no usas colas para este job:
//         // $schedule->call(function () {
//         //     (new App\Jobs\GenerateRenewalInvoicesJob)->handle();
//         // })->dailyAt('03:00');
//     })->create();
```

Asegurar que el programador de Laravel (scheduler) esté configurado en el servidor (cron job que ejecute `php artisan schedule:run`).
**Verificación:** El job se ejecuta según lo programado y procesa las renovaciones. (Se puede probar localmente con `php artisan schedule:run`).

---

¡Generación Automática de Facturas de Renovación Implementada!\*\*
Esto reduce la carga administrativa y asegura la facturación continua de servicios.

````



Permitir a los revendedores (si tienen permiso) crear sus propios productos y/o seleccionar qué productos de plataforma revender.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_36.md
# Geminis - Plan de Tareas Detallado - Parte 36

Este documento se enfoca en expandir las capacidades de los revendedores para gestionar productos.



### 9.6. Listado de Productos para Revendedor
Contexto:** Los revendedores deben ver los productos que pueden ofrecer: los de plataforma (si son `is_resellable_by_default` o si los ha habilitado) y los propios (si `allow_custom_products`).
Crear `Reseller/ProductController.php`:
```bash
php artisan make:controller Reseller/ProductController --resource --model=Product
````

Definir rutas resource para `products` dentro del grupo `reseller` en `routes/web.php`.
Implementar método `index()` en `Reseller\ProductController`:
Obtener productos de plataforma (`owner_id` IS NULL) que sean `is_resellable_by_default = true`.
Obtener productos propios del revendedor (`owner_id = Auth::id()`).
Combinar y paginar.
Pasar datos a la vista `Reseller/Products/Index.vue`.
Crear vista `resources/js/Pages/Reseller/Products/Index.vue`:
Usar `ResellerLayout.vue`.
Tabla de productos (Nombre, Tipo, ¿Propio/Plataforma?, Estado).
Opción para "Crear Producto Propio" (si `Auth::user()->resellerProfile->allow_custom_products`).
Opción para "Gestionar Precios" (lleva a `edit`).
Añadir enlace "Mis Productos" en `ResellerLayout.vue`.
**Verificación:** El revendedor ve la lista correcta de productos.

### 9.7. Creación/Edición de Productos Propios por Revendedor

Contexto:** Si `reseller_profiles.allow_custom_products` es true, el revendedor puede crear sus productos.
Verificar el permiso `allow_custom_products` en el `ResellerProfile` del usuario autenticado.
Se puede acceder a través de `Auth::user()->resellerProfile->allow_custom_products` (asumiendo que la relación `resellerProfile` existe en el modelo `User`).
Implementar `create()` y `store()` en `Reseller\ProductController`:
Solo accesible si tiene permiso.
Vista `Reseller/Products/Create.vue`. Formulario similar al de Admin, pero `owner_id` se asigna automáticamente a `Auth::id()`.
`is_resellable_by_default` no aplica o es `false`.
Validación (usar `StoreResellerProductRequest`).
Implementar `edit(Product $product)` y `update(Request $request, Product $product)`:
Solo accesible si `Auth::user()->resellerProfile->allow_custom_products` Y `$product->owner_id === Auth::id()`.
Vista `Reseller/Products/Edit.vue`.
Permitir editar detalles del producto y gestionar sus `product_pricing` (similar a Admin).
Validación (usar `UpdateResellerProductRequest`).
Implementar `destroy(Product $product)`:
Solo para productos propios (`$product->owner_id === Auth::id()`).
Crear FormRequests: `StoreResellerProductRequest` y `UpdateResellerProductRequest`.
En `authorize()`, verificar `Auth::user()->role === 'reseller'` y `Auth::user()->resellerProfile->allow_custom_products`.
En `UpdateResellerProductRequest@authorize()`, también verificar que el producto pertenezca al revendedor.
(Opcional) Adaptar `ProductPolicy` o crear `ResellerProductPolicy`.
**Verificación:\*\* Un revendedor con permiso puede crear y gestionar sus propios productos. No puede editar productos de plataforma.

### 9.8. (Opcional) Selección de Productos de Plataforma para Revender

Contexto:** Si un producto de plataforma no es `is_resellable_by_default`, el admin podría habilitarlo para revendedores específicos, o el revendedor podría "activarlo" para su catálogo.
Decisión MVP:** Mantenerlo simple. Los revendedores solo ven/usan los de plataforma que son `is_resellable_by_default = true` y los suyos propios. Esta funcionalidad puede ser una mejora futura.

### 9.9. Gestión de Precios para Productos de Revendedor

Contexto:** Los revendedores deben poder definir los precios para sus propios productos. Para productos de plataforma, podrían usar los precios base o tener un margen (esto es más complejo).
Decisión MVP:**
Para productos propios: El revendedor define sus `product_pricing` como lo hace el admin.
Para productos de plataforma: El revendedor usa los `product_pricing` definidos por el admin. No puede modificarlos.
La lógica de gestión de precios en `Reseller/Products/Edit.vue` (para productos propios) será similar a la de `Admin/Products/Edit.vue`.
Rutas y métodos en `ResellerProductController` para `storePricing`, `updatePricing`, `destroyPricing`, asegurando que solo operen sobre productos del revendedor.
**Verificación:** El revendedor puede gestionar precios de sus productos.

---

¡Gestión de Productos por Revendedor Implementada!\*\*
Los revendedores ahora tienen más control sobre su catálogo.

````



Permitir a los revendedores personalizar su perfil (`ResellerProfile`) y algunas configuraciones (`Settings`) específicas.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_37.md
# Geminis - Plan de Tareas Detallado - Parte 37

Este documento se enfoca en permitir a los revendedores configurar aspectos de su cuenta y marca.



### 9.10. Gestión del Perfil de Revendedor (`ResellerProfile`)
Contexto:** Los revendedores deben poder actualizar la información de su marca y configuraciones de perfil.
Crear `Reseller/ProfileController.php`.
Método `edit()` en `Reseller\ProfileController`:
Obtener el `ResellerProfile` del revendedor autenticado (`Auth::user()->resellerProfile()->firstOrCreate(['user_id' => Auth::id()])`).
Pasar el perfil a la vista `Reseller/Profile/Edit.vue`.
Crear vista `resources/js/Pages/Reseller/Profile/Edit.vue`:
Usar `ResellerLayout.vue`.
Formulario para editar campos de `ResellerProfile` (brand_name, custom_domain (informativo, admin lo configura), logo_url, support_email, terms_url).
`allow_custom_products` sería solo visible, no editable por el revendedor (lo gestiona el admin).
Método `update(Request $request)` en `Reseller\ProfileController`:
Validar los datos del formulario.
Actualizar el `ResellerProfile` del revendedor.
Manejar subida de logo si se incluye.
Definir rutas en `routes/web.php` para `reseller.profile.edit` y `reseller.profile.update`.
Añadir enlace "Mi Perfil" o "Configuración de Marca" en `ResellerLayout.vue`.
**Verificación:** El revendedor puede actualizar los detalles de su perfil/marca.

### 9.11. Gestión de Configuraciones Específicas del Revendedor (`Settings`)
Contexto:** Permitir a los revendedores sobrescribir ciertas configuraciones globales o definir las suyas (ej. pasarelas de pago, textos de emails si `is_customizable_by_reseller` es true).
Crear `Reseller/SettingController.php`.
Método `index()` en `Reseller\SettingController`:
Obtener las configuraciones globales que el revendedor puede sobrescribir.
Obtener las configuraciones específicas del revendedor (`Setting::where('reseller_id', Auth::id())->get()`).
Pasar datos a la vista `Reseller/Settings/Index.vue`.
Crear vista `resources/js/Pages/Reseller/Settings/Index.vue`:
Usar `ResellerLayout.vue`.
Formulario para editar las configuraciones permitidas (ej. claves API de su propia pasarela de pago, si se implementa).
Método `update(Request $request)` en `Reseller\SettingController`:
Validar los datos.
Actualizar o crear registros en la tabla `settings` con `reseller_id = Auth::id()`.
Definir rutas en `routes/web.php` para `reseller.settings.index` y `reseller.settings.update`.
Añadir enlace "Configuraciones" en `ResellerLayout.vue`.
**Verificación:** El revendedor puede ver y modificar las configuraciones que le son permitidas.

### 9.12. (Opcional) Personalización de Plantillas de Correo por Revendedor
Contexto:** Si una `EmailTemplate` es `is_customizable_by_reseller`, el revendedor podría editar su propia versión.
En `Reseller/EmailTemplateController.php` (crearlo):
Listar plantillas personalizables y las ya personalizadas por el revendedor.
Formulario para editar el `subject` y `body_html` de una plantilla (crea un nuevo registro en `email_templates` con `reseller_id` y el mismo `slug`).
`EmailService` debe priorizar la plantilla del revendedor si existe.
**Verificación:** El revendedor puede personalizar plantillas de correo permitidas.

---
¡Configuraciones del Panel de Revendedor Implementadas!**
Los revendedores tienen ahora más control sobre su entorno.
````

Definir y desarrollar algunos endpoints API básicos si se prevé la necesidad de una app móvil o integraciones externas.

````diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_38.md
# Geminis - Plan de Tareas Detallado - Parte 38

Este documento se enfoca en la creación de una API básica para clientes y revendedores, si fuera necesaria para futuras aplicaciones móviles o integraciones.

## Fase 13: API y Servicios Externos

### 13.1. Autenticación API (Sanctum)
Contexto:** Si se va a exponer una API, se necesita un método de autenticación seguro. Laravel Sanctum es ideal para tokens API.
Instalar Sanctum (si no se hizo antes para la API de Vue en el libro de ejemplo):
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
````

Añadir `Laravel\Sanctum\HasApiTokens` trait al modelo `User.php`.
Configurar `auth.guards.api.driver` a `sanctum` en `config/auth.php` si se usará autenticación por token para la API.
Crear rutas para generar tokens API (ej. en `Client/ProfileController` o `Reseller/ProfileController`):
`POST /api/tokens/create` - Genera un nuevo token para el usuario autenticado.
`GET /api/tokens` - Lista los tokens del usuario.
`DELETE /api/tokens/{tokenId}` - Revoca un token.
**Verificación:** Los usuarios pueden generar y gestionar tokens API.

### 13.2. Endpoints API para Clientes

Contexto:** Exponer funcionalidades del panel de cliente a través de una API.
Crear controladores en `app/Http/Controllers/Api/Client/`.
Ejemplos de Endpoints (protegidos con `auth:sanctum` y middleware `client`):
`GET /api/client/services` - Listar servicios del cliente.
`GET /api/client/services/{service}` - Ver detalle de un servicio.
`GET /api/client/invoices` - Listar facturas del cliente.
`GET /api/client/invoices/{invoice}` - Ver detalle de una factura.
`GET /api/client/tickets` - Listar tickets de soporte del cliente.
`POST /api/client/tickets` - Crear nuevo ticket.
`POST /api/client/tickets/{ticket}/reply` - Añadir respuesta a ticket.
Usar Resources API de Laravel para formatear las respuestas JSON.
**Verificación:\*\* Los endpoints funcionan y devuelven los datos correctos para el cliente autenticado.

### 13.3. Endpoints API para Revendedores

Contexto:** Exponer funcionalidades del panel de revendedor.
Crear controladores en `app/Http/Controllers/Api/Reseller/`.
Ejemplos de Endpoints (protegidos con `auth:sanctum` y middleware `reseller`):
`GET /api/reseller/clients` - Listar clientes del revendedor.
`POST /api/reseller/clients` - Crear un nuevo cliente para el revendedor.
`GET /api/reseller/client-services` - Listar servicios de los clientes del revendedor.
Usar Resources API.
**Verificación:\*\* Los endpoints funcionan y devuelven los datos correctos para el revendedor autenticado.

---

¡API Básica para Cliente/Revendedor Implementada!\*\*
Esto sienta las bases para futuras integraciones o aplicaciones móviles. La complejidad de la API puede crecer significativamente según los requisitos.

````


Geminis_Tareas_33.md

Escribir pruebas automatizadas para los módulos y funcionalidades clave del sistema.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_39.md
# Geminis - Plan de Tareas Detallado - Parte 39

Este documento se enfoca en la creación de pruebas automatizadas para asegurar la calidad y estabilidad del sistema.

## Fase 14: Pruebas y Refinamiento

### 14.1. Configuración del Entorno de Pruebas
Contexto:** Asegurar que las pruebas se ejecuten en un entorno aislado.
Verificar que `phpunit.xml` esté configurado para usar una base de datos en memoria (SQLite) o una base de datos de prueba separada.
```xml
// phpunit.xml (ejemplo para SQLite en memoria)
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
````

Usar el trait `RefreshDatabase` en las clases de prueba para migrar la BD antes de cada prueba y limpiarla después.
**Verificación:** Las pruebas pueden ejecutarse sin afectar la base de datos de desarrollo.

### 14.2. Pruebas Unitarias para Modelos y Servicios

Contexto:** Probar la lógica interna de modelos (relaciones, scopes, accessors/mutators) y servicios.
Crear pruebas unitarias (en `tests/Unit`) para:
Modelo `User`: Relaciones `clients()`, `reseller()`, `resellerProfile()`.
Modelo `Product`: Relaciones `owner()`, `pricings()`, `configurableOptionGroups()`.
Modelo `ClientService`: Lógica de cambio de estado, cálculo de `next_due_date`.
`InvoiceService`: Generación de facturas de orden y renovación.
`EmailService`: Lógica de selección de plantillas y parseo (puede requerir mocks).
**Verificación:\*\* `php artisan test --filter=Unit` pasa.

### 14.3. Pruebas de Integración (Feature Tests) para Funcionalidades Clave

Contexto:** Probar flujos completos de la aplicación, incluyendo interacciones HTTP, autorización y respuestas.
Crear pruebas de feature (en `tests/Feature`) para:
Autenticación:** Login, registro, protección de rutas.
CRUD Admin Usuarios:** Crear, listar, editar, eliminar usuarios (probando policies).
CRUD Admin Productos:** Crear, listar, editar, eliminar productos y sus precios.
Proceso de Orden Cliente:** Seleccionar producto, aplicar promoción, generar orden y factura.
Sistema de Soporte:** Cliente crea ticket, admin/reseller responde, cliente responde.
Panel Revendedor:** Revendedor crea cliente, revendedor ve servicios de su cliente.
Panel Cliente:** Cliente ve sus servicios y facturas.
Usar factories para crear datos de prueba.
Simular usuarios con diferentes roles (`actingAs`).
Verificar respuestas HTTP (status codes, contenido JSON, vistas Inertia).
**Verificación:** `php artisan test --filter=Feature` pasa.

---

¡Pruebas Automatizadas Implementadas!\*\*
Esto aumenta la confianza en la estabilidad del código y facilita la detección de regresiones. Es un proceso continuo a medida que se añaden nuevas funcionalidades.

````



Revisión general de la experiencia de usuario, interfaz, y chequeos básicos de seguridad.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_40.md
# Geminis - Plan de Tareas Detallado - Parte 40

Este documento se enfoca en el refinamiento general de la aplicación, incluyendo la experiencia de usuario, la interfaz y aspectos básicos de seguridad.



### 14.4. Revisión y Mejoras de UX/UI
Contexto:** Asegurar que la aplicación sea intuitiva y agradable de usar para todos los roles.
**Navegación:**
Revisar la consistencia de la navegación en los paneles de Admin, Reseller y Cliente.
Asegurar que todos los enlaces importantes sean accesibles.
Mejorar la indicación de la sección activa en los menús.
**Formularios:**
Mejorar mensajes de validación (más claros, mejor posicionados).
Asegurar que los campos obligatorios estén claramente indicados.
Considerar feedback visual durante el envío de formularios (ej. deshabilitar botón, mostrar spinner).
**Tablas y Listados:**
Mejorar la legibilidad.
Asegurar que la paginación sea clara y funcional.
Añadir indicadores de "cargando" si los datos tardan en aparecer.
**Mensajes al Usuario:**
Utilizar notificaciones (toasts/flash messages) de forma consistente para feedback de acciones (éxito, error, advertencia).
**Responsividad:**
Probar la aplicación en diferentes tamaños de pantalla (móvil, tablet, escritorio) y ajustar estilos donde sea necesario.
**Consistencia Visual:**
Asegurar que los estilos (colores, fuentes, espaciado) sean consistentes a través de toda la aplicación.
**Verificación:** Realizar pruebas de usabilidad con cada rol, identificando puntos de fricción y mejorándolos.

### 14.5. Chequeos Básicos de Seguridad
Contexto:** Realizar una revisión de las vulnerabilidades más comunes.
**Protección XSS:**
Asegurar que toda la salida de datos del usuario en las vistas Vue/Blade esté debidamente escapada (Vue lo hace por defecto con `{{ }}`, Blade también). Evitar `v-html` o `{!! !!}` sin sanitización.
**Protección CSRF:**
Verificar que todos los formularios que modifican estado (POST, PUT, DELETE) usen el token CSRF (Laravel lo maneja automáticamente con Blade y Axios si está configurado).
**Inyección SQL:**
Asegurar que se esté usando Eloquent ORM o el Query Builder con bindings parametrizados para todas las consultas a la base de datos. Evitar concatenación directa de input del usuario en queries SQL.
**Autorización (Policies):**
Revisar que todas las rutas y acciones de controlador estén protegidas por Policies o checks de autorización adecuados para prevenir acceso no autorizado.
**Validación de Entradas:**
Asegurar que todas las entradas del usuario (formularios, parámetros de ruta, query strings) sean validadas rigurosamente en el backend.
**Manejo de Contraseñas:**
Verificar que las contraseñas se hasheen correctamente al almacenarlas y se comparen de forma segura.
**Dependencias:**
Ejecutar `composer outdated` y `npm outdated` para identificar dependencias desactualizadas que puedan tener vulnerabilidades conocidas. Actualizar con precaución.
**Verificación:** Realizar una revisión manual de estos puntos. Considerar herramientas de análisis estático si el proyecto crece mucho.

---
¡Refinamientos y Chequeos de Seguridad Básicos Completados!**
La aplicación es ahora más pulida y se han mitigado riesgos de seguridad comunes. La seguridad es un proceso continuo.
````

¡Listo! Con esto ya tienes los archivos desde `Geminis_Tareas_07.md` hasta `Geminis_Tareas_40.md` definidos y guardados en `E:\herd\geminis\Tareas\`.

Esto nos da una hoja de ruta muy extensa y detallada. Recuerda que es una guía, y podemos ajustar, añadir o quitar tareas según cómo evolucione el proyecto y tus prioridades.

Cuando quieras continuar, ya sabes, me muestras el contenido de la tarea en la que estemos y seguimos adelante. ¡Buen trabajo con la planificación!

b/E:\herd\geminis\Tareas\Geminis_Tareas_41.md

# Geminis - Plan de Tareas Detallado - Parte 41

Este documento se enfoca en la integración básica de pasarelas de pago.



### 13.4. Configuración de Pasarelas de Pago (Stripe/PayPal - Lado Servidor)

Contexto:** Preparar el backend para procesar pagos a través de pasarelas externas.
**Stripe:\*\*
Instalar el SDK de Stripe para PHP: `composer require stripe/stripe-php`.
Añadir claves API de Stripe (secreta y publicable) a `config/services.php` y `.env`.

```php
// config/services.php
'stripe' => [
'key' => env('STRIPE_KEY'),
'secret' => env('STRIPE_SECRET'),
'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

Crear un `StripeService.php` en `app/Services/` para encapsular la lógica de Stripe (ej. crear PaymentIntents, manejar webhooks).
**PayPal:**
Instalar el SDK de PayPal para PHP: `composer require paypal/paypal-checkout-sdk`.
Añadir credenciales API de PayPal (Client ID, Secret) a `config/services.php` y `.env`.

```php
// config/services.php
'paypal' => [
'client_id' => env('PAYPAL_CLIENT_ID'),
'secret' => env('PAYPAL_SECRET'),
'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' o 'live'
'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
],
```

Crear un `PayPalService.php` en `app/Services/` (ej. crear órdenes, capturar pagos, manejar webhooks).
**Verificación:** Las configuraciones y SDKs están instalados. Los servicios pueden ser instanciados.

### 13.5. Controlador de Webhooks Genérico

Contexto:\*\* Un punto de entrada para manejar notificaciones asíncronas de las pasarelas de pago.
Crear `WebhookController.php` en `app/Http/Controllers/Webhook/`.

```bash
php artisan make:controller Webhook/WebhookController
```

Método `handleStripeWebhook(Request $request)`:
Verificar la firma del webhook de Stripe.
Procesar el evento (ej. `payment_intent.succeeded`, `invoice.payment_failed`).
Actualizar estado de factura/orden, registrar transacción.
Método `handlePaypalWebhook(Request $request)`:
Verificar la firma del webhook de PayPal.
Procesar el evento (ej. `CHECKOUT.ORDER.APPROVED`, `PAYMENT.SALE.COMPLETED`).
Definir rutas para los webhooks en `routes/web.php` (o `routes/api.php` si es más apropiado). Estas rutas deben estar excluidas de la protección CSRF.
**Verificación:** Las rutas de webhook existen y la lógica base de verificación de firma está planteada.

---

¡Integración Base de Pasarelas de Pago (Servidor) Iniciada!\*\*
Las siguientes tareas se enfocarán en la interacción del cliente con estas pasarelas.

````



Permitir a los clientes añadir, ver y eliminar sus métodos de pago (ej. tarjetas con Stripe).


Este documento se enfoca en permitir a los clientes gestionar sus métodos de pago.



### 10.6. Gestión de Métodos de Pago del Cliente (Stripe)
Contexto:** Los clientes deben poder guardar y gestionar sus tarjetas de crédito para pagos futuros (usando Stripe SetupIntents y Customers).
**Backend (StripeService):**
Método `createSetupIntent(User $client)`: Crea un SetupIntent para que el cliente añada una nueva tarjeta.
Método `listPaymentMethods(User $client)`: Lista los métodos de pago guardados del cliente en Stripe.
Método `detachPaymentMethod(User $client, string $paymentMethodId)`: Elimina un método de pago de Stripe.
Método `setDefaultPaymentMethod(User $client, string $paymentMethodId)`: Establece un método de pago como predeterminado.
**Controlador Cliente (`Client/PaymentMethodController.php`):**
```bash
php artisan make:controller Client/PaymentMethodController
````

`index()`: Llama a `StripeService->listPaymentMethods()` y pasa los datos a la vista.
`create()`: Llama a `StripeService->createSetupIntent()` y pasa el `client_secret` del SetupIntent a la vista.
`store(Request $request)`: (Este se maneja principalmente en el frontend con Stripe.js, pero el backend podría confirmar el SetupIntent si es necesario o manejar el webhook `setup_intent.succeeded`).
`destroy(string $paymentMethodId)`: Llama a `StripeService->detachPaymentMethod()`.
`setDefault(Request $request, string $paymentMethodId)`: Llama a `StripeService->setDefaultPaymentMethod()`.
**Vistas Cliente:**
`Client/PaymentMethods/Index.vue`:
Lista los métodos de pago guardados (últimos 4 dígitos, fecha de expiración, tipo).
Botón para "Añadir Nuevo Método de Pago" (lleva a `create`).
Botones para "Eliminar" y "Marcar como Predeterminado".
`Client/PaymentMethods/Create.vue`:
Integra Stripe Elements (Card Element) para la entrada segura de los datos de la tarjeta.
Usa el `client_secret` del SetupIntent para confirmar la configuración de la tarjeta con Stripe.js.
Al éxito, redirigir a `index` o mostrar mensaje.
Definir rutas en `routes/web.php` para `client.payment-methods.*`.
Añadir enlace "Métodos de Pago" en `ClientLayout.vue`.
**Verificación:**
Un cliente puede añadir una nueva tarjeta de forma segura.
Un cliente puede ver sus tarjetas guardadas.
Un cliente puede eliminar una tarjeta.
Un cliente puede establecer una tarjeta como predeterminada.

### 10.7. (Opcional) Gestión de Métodos de Pago (PayPal)

Contexto:\*\* Si se permite guardar acuerdos de facturación de PayPal.
Investigar y planificar la lógica para acuerdos de facturación de PayPal (Billing Agreements / Subscriptions API de PayPal).
Implementar lógica similar a la de Stripe si se decide soportar.

---

¡Gestión de Métodos de Pago por Cliente Implementada (Stripe)!\*\*
Los clientes ahora pueden gestionar sus instrumentos de pago para facilitar futuras transacciones.

````


Geminis_Tareas_40.md

Permitir a los clientes seleccionar una factura pendiente y pagarla.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_43.md
# Geminis - Plan de Tareas Detallado - Parte 43

Este documento se enfoca en permitir a los clientes pagar sus facturas pendientes.



### 10.8. Proceso de Pago de Facturas por el Cliente
Contexto:** Los clientes deben poder seleccionar una factura 'unpaid' y realizar el pago.
**Vista `Client/Invoices/Show.vue` (Modificación):**
Si la factura está 'unpaid', mostrar un botón "Pagar Factura".
Al hacer clic, podría llevar a una nueva página `Client/Invoices/Pay.vue` o mostrar un modal.
**Vista `Client/Invoices/Pay.vue` (o Modal):**
Mostrar resumen de la factura (total a pagar).
Permitir al cliente seleccionar un método de pago guardado (de Tarea 42).
Opción para añadir un nuevo método de pago (si no tiene o quiere usar otro).
Botón "Confirmar Pago".
**Backend (StripeService o PaymentService):**
Método `processInvoicePayment(Invoice $invoice, User $client, string $paymentMethodId)`:
Crear un PaymentIntent en Stripe por el monto de la factura, usando el `customer_id` del cliente y el `paymentMethodId`.
Confirmar el PaymentIntent.
Si el pago es exitoso:
Registrar la transacción en la tabla `transactions`.
Actualizar `invoices.status` a 'paid' y `invoices.paid_date`.
Si la factura está asociada a una orden que activa servicios, disparar lógica de activación de servicios.
Enviar email de confirmación de pago.
Si falla, retornar error.
**Controlador Cliente (`Client/InvoiceController.php` - Modificación o Nuevo):**
Método `showPaymentForm(Invoice $invoice)`:
Verificar que la factura pertenezca al cliente y esté 'unpaid'.
Cargar métodos de pago guardados del cliente.
Pasar datos a `Client/Invoices/Pay.vue`.
Método `processPayment(Request $request, Invoice $invoice)`:
Validar `payment_method_id`.
Llamar a `StripeService->processInvoicePayment()`.
Redirigir con mensaje de éxito/error.
**Verificación:**
Un cliente puede seleccionar una factura no pagada.
Puede elegir un método de pago existente o añadir uno nuevo para pagar.
Tras un pago exitoso, la factura se marca como pagada, se registra la transacción y se notifica al cliente.
Los servicios asociados se activan (si aplica).

### 10.9. (Opcional) Pago con PayPal
Contexto:** Permitir pagar facturas usando PayPal.
En `Client/Invoices/Pay.vue`, añadir opción "Pagar con PayPal".
Al seleccionar, redirigir a PayPal para aprobar el pago (usando `PayPalService` para crear la orden de PayPal).
Manejar el retorno de PayPal (éxito/cancelación) y llamar a `PayPalService` para capturar el pago.
Actualizar factura y transacción de forma similar a Stripe.
**Verificación:** El cliente puede pagar una factura usando PayPal.

---
¡Proceso de Pago de Facturas por Cliente Implementado!**
Los clientes ahora pueden liquidar sus facturas pendientes de forma autónoma.
````

Comenzar la implementación real de un módulo de aprovisionamiento, como cPanel, para la creación de cuentas.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_44.md
# Geminis - Plan de Tareas Detallado - Parte 44

Este documento se enfoca en la implementación del método `createAccount` para un módulo de aprovisionamiento específico, como cPanel.



### 11.4. Implementación de `CpanelModule->createAccount()`
Contexto:** Conectar con la API de cPanel/WHM para crear una nueva cuenta de hosting cuando un servicio se activa.
**Configuración del Servidor en Geminis:**
Asegurar que la tabla `servers` tenga los campos necesarios para cPanel: `hostname_or_ip`, `api_username`, `api_password_or_key_encrypted` (para token API de WHM), `api_port` (2087 por defecto), `api_use_ssl`.
**Librería Cliente cPanel API:**
Investigar e instalar una librería PHP para interactuar con la API de WHM (ej. `CpanelWhm/cpanel-php` o una más moderna si existe, o usar Guzzle HTTP directamente).
`composer require GuzzleHttp/Guzzle` (si se usa directamente).
**En `app/Modules/Provisioning/CpanelModule.php`:**
Añadir constructor que reciba los datos del servidor (`Server $serverModel`).
Implementar el método `createAccount(ClientService $service, User $client, array $options = []): array`:
Obtener los datos del servidor desde `$this->serverModel`.
Obtener datos del producto (`$service->product`) para determinar el plan de cPanel (podría estar en `products.module_specific_config` como un JSON).
Datos necesarios para `createacct` API de WHM: `username`, `domain`, `password`, `email`, `plan`.
Generar un nombre de usuario y contraseña seguros si no se proporcionan en `$options`.
Construir la URL de la API de WHM (ej. `https://{hostname}:2087/json-api/createacct?...`).
Realizar la llamada a la API usando Guzzle o la librería cliente:
Autenticación: `Authorization: WHM username:apitoken` o `Authorization: Basic base64(user:pass)`.
Manejar la respuesta de la API:
Si éxito:
Actualizar `client_services` con `username`, `password_encrypted` (encriptar la contraseña generada), `server_id`, `domain_name`.
Retornar `['success' => true, 'message' => 'Cuenta creada', 'data' => ['username' => ..., 'ip' => ...]]`.
Si error:
Loguear el error.
Retornar `['success' => false, 'message' => 'Error de cPanel: ' . $errorMessage]`.
**Verificación (Manual/Tinker):**
Configurar un servidor cPanel de prueba en la tabla `servers`.
Crear un `ClientService` de prueba.
Desde Tinker, instanciar `CpanelModule` con el servidor y llamar a `createAccount()`.
Verificar que la cuenta se cree en cPanel/WHM y que `client_services` se actualice.

### 11.5. Integración de `createAccount` en el Flujo de Activación de Servicio
Contexto:** Llamar al aprovisionamiento cuando un servicio se activa (ej. después de pagar una orden).
Modificar el lugar donde los servicios se activan (ej. `InvoiceService` después de un pago exitoso, o en `AdminClientServiceController` si es activación manual):
Obtener el `ClientService` y el `Product` asociado.
Si `product.module_name` es 'cpanel':
Obtener el `Server` asignado al producto/servicio (esto necesita lógica de asignación de servidor, ej. de un `server_groups`).
Si no hay servidor asignado, loguear error y marcar el servicio para aprovisionamiento manual.
Instanciar `ProvisioningService` y obtener el módulo: `$module = $provisioningService->getModule('cpanel', $server);`.
Llamar a `$module->createAccount($clientService, $clientService->client)`.
Manejar el resultado (actualizar estado del servicio, notificar al cliente/admin).
**Verificación:** Al activar un servicio de tipo cPanel, se intenta crear la cuenta automáticamente.

---
¡Creación de Cuentas cPanel (Base) Implementada!**
Este es un paso importante hacia la automatización. Las siguientes tareas pueden incluir suspender/terminar cuentas.
```

Implementar las acciones de suspender, reactivar y terminar cuentas en el módulo cPanel.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_45.md
# Geminis - Plan de Tareas Detallado - Parte 45

Este documento se enfoca en implementar las funciones de suspensión, reactivación y terminación para el módulo cPanel.



### 11.6. Implementación de `CpanelModule->suspendAccount()`
Contexto:** Suspender una cuenta de hosting en cPanel.
En `app/Modules/Provisioning/CpanelModule.php`:
Implementar `suspendAccount(ClientService $service): bool`:
Usar la API de WHM `suspendacct` (parámetros: `user`, `reason` (opcional)).
Obtener `username` de `$service->username`.
Realizar la llamada a la API.
Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
**Verificación (Manual/Tinker):** Probar la suspensión de una cuenta existente.

### 11.7. Implementación de `CpanelModule->unsuspendAccount()`
Contexto:** Reactivar una cuenta de hosting suspendida en cPanel.
En `app/Modules/Provisioning/CpanelModule.php`:
Implementar `unsuspendAccount(ClientService $service): bool`:
Usar la API de WHM `unsuspendacct` (parámetro: `user`).
Realizar la llamada a la API.
Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
**Verificación (Manual/Tinker):** Probar la reactivación de una cuenta suspendida.

### 11.8. Implementación de `CpanelModule->terminateAccount()`
Contexto:** Eliminar permanentemente una cuenta de hosting en cPanel.
En `app/Modules/Provisioning/CpanelModule.php`:
Implementar `terminateAccount(ClientService $service): bool`:
Usar la API de WHM `removeacct` (parámetro: `user`, opcional `keepdns`).
Realizar la llamada a la API.
Retornar `true` en caso de éxito, `false` y loguear error en caso de fallo.
**Verificación (Manual/Tinker):** Probar la terminación de una cuenta (¡con cuidado en entornos de prueba!).

### 11.9. Integración de Acciones de Módulo en Controladores de Servicio (Admin/Reseller)
Contexto:** Permitir a los administradores/revendedores ejecutar estas acciones desde el panel.
En `AdminClientServiceController` (y `ResellerClientServiceController` si aplica):
Añadir métodos para `suspend(ClientService $service)`, `unsuspend(ClientService $service)`, `terminate(ClientService $service)`.
En cada método:
Autorizar la acción (ej. con `ClientServicePolicy`).
Obtener el módulo de aprovisionamiento (`$provisioningService->getModule(...)`).
Llamar al método correspondiente del módulo (`$module->suspendAccount($service)`).
Si la acción del módulo es exitosa, actualizar `client_services.status`.
Redirigir con mensaje.
Añadir botones/acciones en `Admin/ClientServices/Show.vue` (o `Index.vue`) y vistas de revendedor para invocar estos métodos.
**Verificación:** Un admin/revendedor puede suspender, reactivar y terminar servicios de cPanel desde la interfaz. El estado del servicio en Geminis se actualiza.

---
¡Funciones de Suspensión/Reactivación/Terminación cPanel Implementadas!**
La gestión del ciclo de vida de los servicios de cPanel está más completa.
```

Permitir a los clientes ver información básica de sus dominios y, potencialmente, gestionar nameservers.

````diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_46.md
# Geminis - Plan de Tareas Detallado - Parte 46

Este documento se enfoca en permitir a los clientes gestionar aspectos básicos de sus dominios.



### 10.10. Visualización de Dominios del Cliente
Contexto:** Los clientes deben poder ver una lista de sus dominios registrados y sus detalles.
Crear `Client/DomainController.php`:
```bash
php artisan make:controller Client/DomainController --resource --model=Domain
````

Definir rutas resource para `domains` dentro del grupo `client` en `routes/web.php` (solo `index` y `show` por ahora).
Implementar método `index()` en `Client\DomainController`:
Listar `Domain` donde `client_id = Auth::id()`.
Paginado.
Pasar datos a la vista `Client/Domains/Index.vue`.
Crear vista `resources/js/Pages/Client/Domains/Index.vue`:
Usar `ClientLayout.vue`.
Tabla/lista de dominios (Nombre Dominio, Fecha Registro, Fecha Expiración, Estado).
Enlace para ver detalles/gestionar.
Implementar método `show(Domain $domain)`:
Verificar que `$domain->client_id === Auth::id()`.
Mostrar detalles del dominio (nameservers, estado de auto-renovación, EPP code si se permite ver).
Vista `Client/Domains/Show.vue`.
Añadir enlace "Mis Dominios" en `ClientLayout.vue`.
**Verificación:** El cliente puede ver sus dominios y detalles.

### 10.11. (Opcional) Gestión de Nameservers por Cliente

Contexto:** Permitir a los clientes actualizar los nameservers de sus dominios (requiere integración con módulo registrador).
**Interfaz Módulo Registrador:**
Definir una interfaz `DomainRegistrarModuleInterface` con métodos como `getNameservers(Domain $domain)`, `updateNameservers(Domain $domain, array $nameservers)`.
Crear clases Platzhalter para módulos específicos (ej. `EnomModule`).
**Backend:**
En `Client\DomainController@show`, si el dominio tiene un `registrar_module_slug`, intentar obtener los NS actuales.
Método `updateNameservers(Request $request, Domain $domain)`:
Validar los nameservers.
Llamar al método del módulo registrador para actualizar los NS.
Actualizar `domains` tabla si es necesario.
**Frontend (`Client/Domains/Show.vue`):**
Formulario para editar nameservers.
**Verificación:\*\* El cliente puede (conceptualmente) actualizar nameservers. La implementación real depende del módulo.

---

¡Visualización de Dominios por Cliente Implementada!\*\*
La gestión avanzada de dominios (transferencias, EPP, etc.) y la integración real con registradores son tareas más complejas para el futuro.

````



Creación de la estructura y gestión administrativa para artículos de la base de conocimiento.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_47.md
# Geminis - Plan de Tareas Detallado - Parte 47

Este documento inicia la implementación de una Base de Conocimiento (KB).

## Fase 15: Base de Conocimiento y Contenido

### 15.1. Migración de la Tabla `kb_categories`
Contexto:** Las categorías para organizar los artículos de la KB.
Crear la migración:
```bash
php artisan make:migration create_kb_categories_table
````

Modificar el método `up()`:

```php
Schema::create('kb_categories', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->foreignId('parent_id')->nullable()->constrained('kb_categories')->onDelete('set null'); // Para subcategorías
$table->integer('display_order')->default(0);
$table->boolean('is_visible_to_clients')->default(true);
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para categorías globales');
$table->timestamps();
});
```

Ejecutar la migración.
**Verificación:** La tabla `kb_categories` existe.

### 15.2. Modelo `KnowledgeBaseCategory`

Crear el modelo: `php artisan make:model KnowledgeBaseCategory -m` (el `-m` crea la migración si no existe, pero ya la creamos).
Configurar `$fillable`. Definir relaciones `parent()`, `children()`, `articles()`, `reseller()`.

### 15.3. Migración de la Tabla `kb_articles`

Contexto:\*\* Los artículos individuales de la KB.
Crear la migración:

```bash
php artisan make:migration create_kb_articles_table
```

Modificar el método `up()`:

```php
Schema::create('kb_articles', function (Blueprint $table) {
$table->id();
$table->foreignId('category_id')->constrained('kb_categories')->onDelete('cascade');
$table->string('title');
$table->string('slug')->unique();
$table->longText('content_html');
$table->integer('views_count')->default(0);
$table->integer('helpful_yes_count')->default(0);
$table->integer('helpful_no_count')->default(0);
$table->boolean('is_published')->default(true);
$table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario admin/staff que lo creó/editó
$table->foreignId('reseller_id')->nullable()->constrained('users')->comment('NULL para artículos globales');
$table->timestamps();
$table->softDeletes();
});
```

Ejecutar la migración.
**Verificación:** La tabla `kb_articles` existe.

### 15.4. Modelo `KnowledgeBaseArticle`

Crear el modelo: `php artisan make:model KnowledgeBaseArticle`
Configurar `$fillable`. Definir relaciones `category()`, `author()`, `reseller()`.

### 15.5. CRUD Admin para `KnowledgeBaseCategory` y `KnowledgeBaseArticle`

Crear controladores `Admin\KnowledgeBaseCategoryController` y `Admin\KnowledgeBaseArticleController` (resources).
Definir rutas resource para `kb-categories` y `kb-articles` (admin).
Implementar CRUDs completos para ambos, con sus vistas (`Admin/KbCategories/*`, `Admin/KbArticles/*`) y FormRequests.
Para artículos, usar un editor WYSIWYG para el campo `content_html`.
Permitir asignar artículos a categorías.
(Opcional) Crear Policies y aplicarlas.
Añadir enlaces en `AdminLayout.vue` para "Base de Conocimiento".
**Verificación:** CRUDs para categorías y artículos de KB funcionan desde el panel de admin.

---

¡Admin CRUD para Base de Conocimiento Implementado!\*\*
La siguiente tarea se enfocará en la visualización de la KB por parte de los clientes.

````



Permitir a los clientes navegar y buscar en la base de conocimiento.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_48.md
# Geminis - Plan de Tareas Detallado - Parte 48

Este documento se enfoca en la visualización de la Base de Conocimiento por parte de los clientes.



### 15.6. Visualización de la Base de Conocimiento (Cliente)
Contexto:** Los clientes deben poder navegar por las categorías y leer los artículos de la KB.
Crear `Client/KnowledgeBaseController.php`.
Método `index()`:
Listar categorías principales de la KB (las que son `is_visible_to_clients = true` y `reseller_id` es NULL o el del revendedor del cliente si existe un contexto de marca blanca).
Pasar datos a la vista `Client/KnowledgeBase/Index.vue`.
Vista `Client/KnowledgeBase/Index.vue`:
Usar `ClientLayout.vue`.
Mostrar lista de categorías, posiblemente con conteo de artículos.
Campo de búsqueda.
Método `showCategory(KnowledgeBaseCategory $category)`:
Verificar visibilidad de la categoría.
Listar artículos publicados de esa categoría.
Pasar datos a la vista `Client/KnowledgeBase/CategoryShow.vue`.
Vista `Client/KnowledgeBase/CategoryShow.vue`:
Mostrar nombre de la categoría y lista de sus artículos.
Método `showArticle(KnowledgeBaseCategory $category, KnowledgeBaseArticle $article)`:
Verificar publicación y visibilidad del artículo y categoría.
Incrementar `views_count` del artículo.
Pasar datos a la vista `Client/KnowledgeBase/ArticleShow.vue`.
Vista `Client/KnowledgeBase/ArticleShow.vue`:
Mostrar título y `content_html` del artículo.
(Opcional) Botones "¿Fue útil este artículo? Sí/No" que actualicen `helpful_yes_count`/`helpful_no_count`.
Método `search(Request $request)`:
Buscar artículos por título o contenido.
Pasar resultados a una vista `Client/KnowledgeBase/SearchResults.vue`.
Definir rutas en `routes/web.php` para `client.kb.*`.
Añadir enlace "Base de Conocimiento" o "Ayuda" en `ClientLayout.vue`.
**Verificación:**
El cliente puede navegar por las categorías de la KB.
El cliente puede leer artículos.
El cliente puede buscar artículos.

---
¡Visualización de Base de Conocimiento por Cliente Implementada!**
Los clientes ahora tienen un recurso de autoayuda.
````

Estructura inicial para un sistema de afiliados, incluyendo la gestión de afiliados y visualización de estadísticas básicas por el admin.

````diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_49.md
# Geminis - Plan de Tareas Detallado - Parte 49

Este documento inicia la implementación de un Sistema de Afiliados.

## Fase 16: Sistema de Afiliados

### 16.1. Migración de la Tabla `affiliates`
Contexto:** Almacena la información de los afiliados. Un afiliado es un usuario (cliente o revendedor).
Crear la migración:
```bash
php artisan make:migration create_affiliates_table
````

Modificar el método `up()`:

```php
Schema::create('affiliates', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // El usuario que es afiliado
$table->string('referral_code')->unique();
$table->decimal('commission_rate_percentage', 5, 2)->default(10.00); // Porcentaje de comisión
$table->unsignedInteger('min_payout_amount')->default(50); // Monto mínimo para solicitar pago
$table->boolean('is_active')->default(false);
$table->decimal('balance', 10, 2)->default(0.00); // Saldo actual de comisiones
$table->timestamps();
});
```

Ejecutar la migración.
**Verificación:** La tabla `affiliates` existe.

### 16.2. Modelo `Affiliate`

Crear el modelo: `php artisan make:model Affiliate`
Configurar `$fillable`. Definir relación `user()`.

### 16.3. Migraciones para Tablas de Seguimiento de Afiliados

**`affiliate_clicks`**: Registra clics en enlaces de afiliados.
Campos: `id`, `affiliate_id` (FK a `affiliates`), `ip_address`, `user_agent`, `referral_url`, `landing_page_url`, `created_at`.
**`affiliate_signups`**: Registra nuevos clientes referidos.
Campos: `id`, `affiliate_id`, `referred_user_id` (FK a `users`), `order_id` (FK a `orders`, opcional), `commission_earned` (nullable), `commission_paid_date` (nullable), `status` ('pending', 'approved', 'paid', 'rejected'), `created_at`.
**`affiliate_payouts`**: Registra los pagos realizados a los afiliados.
Campos: `id`, `affiliate_id`, `payout_date`, `amount`, `payment_method_details`, `transaction_reference`, `status` ('pending', 'completed', 'failed'), `created_at`.
Crear modelos para estas tablas: `AffiliateClick`, `AffiliateSignup`, `AffiliatePayout`.
Ejecutar migraciones.
**Verificación:** Las tablas existen.

### 16.4. Gestión de Afiliados (Admin)

Contexto:** Los administradores deben poder activar/desactivar afiliados, ver sus estadísticas y gestionar pagos.
Crear `Admin\AffiliateController.php`.
Método `index()`:
Listar usuarios que son afiliados (`Affiliate::with('user')->paginate()`).
Mostrar estadísticas básicas (clics, referidos, saldo).
Vista `Admin/Affiliates/Index.vue`.
Método `edit(Affiliate $affiliate)` y `update(Request $request, Affiliate $affiliate)`:
Permitir al admin activar/desactivar el afiliado, ajustar tasa de comisión, mínimo de pago.
Vista `Admin/Affiliates/Edit.vue`.
(Opcional) Proceso para que un usuario solicite ser afiliado. Por ahora, el admin los crea/asigna.
Añadir enlace "Afiliados" en `AdminLayout.vue`.
**Verificación:\*\* Admin puede ver y editar afiliados.

### 16.5. Lógica de Seguimiento de Clics y Referidos (Básica)

Contexto:** Registrar cuando alguien usa un enlace de afiliado.
**Middleware de Afiliado (`TrackAffiliateReferralMiddleware`):**
Si la URL contiene un `?ref=REFERRAL_CODE`:
Buscar el afiliado por `referral_code`.
Si existe y está activo, registrar en `affiliate_clicks`.
Guardar `affiliate_id` en la sesión del visitante por X tiempo (ej. 30-90 días).
Aplicar este middleware a rutas públicas relevantes (ej. página de inicio, páginas de productos).
**Registro de Signup:**
Al registrar un nuevo usuario (`RegisteredUserController@store` o similar):
Si hay un `affiliate_id` en la sesión, crear un registro en `affiliate_signups` con `status = 'pending'`.
**Verificación:\*\* Los clics y registros de referidos se rastrean (básicamente).

---

¡Sistema de Afiliados (Base Admin y Seguimiento) Implementado!\*\*
La lógica de cálculo de comisiones, aprobación de referidos y el panel del afiliado son tareas para después.

````



Consideraciones y pasos iniciales para preparar la aplicación para un entorno de producción.

```diff
b/E:\herd\geminis\Tareas\Geminis_Tareas_50.md
# Geminis - Plan de Tareas Detallado - Parte 50

Este documento se enfoca en los preparativos para el despliegue a producción y optimizaciones.

## Fase 17: Despliegue y Mantenimiento

### 17.1. Revisión de Configuración para Producción
Contexto:** Asegurar que las configuraciones sean adecuadas para un entorno en vivo.
**Archivo `.env`:**
Crear un `.env.production` o asegurar que el `.env` del servidor de producción tenga:
`APP_ENV=production`
`APP_DEBUG=false`
`APP_KEY` (¡Debe ser única y segura! Generar con `php artisan key:generate` en producción si es el primer despliegue).
Configuraciones de base de datos correctas para producción.
Configuraciones de `MAIL_*` para el envío real de correos.
Claves API de servicios externos (Stripe, PayPal, etc.) para modo 'live'.
`SESSION_DRIVER=database` o `redis` (en lugar de `file` para mejor rendimiento en balanceo de carga).
`CACHE_STORE=redis` o `memcached` (en lugar de `file`).
`QUEUE_CONNECTION=redis` o `database` (en lugar de `sync`).
**Permisos de Directorio:**
Asegurar que los directorios `storage` y `bootstrap/cache` tengan permisos de escritura por el servidor web.
**Verificación:** Las variables de entorno críticas están configuradas para producción.

### 17.2. Optimizaciones de Laravel
Contexto:** Comandos para mejorar el rendimiento en producción.
En el script de despliegue o manualmente después de cada despliegue:
`php artisan config:cache` (Combina todos los archivos de configuración en uno solo).
`php artisan route:cache` (Crea un archivo de caché de rutas).
`php artisan view:cache` (Precompila todas las vistas Blade).
`php artisan event:cache` (Si usas descubrimiento de eventos).
`composer install --optimize-autoloader --no-dev` (Optimiza el autoloader de Composer y no instala dependencias de desarrollo).
`npm run build` (Compila los assets de frontend para producción).
**Verificación:** Los comandos se ejecutan sin error.

### 17.3. Configuración del Servidor Web (Nginx/Apache)
Contexto:** Asegurar que el servidor web esté configurado correctamente para servir la aplicación Laravel.
Apuntar la raíz del documento del servidor web al directorio `public` de Laravel.
Configurar reglas de reescritura (rewrite rules) para manejar las "pretty URLs" (Laravel ya incluye un `.htaccess` para Apache; para Nginx se necesita configuración específica).
Configurar SSL/HTTPS (altamente recomendado).
**Verificación:** La aplicación es accesible a través del dominio de producción y HTTPS funciona.

### 17.4. Configuración del Supervisor para Workers de Cola
Contexto:** Asegurar que los workers de la cola de Laravel se ejecuten continuamente.
Instalar Supervisor en el servidor (si no está).
Crear un archivo de configuración de Supervisor para `php artisan queue:work`.
Especificar el comando, usuario, número de procesos, auto-restart, etc.
**Verificación:** Los workers de la cola están corriendo y procesando jobs.

---
¡Preparativos para Despliegue y Optimización Iniciados!**
Estos son pasos fundamentales para un entorno de producción estable y eficiente. El despliegue real puede involucrar más pasos dependiendo del proveedor de hosting y la complejidad.
````

¡Y con eso, tienes definidas las tareas hasta la número 50! Esta es una planificación muy completa que cubre la gran mayoría de las funcionalidades que se esperan en un sistema como Geminis.

Recuerda que esta es una guía flexible. A medida que avancemos, podemos reevaluar, priorizar y ajustar las tareas según sea necesario.

Cuando estés listo para seguir, ya sabes el procedimiento. ¡Estoy aquí para ayudarte a construir Geminis, tarea por tarea!
