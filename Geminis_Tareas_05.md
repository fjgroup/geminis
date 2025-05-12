# Geminis - Plan de Tareas Detallado - Parte 05

Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en completar la funcionalidad "Ver Usuario" y comenzando el módulo de Productos para el panel de administración.

## Fase 2: Mejoras en el Panel de Administración y Seguridad Básica - Continuación

### 2.8. Implementación de la Funcionalidad "Ver Usuario" (`show` en `Admin\UserController`)
*   **Contexto:** Actualmente no tenemos una vista dedicada para ver los detalles completos de un usuario.
*   `[ ]` En `app/Http/Controllers/Admin/UserController.php`, implementar el método `show(User $user)`:
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
    ```
*   `[ ]` Crear la vista `resources/js/Pages/Admin/Users/Show.vue`:
    ```vue
    // resources/js/Pages/Admin/Users/Show.vue
    <script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Head, Link } from '@inertiajs/vue3';

    const props = defineProps({
      user: Object,
    });
    </script>

    <template>
        <AdminLayout :title="'View User - ' + user.name">
            <Head :title="'View User - ' + user.name" />

            <template #header>
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        User Details: {{ user.name }}
                    </h2>
                    <Link :href="route('admin.users.edit', user.id)" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                        Edit User
                    </Link>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 md:p-8 bg-white border-b border-gray-200 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">User Information</h3>
                                <dl class="mt-2 divide-y divide-gray-200">
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.name }}</dd>
                                    </div>
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.email }}</dd>
                                    </div>
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 capitalize">{{ user.role }}</dd>
                                    </div>
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 capitalize">{{ user.status }}</dd>
                                    </div>
                                    <div v-if="user.role === 'client' && user.reseller_id" class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Belongs to Reseller ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.reseller_id }}</dd>
                                        {/* TODO: Podríamos mostrar el nombre del revendedor si cargamos la relación */}
                                    </div>
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Registered On</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ new Date(user.created_at).toLocaleDateString() }}</dd>
                                    </div>
                                    <div v-if="user.last_login_at" class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ new Date(user.last_login_at).toLocaleString() }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div v-if="user.company_name || user.phone_number">
                                <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
                                <dl class="mt-2 divide-y divide-gray-200">
                                    <div v-if="user.company_name" class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.company_name }}</dd>
                                    </div>
                                    <div v-if="user.phone_number" class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.phone_number }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div v-if="user.address_line1">
                                <h3 class="text-lg font-medium text-gray-900">Address</h3>
                                <dl class="mt-2 divide-y divide-gray-200">
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            {{ user.address_line1 }}<br>
                                            <span v-if="user.address_line2">{{ user.address_line2 }}<br></span>
                                            {{ user.city }}, {{ user.state_province }} {{ user.postal_code }}<br>
                                            {{ user.country_code }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">
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
*   `[ ]` En `resources/js/Pages/Admin/Users/Index.vue`, añadir un enlace "View" para cada usuario en la tabla que apunte a la ruta `admin.users.show`.
    ```diff
    --- a/resources/js/Pages/Admin/Users/Index.vue
    +++ b/resources/js/Pages/Admin/Users/Index.vue
    @@ -41,7 +41,8 @@
                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.role }}</td>
                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.status }}</td>
                       <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
-                        <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
+                        <Link :href="route('admin.users.show', user.id)" class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
+                        <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                         <!-- Botón de eliminar vendrá después -->
                         <button @click="deleteUser(user.id, user.name)" class="text-red-600 hover:text-red-900">Delete</button>
                       </td>
    ```
*   `[ ]` **Verificación:**
    *   En la lista de usuarios (`/admin/users`), hacer clic en el enlace "View" de un usuario.
    *   Deberías ser redirigido a `/admin/users/{id}`.
    *   La página debería mostrar los detalles del usuario seleccionado, utilizando el `AdminLayout`.
    *   El botón "Edit User" en la página de detalles debería llevar al formulario de edición.
    *   El botón "Back to Users List" debería llevar de vuelta a `/admin/users`.
    *   Intentar acceder a la URL de `show` de un usuario como un usuario no administrador (si el middleware `admin` o la policy `view` fallan). Debería ser denegado.

---
<!-- Siguientes pasos: Inicio del Módulo de Productos -->

## Fase 3: Módulo de Productos (CRUD Básico - Panel de Administración)

**Objetivo:** Crear la estructura básica para la gestión de productos (servicios de hosting, dominios, etc.) desde el panel de administración.

### 3.1. Migración de la Tabla `products`
*   **Contexto:** Necesitamos una tabla para almacenar la información de los productos ofrecidos.
*   `[ ]` Crear la migración para la tabla `products`:
    ```bash
    php artisan make:migration create_products_table
    ```
*   `[ ]` En el archivo de migración recién creado (ej: `database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php`), definir la estructura de la tabla `products` según `Geminis_Estructura.md`.
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
    *   **Nota sobre `welcome_email_template_id`:** He comentado la constraint por ahora, asumiendo que la tabla `email_templates` se creará más adelante. Si ya existe, puedes descomentarla.
*   `[ ]` Ejecutar la migración:
    ```bash
    php artisan migrate
    ```
*   `[ ]` **Verificación:**
    *   El comando `php artisan migrate` se ejecuta sin errores.
    *   Inspeccionar la estructura de la tabla `products` en tu cliente de base de datos. Debe tener todos los campos definidos.

### 3.2. Modelo `Product`
*   `[ ]` Crear el modelo `Product`:
    ```bash
    php artisan make:model Product -mfs # El -mfs crea migración, factory y seeder (ya creamos la migración manualmente)
    # Si ya creaste la migración, solo: php artisan make:model Product -fs
    # O solo: php artisan make:model Product
    ```
*   `[ ]` En `app/Models/Product.php`:
    *   Añadir `SoftDeletes`.
    *   Definir la propiedad `$fillable` con los campos de la tabla `products`.
    *   Definir la propiedad `$casts` si es necesario (ej: para los ENUM si quieres que se comporten de manera específica, aunque no es estrictamente necesario para ENUMs de BD).
    *   Definir relaciones (ej: `owner()` para el `users.id` del propietario, `pricings()` para `product_pricing`).
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
*   `[ ]` **Verificación:**
    *   Abrir `php artisan tinker`.
    *   Intentar crear un producto: `App\Models\Product::create(['name' => 'Test Product', 'slug' => 'test-product', 'type' => 'shared_hosting', 'status' => 'active']);`
    *   Verificar que se crea en la base de datos.

---
<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->

<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->

### 3.3. Controlador de Productos para Administración (`Admin\ProductController`)
*   `[ ]` Crear un controlador resource para la gestión de productos en el panel de administración:
    ```bash
    php artisan make:controller Admin/ProductController --resource --model=Product
    ```
*   `[ ]` **Verificación:** El archivo `app/Http/Controllers/Admin/ProductController.php` existe y contiene los métodos CRUD (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).

### 3.4. Rutas para el CRUD de Productos (Administración)
*   `[ ]` En `routes/web.php`, definir las rutas resource para `Admin\ProductController` dentro del grupo de administración:
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
*   `[ ]` **Verificación:**
    *   Ejecutar `php artisan route:list`.
    *   Verificar que las rutas para `admin.products.index`, `admin.products.create`, etc., estén listadas y apunten a `Admin\ProductController`.

### 3.5. Vista de Listado de Productos (`resources/js/Pages/Admin/Products/Index.vue`)
*   `[ ]` En el método `index` de `Admin\ProductController.php`, obtener todos los productos y pasarlos a una vista Inertia.
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
*   `[ ]` Crear el directorio `resources/js/Pages/Admin/Products/`.
*   `[ ]` Crear el archivo `resources/js/Pages/Admin/Products/Index.vue`.
*   `[ ]` Implementar una tabla básica para mostrar los productos (ID, Nombre, Tipo, Propietario (si es de revendedor), Estado). Usar Tailwind CSS.
    ```vue
    // resources/js/Pages/Admin/Products/Index.vue
    <script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Head, Link } from '@inertiajs/vue3';
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
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Manage Products
                    </h2>
                    <Link :href="route('admin.products.create')" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Create Product
                    </Link>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <!-- TODO: Añadir tabla para listar productos -->
                            <p>Product listing table will go here.</p>
                            <p>Fields to show: ID, Name, Slug, Type, Owner (if reseller), Status, Actions (View, Edit, Delete)</p>
                            <!-- TODO: Añadir paginación -->
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>
    ```
    *   **Nota:** El contenido de la tabla es un placeholder. Se completará en los siguientes pasos.
*   `[ ]` Actualizar el sidebar en `resources/js/Layouts/AdminLayout.vue` para incluir un enlace a "Manage Products":
    ```diff
    --- a/resources/js/Layouts/AdminLayout.vue
    +++ b/resources/js/Layouts/AdminLayout.vue
    @@ -35,8 +35,9 @@
                               class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
                             Manage Users
                         </Link>
-                        {/* <!-- Otros enlaces del sidebar aquí --> */}
-                        <Link href="#"
+                        <Link :href="route('admin.products.index')"
+                              :class="{ 'bg-gray-900 text-white': route().current('admin.products.*') }"
                               class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white mt-2">
                             Products (Placeholder)
                         </Link>
    ```
*   `[ ]` **Verificación:**
    *   Asegúrate de que `npm run dev` esté ejecutándose.
    *   Navegar a `/admin/products` en tu navegador.
    *   Deberías ver la página con el `AdminLayout` y el placeholder "Product listing table will go here."
    *   El enlace "Products" en el sidebar debería estar activo y llevar a esta página.
    *   El botón "Create Product" debería estar visible.

---
<!-- Siguientes pasos: Completar la tabla de listado de productos, formularios de creación/edición, validaciones y lógica para Productos. -->
```

Con esto, `Geminis_Tareas_05.md` estaría completo. Hemos finalizado la vista de "Ver Usuario" y hemos sentado las bases para el módulo de Productos.

<!-- Siguientes pasos: Inicio del Módulo de Productos -->

## Fase 3: Módulo de Productos (CRUD Básico - Panel de Administración)

**Objetivo:** Crear la estructura básica para la gestión de productos (servicios de hosting, dominios, etc.) desde el panel de administración.

### 3.1. Migración de la Tabla `products`
*   **Contexto:** Necesitamos una tabla para almacenar la información de los productos ofrecidos.
*   `[ ]` Crear la migración para la tabla `products`:
    ```bash
    php artisan make:migration create_products_table
    ```
*   `[ ]` En el archivo de migración recién creado (ej: `database/migrations/xxxx_xx_xx_xxxxxx_create_products_table.php`), definir la estructura de la tabla `products` según `Geminis_Estructura.md`.
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
    *   **Nota sobre `welcome_email_template_id`:** He comentado la constraint por ahora, asumiendo que la tabla `email_templates` se creará más adelante. Si ya existe, puedes descomentarla.
*   `[ ]` Ejecutar la migración:
    ```bash
    php artisan migrate
    ```
*   `[ ]` **Verificación:**
    *   El comando `php artisan migrate` se ejecuta sin errores.
    *   Inspeccionar la estructura de la tabla `products` en tu cliente de base de datos. Debe tener todos los campos definidos.

### 3.2. Modelo `Product`
*   `[ ]` Crear el modelo `Product`:
    ```bash
    php artisan make:model Product -fs # -fs crea factory y seeder. La migración ya la hicimos.
    # O solo: php artisan make:model Product
    ```
*   `[ ]` En `app/Models/Product.php`:
    *   Añadir `SoftDeletes`.
    *   Definir la propiedad `$fillable` con los campos de la tabla `products`.
    *   Definir la propiedad `$casts` si es necesario.
    *   Definir relaciones (ej: `owner()` para `users.id`, `pricings()` para `product_pricing`).
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
*   `[ ]` **Verificación:**
    *   Abrir `php artisan tinker`.
    *   Intentar crear un producto: `App\Models\Product::create(['name' => 'Test Product', 'slug' => 'test-product', 'type' => 'shared_hosting', 'status' => 'active']);`
    *   Verificar que se crea en la base de datos.

---
<!-- Siguientes pasos: Controlador, Rutas y Vistas para Productos -->
