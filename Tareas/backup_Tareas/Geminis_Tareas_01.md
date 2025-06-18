# Geminis - Plan de Tareas Detallado

Este documento detalla el paso a paso para la construcción del sistema Geminis. Cada tarea debe ser completada y verificada antes de pasar a la siguiente.

## Fase -1: Control de Versiones (Git y GitHub)

**Objetivo:** Establecer el control de versiones para el proyecto y asegurar que los cambios se registren regularmente.

*   `[ ]` **Inicializar Repositorio Git (si no existe):**
    *   Dentro del directorio raíz de tu proyecto (`hostgemini`), ejecuta:
        ```bash
        git status
        git add
        git status
        git comit - m segunda version
        git push
        ```
*   `[ ]` **Crear Repositorio en GitHub (o similar):**
    *   Ve a GitHub (o tu plataforma de preferencia) y crea un nuevo repositorio (puede ser privado o público).
    *   Sigue las instrucciones de GitHub para conectar tu repositorio local al remoto. Generalmente implica comandos como:
        ```bash
        git remote add origin URL_DEL_REPOSITORIO_REMOTO
        git branch -M main
        git push -u origin main
        ```
*   `[ ]` **Recordatorio Constante:** Antes de cada modificación importante, instalación de paquetes nuevos, o al finalizar una sección de tareas (ej. al final de la Fase 0, o antes de empezar la Fase 1), **realiza un commit y un push a tu repositorio remoto.**
    *   Ejemplo de flujo de commit:
        ```bash
        git add .
        git commit -m "Descripción clara de los cambios realizados"
        git push origin main
        ```

Este documento detalla el paso a paso para la construcción del sistema Geminis. Cada tarea debe ser completada y verificada antes de pasar a la siguiente.

## Fase 0: Configuración Inicial del Proyecto

**Objetivo:** Tener un proyecto Laravel funcional con las herramientas base (Vue, Inertia) y la base de datos conectada.

### 0.1. Creación del Proyecto Laravel (Si es nuevo)
*   `[ ]` Si aún no existe, crear un nuevo proyecto Laravel:
    ```bash
    composer create-project laravel/laravel hostgemini
    cd hostgemini
    ```
*   `[ ]` **Verificación:** Acceder a la URL base del proyecto en el navegador y ver la página de bienvenida de Laravel.

### 0.2. Configuración del Entorno
*   `[ ]` Copiar `.env.example` a `.env` si no existe.
*   `[ ]` Configurar las variables de entorno en `.env`:
    *   `APP_NAME="HostGemini"` (o el nombre que prefieras)
    *   `APP_URL=http://hostgemini.test` (o tu URL de desarrollo local)
    *   `DB_CONNECTION=mysql`
    *   `DB_HOST=127.0.0.1`
    *   `DB_PORT=3306`
    *   `DB_DATABASE=hostgemini` (crea esta base de datos vacía en tu MySQL)
    *   `DB_USERNAME=root` (o tu usuario de BD)
    *   `DB_PASSWORD=` (o tu contraseña de BD)
*   `[ ]` Generar la clave de aplicación:
    ```bash
    php artisan key:generate
    ```
*   `[ ]` Ejecutar las migraciones iniciales de Laravel (si las hay por defecto):
    ```bash
    php artisan migrate
    ```
*   `[ ]` **Verificación:** El comando `php artisan migrate` se ejecuta sin errores. Puedes conectar a tu base de datos `hostgemini` y ver las tablas creadas por Laravel (users, password_resets, etc., si no las eliminaste).

### 0.3. Instalación de Laravel Breeze o Jetstream (para Autenticación Base e Inertia/Vue)
*   **Decisión:** Usaremos Laravel Breeze con la opción de Inertia + Vue. Es más ligero para empezar.
*   `[ ]` Instalar Laravel Breeze:
    ```bash
    composer require laravel/breeze --dev
    ```
