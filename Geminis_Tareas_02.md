# Geminis - Plan de Tareas Detallado - Parte 02

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en completar el CRUD para el módulo de Usuarios en el panel de administración.

## Fase 1: Módulo de Usuarios (CRUD Básico - Panel de Administración) - Continuación

### 1.6. Vista y Formulario de Creación de Usuarios (`resources/js/Pages/Admin/Users/Create.vue`)
*   `[ ]` En `Admin/UserController.php`, implementar el método `create()`:
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
*   `[ ]` Crear el archivo `resources/js/Pages/Admin/Users/Create.vue`.
*   `[ ]` Implementar el formulario de creación en `Create.vue`. Incluir campos para: `name`, `email`, `password`, `password_confirmation`, `role` (select), `reseller_id` (input numérico, podría ser un select de revendedores más adelante), `company_name`, `phone_number`, `address_line1`, `address_line2`, `city`, `state_province`, `postal_code`, `country_code` (select o input), `status` (select), `language_code` (select o input), `currency_code` (select o input).
    *   Usar el componente `useForm` de Inertia para manejar el estado del formulario y los envíos.
    *   Aplicar estilos básicos con Tailwind CSS.
    ```vue
    // resources/js/Pages/Admin/Users/Create.vue
    <script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    // Si tienes un layout de Admin, impórtalo:
    // import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumiendo que lo crearás
    // Importar componentes de formulario reutilizables si los tienes (ej: InputLabel, TextInput, PrimaryButton, SelectInput)
    // Por ahora, usaremos elementos HTML estándar con clases de Tailwind.

    const form = useForm({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'client', // Default role
      reseller_id: null,
      company_name: '',
      phone_number: '',
      address_line1: '',
      address_line2: '',
      city: '',
      state_province: '',
      postal_code: '',
      country_code: 'ES', // Default country (ej. España)
      status: 'active', // Default status
      language_code: 'es', // Default language
      currency_code: 'EUR', // Default currency
    });

    const submit = () => {
      form.post(route('admin.users.store'), {
        onFinish: () => {
          // No reseteamos todo el form para que los errores se mantengan si los hay
          // Solo reseteamos campos sensibles como password si el envío fue exitoso (se maneja mejor con onSuccess)
        },
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        }
      });
    };

    // Opciones para selects (ejemplos, podrías obtenerlos de props o constantes globales)
    const roleOptions = [
      { value: 'admin', label: 'Admin' },
      { value: 'client', label: 'Client' },
      { value: 'reseller', label: 'Reseller' },
    ];
    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'suspended', label: 'Suspended' },
    ];
    // TODO: Considerar cargar listas de países, idiomas, monedas dinámicamente o desde un archivo de configuración.
    </script>

    <template>
      <!-- <AdminLayout> -->
        <Head title="Create User" />

        <div class="py-12">
          <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Create New User</h1>
                <form @submit.prevent="submit">
                  <!-- Name -->
                  <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="form.name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                  </div>

                  <!-- Email -->
                  <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" v-model="form.email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                  </div>

                  <!-- Password -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                      <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                      <input type="password" v-model="form.password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                      <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
                    </div>
                    <div>
                      <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                      <input type="password" v-model="form.password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                  </div>

                  <!-- Role -->
                  <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option v-for="option in roleOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                    <div v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</div>
                  </div>
                  
                  <!-- Reseller ID (Conditional) -->
                  <div class="mb-4" v-if="form.role === 'client'">
                    <label for="reseller_id" class="block text-sm font-medium text-gray-700 mb-1">Reseller ID (if client of a reseller)</label>
                    <input type="number" v-model="form.reseller_id" id="reseller_id" placeholder="Leave empty if direct client" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.reseller_id" class="text-sm text-red-600 mt-1">{{ form.errors.reseller_id }}</div>
                  </div>

                  <!-- Company Name -->
                  <div class="mb-4">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" v-model="form.company_name" id="company_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.company_name" class="text-sm text-red-600 mt-1">{{ form.errors.company_name }}</div>
                  </div>
                  
                  {/* TODO: Add other fields: phone_number, address_line1, address_line2, city, state_province, postal_code, country_code, language_code, currency_code, siguiendo el mismo patrón */}

                  <!-- Status -->
                  <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                    <div v-if="form.errors.status" class="text-sm text-red-600 mt-1">{{ form.errors.status }}</div>
                  </div>

                  <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
                    <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                      {{ form.processing ? 'Creating...' : 'Create User' }}
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
*   `[ ]` **Verificación:**
    *   Navegar a `/admin/users/create` en tu navegador.
    *   Deberías ver el formulario de creación de usuarios con todos los campos visibles (o condicionalmente visibles como `reseller_id`).
    *   El botón "Create User" debería estar visible.
    *   El botón "Cancel" debería llevar de vuelta a `/admin/users`.

### 1.7. Lógica de Almacenamiento de Usuarios (`store` en `Admin\UserController`) y Validación
*   `[ ]` Crear un Form Request para la validación de la creación de usuarios:
    ```bash
    php artisan make:request Admin/StoreUserRequest
    ```
*   `[ ]` En `app/Http/Requests/Admin/StoreUserRequest.php`:
    *   Poner `authorize()` a `true` (o implementar lógica de autorización si es necesario más adelante, por ejemplo, `return auth()->user()->isAdmin();` si tuvieras un método `isAdmin()` en el modelo User o usaras roles de Spatie).
    *   Definir las reglas de validación en el método `rules()` para todos los campos del formulario, según `Geminis_Estructura.md`.
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
*   `[ ]` En `Admin/UserController.php`, actualizar el método `store()` para usar `StoreUserRequest` y crear el usuario:
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
*   `[ ]` **Verificación:**
    *   Intentar enviar el formulario de creación vacío. Deberían aparecer los mensajes de error de validación debajo de cada campo correspondiente en la vista `Create.vue`.
    *   Intentar crear un usuario con un email que ya existe. Debería mostrar el error de validación de email único.
    *   Intentar crear un usuario con contraseñas que no coinciden. Debería mostrar el error de `password_confirmation`.
    *   Crear un usuario válido. Debería ser redirigido a `/admin/users` con un mensaje de éxito.
        *   **Nota:** Para ver el mensaje flash de éxito, necesitarás añadir lógica en tu layout principal o en `Index.vue` para mostrar los mensajes flash de Inertia. Ejemplo básico en `Index.vue` (o en un layout):
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
            <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
              {{ successMessage }}
            </div>
            ```
    *   Verificar que el nuevo usuario existe en la base de datos con la contraseña hasheada y todos los datos correctos.