*   `[ ]` Instalar el scaffolding de Breeze con Vue + Inertia:
    ```bash
    php artisan breeze:install vue --ssr
    # O sin SSR si no lo necesitas inicialmente:
    # php artisan breeze:install vue
    ```
*   `[ ]` Instalar dependencias NPM:
    ```bash
    npm install
    ```
*   `[ ]` Compilar los assets:
    ```bash
    npm run dev
    # O npm run build para producción
    ```
*   `[ ]` Ejecutar las migraciones (Breeze añade algunas):
    ```bash
    php artisan migrate
    ```
*   `[ ]` **Verificación:**
    *   Visitar `/login` en el navegador. Deberías ver el formulario de login de Breeze.
    *   Visitar `/register`. Deberías ver el formulario de registro.
    *   Poder registrar un nuevo usuario y ser redirigido al `/dashboard`.
    *   La tabla `users` en la base de datos ahora tiene los campos que Breeze necesita (name, email, password, etc.).

### 0.4. Limpieza Inicial de Rutas y Vistas de Breeze (Opcional, para nuestro enfoque)
*   **Contexto:** Dado que construiremos los paneles de admin, reseller y client por separado y la autenticación se manejará de forma más específica después, podemos simplificar las rutas y vistas que Breeze genera por defecto si interfieren con el desarrollo público inicial. Por ahora, las dejaremos, ya que el login único es un objetivo.

## Fase 1: Módulo de Usuarios (CRUD Básico - Panel de Administración)

**Objetivo:** Crear la gestión completa (Listar, Crear, Ver, Editar, Eliminar) para los usuarios desde un futuro panel de administración. Por ahora, las rutas serán públicas para facilitar el desarrollo.

### 1.1. Migración de la Tabla `users`
*   **Contexto:** La migración de `users` ya fue creada por Laravel y modificada por Breeze. Necesitamos ajustarla según nuestro `Geminis_Estructura.md`.
*   `[ ]` Localizar el archivo de migración `..._create_users_table.php` en `database/migrations/`.
*   `[ ]` Modificar el método `up()` de la migración para que coincida con la definición de la tabla `users` en `Geminis_Estructura.md`. Asegúrate de incluir todos los campos: `role`, `reseller_id`, `company_name`, `phone_number`, direcciones, `status`, `language_code`, `currency_code`, `last_login_at`, y `deleted_at` (softDeletes).
    *   Ejemplo de cómo añadir `role` y `reseller_id`:
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
    *   **Nota sobre `reseller_id` FK:** Si `reseller_id` es una FK a `users.id`, la tabla `users` ya existe. Si hay problemas con la auto-referencia en la misma migración, se puede añadir la constraint en una migración separada después. Por simplicidad inicial, podrías solo definirlo como `->nullable()->unsignedBigInteger('reseller_id')` y añadir el índice y la FK después. Por ahora, usaremos `->constrained('users')` asumiendo que el SGBD lo maneja bien o lo ajustaremos.
*   `[ ]` Si ya ejecutaste migraciones y necesitas rehacer la de `users`:
    ```bash
    php artisan migrate:fresh # ¡CUIDADO! Esto borra TODA la base de datos.
    # O, si quieres ser más selectivo, puedes hacer rollback de la última y reintentar.
    # php artisan migrate:rollback --step=1
    # php artisan migrate
    ```
*   `[ ]` **Verificación:**
    *   El comando `php artisan migrate` (o `migrate:fresh`) se ejecuta sin errores.
    *   Inspeccionar la estructura de la tabla `users` en tu cliente de base de datos. Debe tener todos los campos definidos en `Geminis_Estructura.md` con los tipos correctos.

    ### 1.2. Modelo `User`