---
<!-- Siguientes pasos: Formulario y lógica de Edición, Actualización y Eliminación para Usuarios -->

### 1.8. Vista y Formulario de Edición de Usuarios (`resources/js/Pages/Admin/Users/Edit.vue`)
*   `[ ]` En `Admin/UserController.php`, implementar el método `edit()`:
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
*   `[ ]` Crear el archivo `resources/js/Pages/Admin/Users/Edit.vue`.
*   `[ ]` Implementar el formulario de edición en `Edit.vue`. Este formulario será muy similar al de `Create.vue`, pero se inicializará con los datos del usuario que se está editando.
    *   Los campos de contraseña serán opcionales (solo se actualizan si se rellenan).
    *   Usar el componente `useForm` de Inertia.
    *   Aplicar estilos básicos con Tailwind CSS.
    ```vue
    // resources/js/Pages/Admin/Users/Edit.vue
    <script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    // import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumiendo que lo crearás

    const props = defineProps({
      user: Object, // El usuario que se está editando, pasado desde el controlador
      // roles: Array, // Si pasas opciones de roles desde el controlador
    });

    const form = useForm({
      _method: 'PUT', // Necesario para el envío de formularios de actualización con Inertia
      name: props.user.name,
      email: props.user.email,
      password: '', // Dejar vacío, solo se actualiza si se ingresa algo
      password_confirmation: '',
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
      form.post(route('admin.users.update', props.user.id), { // Usar form.post con _method: 'PUT'
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        }
      });
    };

    // Opciones para selects (ejemplos, podrías obtenerlos de props o constantes globales)
    const roleOptions = [
      { value: 'admin', label: 'Admin' },
      { value: 'client', label: 'Client' },
      { value: 'reseller', label: 'Reseller' },
    ];
    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'suspended', label: 'Suspended' },
    ];
    // TODO: Considerar cargar listas de países, idiomas, monedas dinámicamente o desde un archivo de configuración.
    </script>

    <template>
      <!-- <AdminLayout> -->
        <Head :title="'Edit User - ' + user.name" />

        <div class="py-12">
          <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Edit User: {{ user.name }}</h1>
                <form @submit.prevent="submit">
                  <!-- Name -->
                  <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="form.name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                  </div>

                  <!-- Email -->
                  <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" v-model="form.email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                  </div>

                  <!-- Password (Optional) -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                      <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                      <input type="password" v-model="form.password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                      <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
                    </div>
                    <div>
                      <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                      <input type="password" v-model="form.password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                  </div>

                  <!-- Role -->
                  <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option v-for="option in roleOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                    <div v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</div>
                  </div>
                  
                  <!-- Reseller ID (Conditional) -->
                  <div class="mb-4" v-if="form.role === 'client'">
                    <label for="reseller_id" class="block text-sm font-medium text-gray-700 mb-1">Reseller ID (if client of a reseller)</label>
                    <input type="number" v-model="form.reseller_id" id="reseller_id" placeholder="Leave empty if direct client" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.reseller_id" class="text-sm text-red-600 mt-1">{{ form.errors.reseller_id }}</div>
                  </div>

                  <!-- Company Name -->
                  <div class="mb-4">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" v-model="form.company_name" id="company_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    <div v-if="form.errors.company_name" class="text-sm text-red-600 mt-1">{{ form.errors.company_name }}</div>
                  </div>

                  {/* TODO: Add other fields: phone_number, address_line1, address_line2, city, state_province, postal_code, country_code, language_code, currency_code, siguiendo el mismo patrón que en Create.vue pero inicializados con props.user */}

                  <!-- Status -->
                  <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                    <div v-if="form.errors.status" class="text-sm text-red-600 mt-1">{{ form.errors.status }}</div>
                  </div>

                  <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
                    <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                      {{ form.processing ? 'Updating...' : 'Update User' }}
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
*   `[ ]` **Verificación:**
    *   Desde la tabla de listado de usuarios (`/admin/users`), hacer clic en el enlace "Edit" de un usuario.
    *   Deberías ser redirigido a `/admin/users/{id}/edit`.
    *   El formulario de edición debería mostrarse con los datos del usuario seleccionado.
    *   El botón "Update User" debería estar visible.

---
<!-- Siguientes pasos: Lógica de Actualización y Eliminación para Usuarios -->

### 1.9. Lógica de Actualización de Usuarios (`update` en `Admin\UserController`) y Validación
*   `[ ]` Crear un Form Request para la validación de la actualización de usuarios:
    ```bash
    php artisan make:request Admin/UpdateUserRequest
    ```
*   `[ ]` En `app/Http/Requests/Admin/UpdateUserRequest.php`:
    *   Poner `authorize()` a `true` (o implementar lógica de autorización).
    *   Definir las reglas de validación en `rules()`. Serán similares a `StoreUserRequest`, pero el email debe ser único ignorando al usuario actual, y la contraseña será opcional.
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
*   `[ ]` En `Admin/UserController.php`, actualizar el método `update()` para usar `UpdateUserRequest` y actualizar el usuario:
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
*   `[ ]` **Verificación:**
    *   Intentar enviar el formulario de edición con datos inválidos (ej: email vacío, email duplicado de otro usuario). Deberían aparecer los errores de validación.
    *   Editar un usuario sin cambiar la contraseña. La contraseña no debería cambiar en la BD.
    *   Editar un usuario y cambiar la contraseña. La contraseña debería actualizarse (hasheada) en la BD.
    *   Actualizar un usuario con datos válidos. Debería ser redirigido a `/admin/users` con un mensaje de éxito, y los cambios deberían reflejarse en la tabla de listado y en la BD.

### 1.10. Lógica de Eliminación de Usuarios (`destroy` en `Admin\UserController`)
*   `[ ]` En `Admin/UserController.php`, implementar el método `destroy()`:
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
*   `[ ]` En `resources/js/Pages/Admin/Users/Index.vue`, añadir un botón de "Delete" para cada usuario y la lógica para enviar la solicitud DELETE.
    *   **Importante:** Usar un diálogo de confirmación antes de eliminar.
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
    *   Añadir el botón en la tabla dentro del `<td>` de acciones:
    ```html
    <!-- Dentro del bucle v-for="user in users.data" en Index.vue -->
    <button @click="deleteUser(user.id, user.name)" class="text-red-600 hover:text-red-900">Delete</button>
    ```
*   `[ ]` **Verificación:**
    *   Hacer clic en el botón "Delete" de un usuario en la lista. Debería aparecer un diálogo de confirmación.
    *   Cancelar la eliminación. No debería pasar nada.
    *   Confirmar la eliminación. El usuario debería desaparecer de la lista (y ser marcado como `deleted_at` en la BD si se usa SoftDeletes). Debería aparecer un mensaje de éxito.
    *   Intentar eliminar un usuario que no debería poder ser eliminado (ej: el propio admin logueado, si implementas esa lógica). La acción debería fallar o ser prevenida.

---
**¡CRUD Básico de Usuarios Completado!**
En este punto, deberías tener una gestión funcional de usuarios desde el "panel de administración" (aunque aún sea público).
Los siguientes pasos podrían incluir:
    - Implementación de Layouts de Administración.
    - Aplicación de Middlewares de Autenticación y Roles.
    - Mejoras en la UI/UX (componentes de formulario reutilizables, notificaciones más elegantes, etc.).
    - Implementación de la funcionalidad "Ver Usuario" (método `show` y vista `Show.vue`).
    - Filtros y búsqueda en la tabla de listado de usuarios.