*   **Contexto:** El modelo `App\Models\User.php` ya existe. Necesitamos actualizarlo.
*   `[ ]` Abrir `app/Models/User.php`.
*   `[ ]` Asegurar que la trait `HasFactory` y `Notifiable` estén presentes. Añadir `SoftDeletes`.
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
*   `[ ]` Actualizar la propiedad `$fillable` para incluir los nuevos campos que queremos que sean asignables masivamente.
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
*   `[ ]` Actualizar la propiedad `$hidden` si es necesario (generalmente `password` y `remember_token` ya están).
*   `[ ]` Actualizar la propiedad `$casts` para los campos que lo necesiten (ej: `email_verified_at` a `datetime`, `last_login_at` a `datetime`). Los ENUM no necesitan cast explícito aquí, pero las validaciones se encargarán de los valores permitidos.
    ```php
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime', // Añadir
        'password' => 'hashed', // Breeze ya lo pone
    ];
    ```
*   `[ ]` (Opcional por ahora, pero bueno para el futuro) Definir relaciones Eloquent. Por ejemplo, si un usuario es un revendedor, podría tener muchos clientes:
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
*   `[ ]` **Verificación:**
    *   Abrir `php artisan tinker`.
    *   Intentar crear un usuario con los nuevos campos:
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
    *   Verificar que el usuario se crea en la base de datos con los valores correctos.
    *   `App\Models\User::first()->toArray();` debe mostrar los campos.

### 1.3. Controlador de Usuarios para Administración (`Admin\UserController`)
*   `[ ]` Crear un controlador resource para la gestión de usuarios en el panel de administración:
    ```bash
    php artisan make:controller Admin/UserController --resource --model=User
    ```
    Esto creará `app/Http/Controllers/Admin/UserController.php` con los métodos `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
*   `[ ]` **Verificación:** El archivo del controlador existe en la ruta especificada y contiene los métodos esperados.

### 1.4. Rutas para el CRUD de Usuarios (Administración)
*   `[ ]` Abrir `routes/web.php`.
*   `[ ]` Definir las rutas resource para el `Admin\UserController`. Por ahora, sin middleware de autenticación o rol para facilitar el desarrollo.
    ```php

    use App\Http\Controllers\Admin\UserController as AdminUserController; // Alias para evitar colisiones si hay otros UserController


    // ... otras rutas

    // Rutas para la gestión de Usuarios (Admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
    });
        
    ```
    *   **Nota:** La ruta base será `/admin/users`. Los middlewares de autenticación y rol se aplicarán a este grupo de rutas más adelante.

*   `[ ]` **Verificación:**
    *   Ejecutar `php artisan route:list`.
    *   Verificar que las rutas para `admin.users.index`, `admin.users.create`, etc., estén listadas y apunten a `Admin\UserController`.


### 1.5. Vista de Listado de Usuarios (`resources/js/Pages/Admin/Users/Index.vue`)
*   `[ ]` En el método `index` de `Admin\UserController.php`, obtener todos los usuarios y pasarlos a una vista Inertia.
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
*   `[ ]` Crear el directorio `resources/js/Pages/Admin/Users/`.
*   `[ ]` Crear el archivo `resources/js/Pages/Admin/Users/Index.vue`.
*   `[ ]` Implementar una tabla básica para mostrar los usuarios (ID, Nombre, Email, Rol, Status). Usar Tailwind CSS para estilos básicos. (El código Vue se proporcionó en la conversación anterior, puedes copiarlo desde allí).
*   `[ ]` **Verificación:**
    *   Asegúrate de que `npm run dev` esté ejecutándose.
    *   Navegar a `/admin/users` en tu navegador.
    *   Deberías ver la tabla con los usuarios (si creaste alguno en el paso 1.2) o "No users found".
    *   La paginación debería funcionar si tienes más de 10 usuarios.
    *   El botón "Create User" debería estar visible y llevar a `admin.users.create` (aunque la página de creación aún no exista).


---
<!-- Siguientes pasos: Crear formulario, validaciones, lógica de store, edit, update, delete para Usuarios  Geminis_Tareas_02 -->
