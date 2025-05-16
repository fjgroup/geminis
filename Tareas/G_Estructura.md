Geminis - Estructura del Proyecto y Base de Datos

1. Nombre del Proyecto (Sugerencia): HostGemini o GeminiPanel (podemos refinarlo luego).

2. Tecnologías Base:

-   Backend: Laravel (última versión estable, actualmente Laravel 12)
-   Frontend: Vue.js (última versión estable, actualmente Vue 3)
-   Interconexión Backend-Frontend: Inertia.js
-   Base de Datos: MySQL

3. Estructura de Directorios Clave (Más allá de la estándar de Laravel):

Si bien Laravel ya provee una estructura robusta, pondremos énfasis en cómo organizaremos los componentes específicos de nuestra aplicación:

plaintext
hostgemini/
├── app/
│ ├── Console/
│ ├── Exceptions/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Admin/ # Controladores para el panel de administración global
│ │ │ ├── Auth/ # Controladores de autenticación (Laravel Breeze/Jetstream)
│ │ │ ├── Client/ # Controladores para el portal del cliente final
│ │ │ ├── Reseller/ # Controladores para el panel del revendedor
│ │ │ └── Webhook/ # Controladores para webhooks (pasarelas de pago, etc.)
│ │ ├── Middleware/
│ │ │ ├── EnsureUserIsAdmin.php
│ │ │ ├── EnsureUserIsClient.php
│ │ │ ├── EnsureUserIsReseller.php
│ │ │ └── HandleInertiaRequests.php # Middleware de Inertia
│ │ └── Requests/ # Form Requests para validación centralizada
│ ├── Jobs/ # Trabajos en cola (ej: aprovisionamiento, emails)
│ ├── Listeners/ # Manejadores de eventos
│ ├── Mail/ # Clases Mailable para notificaciones por correo
│ ├── Models/ # Modelos Eloquent (ver detalle en Base de Datos)
│ ├── Modules/ # (Opcional, para módulos de aprovisionamiento/registradores)
│ │ ├── Provisioning/
│ │ │ ├── Cpanel.php
│ │ │ └── Plesk.php
│ │ └── DomainRegistrars/
│ │ └── Enom.php
│ ├── Notifications/ # Notificaciones de Laravel
│ ├── Policies/ # Políticas de autorización para controlar acceso
│ ├── Providers/
│ ├── Rules/ # Reglas de validación personalizadas
│ └── Services/ # Lógica de negocio más compleja, interacción con APIs externas
├── database/
│ ├── factories/
│ ├── migrations/ # Definiciones de la estructura de la BD
│ └── seeders/
├── resources/
│ ├── js/
│ │ ├── Components/ # Componentes Vue reutilizables
│ │ │ ├── Shared/ # Componentes comunes a varios layouts/secciones
│ │ │ ├── Admin/
│ │ │ ├── Client/
│ │ │ └── Reseller/
│ │ ├── Layouts/ # Layouts base de Inertia
│ │ │ ├── AppLayout.vue # Layout principal para usuarios autenticados
│ │ │ ├── AdminLayout.vue
│ │ │ ├── ClientLayout.vue
│ │ │ ├── ResellerLayout.vue
│ │ │ └── GuestLayout.vue # Layout para páginas públicas (login, registro)
│ │ ├── Pages/ # Componentes Vue que representan páginas completas (mapeados a rutas)
│ │ │ ├── Admin/
│ │ │ ├── Auth/
│ │ │ ├── Client/
│ │ │ └── Reseller/
│ │ ├── app.js # Punto de entrada de Vue e Inertia
│ │ └── bootstrap.js # Configuración inicial de JS (ej: Axios)
│ ├── views/
│ │ └── app.blade.php # Plantilla Blade raíz que carga Inertia y Vue
├── routes/
│ ├── web.php # Rutas principales (Admin, Client, Reseller, Auth)
│ ├── api.php # Rutas para API (si se expone una)
│ └── admin.php # (Opcional) Rutas específicas para admin agrupadas
│ └── reseller.php # (Opcional) Rutas específicas para reseller agrupadas
└── ... (otros directorios estándar de Laravel)

**3.1. Convenciones de Nombres:**

-   **Controladores:** PascalCase, terminan en `Controller` (ej: `UserController.php`)
-   **Modelos:** Singular, PascalCase (ej: `User.php`, `Product.php`)
-   **Componentes Vue (Vistas/Páginas):** PascalCase (ej: `UserList.vue`, `AdminDashboard.vue`)
-   **Archivos de Rutas:** `snake_case.php` (ej: `admin_routes.php` o agrupados como `admin.php`)
-   **Nombres de Rutas (named routes):** `snake_case`, con prefijos claros (ej: `admin.users.index`, `reseller.products.create`)

4. Base de Datos (Esquema Inicial Propuesto):

Este es el corazón del sistema. La clave para la funcionalidad de revendedores es el uso inteligente de `reseller_id` o un concepto similar de "tenant".

**Tabla: `users`**
**Propósito:** Almacena la información de todos los individuos que interactúan con el sistema (administradores, revendedores, clientes). Es la tabla central para la identificación y roles base.

| Campo               | Tipo                                                              | Descripción                                                                                                                                                                                                                                                             |
| ------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `id`                | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del usuario.                                                                                                                                                                                                                                        |
| `name`              | `VARCHAR(255)`                                                    | Nombre completo del usuario.                                                                                                                                                                                                                                            |
| `email`             | `VARCHAR(255) UNIQUE`                                             | Correo electrónico único del usuario.                                                                                                                                                                                                                                   |
| `email_verified_at` | `TIMESTAMP NULLABLE`                                              | Fecha y hora en que el correo electrónico del usuario fue verificado.                                                                                                                                                                                                   |
| `password`          | `VARCHAR(255)`                                                    | Contraseña hasheada del usuario.                                                                                                                                                                                                                                        |
| `role`              | `ENUM('admin', 'client', 'reseller')`                             | Rol principal del usuario en el sistema. Decidido mantener por ahora; Spatie se puede integrar después para permisos más granulares.                                                                                                                                    |
| `reseller_id`       | `BIGINT UNSIGNED NULLABLE INDEX`                                  | FK a `users.id`. Si `role` es 'client', indica el `id` del revendedor al que pertenece. `NULL` si es cliente directo de la plataforma. Si `role` es 'reseller', puede ser `NULL` o su propio `id` para auto-referencia de su "tenant". Si `role` es 'admin', es `NULL`. |
| `company_name`      | `VARCHAR(255) NULLABLE`                                           | Nombre de la empresa del usuario (si aplica).                                                                                                                                                                                                                           |
| `phone_number`      | `VARCHAR(255) NULLABLE`                                           | Número de teléfono del usuario.                                                                                                                                                                                                                                         |
| `address_line1`     | `VARCHAR(255) NULLABLE`                                           | Primera línea de la dirección del usuario.                                                                                                                                                                                                                              |
| `address_line2`     | `VARCHAR(255) NULLABLE`                                           | Segunda línea de la dirección del usuario.                                                                                                                                                                                                                              |
| `city`              | `VARCHAR(255) NULLABLE`                                           | Ciudad del usuario.                                                                                                                                                                                                                                                     |
| `state_province`    | `VARCHAR(255) NULLABLE`                                           | Estado o provincia del usuario.                                                                                                                                                                                                                                         |
| `postal_code`       | `VARCHAR(255) NULLABLE`                                           | Código postal del usuario.                                                                                                                                                                                                                                              |
| `country`      | `VARCHAR(255) NULLABLE`                                             | Código de país del usuario (ISO 3166-1 alpha-2).                                                                                                                                                                                                                        |
| `status`            | `ENUM('active', 'inactive', 'suspended') DEFAULT 'active'`        | Estado de la cuenta del usuario.                                                                                                                                                                                                                                        |
| `language_code`     | `VARCHAR(10) DEFAULT 'es' INDEX`                                  | Código de idioma preferido por el usuario (ej. 'es', 'en-US').                                                                                                                                                                                                          |
| `currency_code`     | `VARCHAR(3) DEFAULT 'USD' INDEX`                                  | Código de moneda preferida por el usuario (ISO 4217).                                                                                                                                                                                                                   |
| `last_login_at`     | `TIMESTAMP NULLABLE`                                              | Fecha y hora del último inicio de sesión del usuario.                                                                                                                                                                                                                   |
| `remember_token`    | `VARCHAR(100) NULLABLE`                                           | Token para la funcionalidad "recordarme".                                                                                                                                                                                                                               |
| `created_at`        | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                                                                                                                                                                                                  |
| `updated_at`        | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                                                                                                                                                                                                   |
| `deleted_at`        | `TIMESTAMP NULLABLE`                                              | Fecha y hora para el borrado lógico (Soft Deletes).                                                                                                                                                                                                                     |

**Tabla: `reseller_profiles`**
**Propósito:** Almacena configuraciones y personalizaciones específicas para los usuarios con rol de revendedor.

| Campo                   | Tipo                                                              | Descripción                                                                   |
| ----------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| `id`                    | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del perfil de revendedor.                                 |
| `user_id`               | `BIGINT UNSIGNED UNIQUE FK`                                       | Referencia al `users.id` del revendedor.                                      |
| `brand_name`            | `VARCHAR(255) NULLABLE`                                           | Nombre de la marca del revendedor para su panel y comunicaciones.             |
| `custom_domain`         | `VARCHAR(255) NULLABLE UNIQUE`                                    | Dominio personalizado que el revendedor puede usar para su panel (si aplica). |
| `logo_url`              | `VARCHAR(255) NULLABLE`                                           | URL del logo de la marca del revendedor.                                      |
| `support_email`         | `VARCHAR(255) NULLABLE`                                           | Correo electrónico de soporte específico del revendedor.                      |
| `terms_url`             | `VARCHAR(255) NULLABLE`                                           | URL a los términos y condiciones específicos del revendedor.                  |
| `allow_custom_products` | `BOOLEAN DEFAULT FALSE`                                           | Indica si el revendedor puede crear y vender sus propios productos.           |
| `created_at`            | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                        |
| `updated_at`            | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                         |

**Tabla: `products`**
**Propósito:** Catálogo de todos los servicios y artículos que se pueden vender en la plataforma (hosting, dominios, SSL, etc.).

| Campo                       | Tipo                                                                                                          | Descripción                                                                                                                             |
| --------------------------- | ------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `id`                        | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                                           | Identificador único del producto.                                                                                                       |
| `name`                      | `VARCHAR(255)`                                                                                                | Nombre del producto.                                                                                                                    |
| `slug`                      | `VARCHAR(255) UNIQUE`                                                                                         | Versión amigable para URL del nombre del producto.                                                                                      |
| `description`               | `TEXT NULLABLE`                                                                                               | Descripción detallada del producto.                                                                                                     |
| `type`                      | `ENUM('shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other') INDEX` | Tipo de producto, para lógica de aprovisionamiento o gestión específica.                                                                |
| `module_name`               | `VARCHAR(255) NULLABLE INDEX`                                                                                 | Nombre del módulo de software (interno o externo) para la automatización de este producto (ej: 'cpanel', 'plesk', 'custom_script').     |
| `owner_id`                  | `BIGINT UNSIGNED NULLABLE INDEX`                                                                              | FK a `users.id`. `NULL` si es un producto de la plataforma. ID del revendedor si es un producto personalizado creado por un revendedor. |
| `is_publicly_available`     | `BOOLEAN DEFAULT TRUE`                                                                                        | Indica si el producto es visible en la tienda/catálogo general.                                                                         |
| `is_resellable_by_default`  | `BOOLEAN DEFAULT TRUE`                                                                                        | Para productos de plataforma, indica si los revendedores pueden ofrecer este producto por defecto.                                      |
| `welcome_email_template_id` | `BIGINT UNSIGNED NULLABLE FK`                                                                                 | Referencia a `email_templates.id` para el correo de bienvenida de este producto.                                                        |
| `status`                    | `ENUM('active', 'inactive', 'hidden') DEFAULT 'active' INDEX`                                                 | Estado del producto (activo, inactivo, oculto).                                                                                         |
| `display_order`             | `INTEGER DEFAULT 0`                                                                                           | Orden de visualización del producto en listados.                                                                                        |
| `created_at`                | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                                         | Fecha y hora de creación del registro.                                                                                                  |
| `updated_at`                | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                                             | Fecha y hora de la última actualización del registro.                                                                                   |

**Tabla: `product_pricings`**
**Propósito:** Define los diferentes ciclos de facturación y precios para cada producto.

| Campo           | Tipo                                                                                                       | Descripción                                                     |
| --------------- | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| `id`            | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                                        | Identificador único del precio del producto.                    |
| `product_id`    | `BIGINT UNSIGNED FK`                                                                                       | Referencia a `products.id`.                                     |
| `billing_cycle` | `ENUM('monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially', 'one_time') INDEX` | Ciclo de facturación (mensual, anual, etc.).                    |
| `price`         | `DECIMAL(10, 2)`                                                                                           | Precio para este ciclo de facturación.                          |
| `setup_fee`     | `DECIMAL(10, 2) DEFAULT 0.00`                                                                              | Tarifa de configuración única para este ciclo (si aplica).      |
| `currency_code` | `VARCHAR(3) INDEX`                                                                                         | Código de moneda para este precio (ISO 4217, ej: 'USD', 'EUR'). |
| `is_active`     | `BOOLEAN DEFAULT TRUE`                                                                                     | Indica si esta opción de precio está activa y disponible.       |
| `created_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                                      | Fecha y hora de creación del registro.                          |
| `updated_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                                          | Fecha y hora de la última actualización del registro.           |

_Constraint: Índice único en (`product_id`, `billing_cycle`, `currency_code`)_

**Tabla: `configurable_option_groups`**
**Propósito:** Agrupa opciones configurables para los productos (Ej: "Sistema Operativo", "Ubicación del Servidor").

| Campo           | Tipo                                                              | Descripción                                                                               |
| --------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| `id`            | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del grupo de opciones configurables.                                  |
| `product_id`    | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `products.id` si el grupo es específico de un producto. `NULL` si es global. |
| `name`          | `VARCHAR(255)`                                                    | Nombre del grupo de opciones (ej: "Sistema Operativo").                                   |
| `description`   | `VARCHAR(255) NULLABLE`                                           | Descripción breve del grupo de opciones.                                                  |
| `display_order` | `INTEGER DEFAULT 0`                                               | Orden de visualización del grupo.                                                         |
| `created_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                    |
| `updated_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                     |

**Tabla: `configurable_options`**
**Propósito:** Define las opciones individuales dentro de un grupo configurable (Ej: "CentOS", "Ubuntu", "Dallas").

| Campo           | Tipo                                                              | Descripción                                                        |
| --------------- | ----------------------------------------------------------------- | ------------------------------------------------------------------ |
| `id`            | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único de la opción configurable.                     |
| `group_id`      | `BIGINT UNSIGNED FK`                                              | Referencia a `configurable_option_groups.id`.                      |
| `name`          | `VARCHAR(255)`                                                    | Nombre visible de la opción (ej: "CentOS 7").                      |
| `value`         | `VARCHAR(255) NULLABLE`                                           | Valor interno de la opción para aprovisionamiento (ej: "centos7"). |
| `display_order` | `INTEGER DEFAULT 0`                                               | Orden de visualización de la opción dentro del grupo.              |
| `created_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                             |
| `updated_at`    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.              |

**Tabla: `configurable_option_pricings`**
**Propósito:** Define los precios para cada opción configurable, vinculados a un ciclo de facturación del producto base.

| Campo                    | Tipo                                                              | Descripción                                                                            |
| ------------------------ | ----------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| `id`                     | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del precio de la opción configurable.                              |
| `configurable_option_id` | `BIGINT UNSIGNED FK`                                              | Referencia a `configurable_options.id`.                                                |
| `product_pricing_id`     | `BIGINT UNSIGNED FK`                                              | Referencia a `product_pricing.id` (vincula al ciclo de facturación del producto base). |
| `price`                  | `DECIMAL(10, 2)`                                                  | Precio adicional de la opción para el ciclo de facturación vinculado.                  |
| `setup_fee`              | `DECIMAL(10, 2) DEFAULT 0.00`                                     | Tarifa de configuración única para la opción (si aplica).                              |
| `created_at`             | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                 |
| `updated_at`             | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                  |

**Tabla: `client_services`**
**Propósito:** Almacena las instancias de productos o servicios que los clientes han contratado.

| Campo                | Tipo                                                                               | Descripción                                                                                       |
| -------------------- | ---------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `id`                 | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                | Identificador único del servicio del cliente.                                                     |
| `client_id`          | `BIGINT UNSIGNED FK`                                                               | Referencia al `users.id` del cliente que posee el servicio.                                       |
| `reseller_id`        | `BIGINT UNSIGNED NULLABLE INDEX FK`                                                | Referencia al `users.id` del revendedor que gestiona este servicio. `NULL` si es cliente directo. |
| `product_id`         | `BIGINT UNSIGNED FK`                                                               | Referencia al `products.id` del producto contratado.                                              |
| `product_pricing_id` | `BIGINT UNSIGNED FK`                                                               | Referencia al `product_pricing.id` que define el ciclo y precio actual del servicio.              |
| `domain_name`        | `VARCHAR(255) NULLABLE INDEX`                                                      | Dominio principal asociado al servicio (si aplica).                                               |
| `username`           | `VARCHAR(255) NULLABLE`                                                            | Nombre de usuario para el servicio (ej: usuario de cPanel).                                       |
| `password_encrypted` | `TEXT NULLABLE`                                                                    | Contraseña encriptada para el servicio (si aplica).                                               |
| `server_id`          | `BIGINT UNSIGNED NULLABLE FK`                                                      | Referencia al `servers.id` donde está aprovisionado el servicio.                                  |
| `status`             | `ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud') INDEX` | Estado actual del servicio.                                                                       |
| `registration_date`  | `DATE`                                                                             | Fecha de alta/registro inicial del servicio.                                                      |
| `next_due_date`      | `DATE INDEX`                                                                       | Fecha para la próxima factura de renovación del servicio.                                         |
| `termination_date`   | `DATE NULLABLE`                                                                    | Fecha en que el servicio fue o será terminado.                                                    |
| `billing_amount`     | `DECIMAL(10, 2)`                                                                   | Monto recurrente actual del servicio.                                                             |
| `notes`              | `TEXT NULLABLE`                                                                    | Notas administrativas o del cliente sobre el servicio.                                            |
| `created_at`         | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                              | Fecha y hora de creación del registro.                                                            |
| `updated_at`         | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                  | Fecha y hora de la última actualización del registro.                                             |

**Tabla: `client_service_configurable_options`**
**Propósito:** Vincula las opciones configurables seleccionadas por un cliente a un servicio específico.

| Campo                            | Tipo                                                              | Descripción                                                          |
| -------------------------------- | ----------------------------------------------------------------- | -------------------------------------------------------------------- |
| `id`                             | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único.                                                 |
| `client_service_id`              | `BIGINT UNSIGNED FK`                                              | Referencia a `client_services.id`.                                   |
| `configurable_option_id`         | `BIGINT UNSIGNED FK`                                              | Referencia a `configurable_options.id` seleccionada.                 |
| `configurable_option_pricing_id` | `BIGINT UNSIGNED FK`                                              | Referencia al `configurable_option_pricing.id` de la opción elegida. |
| `quantity`                       | `INTEGER DEFAULT 1`                                               | Cantidad de esta opción (si aplica).                                 |
| `created_at`                     | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                               |
| `updated_at`                     | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                |

**Tabla: `invoices`**
**Propósito:** Almacena las facturas generadas para los clientes.

| Campo             | Tipo                                                                              | Descripción                                                                             |
| ----------------- | --------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `id`              | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                               | Identificador único de la factura.                                                      |
| `client_id`       | `BIGINT UNSIGNED FK`                                                              | Referencia al `users.id` del cliente al que se emite la factura.                        |
| `reseller_id`     | `BIGINT UNSIGNED NULLABLE INDEX FK`                                               | Referencia al `users.id` del revendedor (si la factura es de un cliente de revendedor). |
| `invoice_number`  | `VARCHAR(255) UNIQUE`                                                             | Número de factura único y legible.                                                      |
| `issue_date`      | `DATE`                                                                            | Fecha de emisión de la factura.                                                         |
| `due_date`        | `DATE INDEX`                                                                      | Fecha de vencimiento de la factura.                                                     |
| `paid_date`       | `DATE NULLABLE`                                                                   | Fecha en que se pagó la factura.                                                        |
| `status`          | `ENUM('unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections') INDEX` | Estado actual de la factura.                                                            |
| `subtotal`        | `DECIMAL(10, 2)`                                                                  | Subtotal de la factura antes de impuestos.                                              |
| `tax1_name`       | `VARCHAR(50) NULLABLE`                                                            | Nombre del primer impuesto (ej: "IVA").                                                 |
| `tax1_rate`       | `DECIMAL(5, 2) NULLABLE`                                                          | Tasa del primer impuesto (ej: 21.00 para 21%).                                          |
| `tax1_amount`     | `DECIMAL(10, 2) NULLABLE`                                                         | Monto del primer impuesto.                                                              |
| `tax2_name`       | `VARCHAR(50) NULLABLE`                                                            | Nombre del segundo impuesto.                                                            |
| `tax2_rate`       | `DECIMAL(5, 2) NULLABLE`                                                          | Tasa del segundo impuesto.                                                              |
| `tax2_amount`     | `DECIMAL(10, 2) NULLABLE`                                                         | Monto del segundo impuesto.                                                             |
| `total_amount`    | `DECIMAL(10, 2)`                                                                  | Monto total de la factura (subtotal + impuestos).                                       |
| `currency_code`   | `VARCHAR(3)`                                                                      | Código de moneda de la factura (ISO 4217).                                              |
| `notes_to_client` | `TEXT NULLABLE`                                                                   | Notas visibles para el cliente en la factura.                                           |
| `admin_notes`     | `TEXT NULLABLE`                                                                   | Notas internas para administración sobre la factura.                                    |
| `created_at`      | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                             | Fecha y hora de creación del registro.                                                  |
| `updated_at`      | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                 | Fecha y hora de la última actualización del registro.                                   |

**Tabla: `orders`**
**Propósito:** Registra las órdenes de compra realizadas por los clientes.

| Campo                  | Tipo                                                                                    | Descripción                                                                           |
| ---------------------- | --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| `id`                   | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                     | Identificador único de la orden.                                                      |
| `client_id`            | `BIGINT UNSIGNED FK`                                                                    | Referencia al `users.id` del cliente que realizó la orden.                            |
| `reseller_id`          | `BIGINT UNSIGNED NULLABLE INDEX FK`                                                     | Referencia al `users.id` del revendedor (si la orden es de un cliente de revendedor). |
| `order_number`         | `VARCHAR(255) UNIQUE`                                                                   | Número de orden único y legible.                                                      |
| `invoice_id`           | `BIGINT UNSIGNED NULLABLE UNIQUE FK`                                                    | Referencia a `invoices.id` si se generó una factura para esta orden.                  |
| `order_date`           | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                   | Fecha y hora en que se realizó la orden.                                              |
| `status`               | `ENUM('pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled') INDEX` | Estado actual de la orden.                                                            |
| `total_amount`         | `DECIMAL(10, 2)`                                                                        | Monto total de la orden.                                                              |
| `currency_code`        | `VARCHAR(3)`                                                                            | Código de moneda de la orden (ISO 4217).                                              |
| `payment_gateway_slug` | `VARCHAR(255) NULLABLE INDEX`                                                           | Identificador de la pasarela de pago utilizada (ej: 'paypal', 'stripe').              |
| `ip_address`           | `VARCHAR(45) NULLABLE`                                                                  | Dirección IP desde la que se realizó la orden.                                        |
| `notes`                | `TEXT NULLABLE`                                                                         | Notas adicionales sobre la orden.                                                     |
| `created_at`           | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                   | Fecha y hora de creación del registro.                                                |
| `updated_at`           | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                       | Fecha y hora de la última actualización del registro.                                 |

**Tabla: `order_items`**
**Propósito:** Detalla los productos o servicios incluidos en cada orden.

| Campo                       | Tipo                                                                                                                | Descripción                                                                          |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| `id`                        | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                                                 | Identificador único del ítem de la orden.                                            |
| `order_id`                  | `BIGINT UNSIGNED FK`                                                                                                | Referencia a `orders.id`.                                                            |
| `product_id`                | `BIGINT UNSIGNED FK`                                                                                                | Referencia a `products.id`.                                                          |
| `product_pricing_id`        | `BIGINT UNSIGNED FK`                                                                                                | Referencia a `product_pricing.id` del ciclo de facturación elegido.                  |
| `item_type`                 | `ENUM('product', 'addon', 'domain_registration', 'domain_renewal', 'domain_transfer', 'configurable_option') INDEX` | Tipo de ítem.                                                                        |
| `description`               | `VARCHAR(255)`                                                                                                      | Descripción del ítem (ej: "Web Hosting - Plan Básico (Mensual)").                    |
| `quantity`                  | `INTEGER DEFAULT 1`                                                                                                 | Cantidad del ítem.                                                                   |
| `unit_price`                | `DECIMAL(10, 2)`                                                                                                    | Precio unitario del ítem.                                                            |
| `setup_fee`                 | `DECIMAL(10, 2) DEFAULT 0.00`                                                                                       | Tarifa de configuración para este ítem (si aplica).                                  |
| `total_price`               | `DECIMAL(10, 2)`                                                                                                    | Precio total del ítem (unit_price \* quantity + setup_fee).                          |
| `domain_name`               | `VARCHAR(255) NULLABLE`                                                                                             | Nombre del dominio si el ítem es un dominio.                                         |
| `registration_period_years` | `INTEGER NULLABLE`                                                                                                  | Período de registro en años para dominios.                                           |
| `client_service_id`         | `BIGINT UNSIGNED NULLABLE FK`                                                                                       | Referencia a `client_services.id` si este ítem resultó en un servicio aprovisionado. |
| `created_at`                | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                                               | Fecha y hora de creación del registro.                                               |
| `updated_at`                | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                                                   | Fecha y hora de la última actualización del registro.                                |

**Tabla: `invoice_items`**
**Propósito:** Detalla los conceptos facturados en cada factura.

| Campo               | Tipo                                                              | Descripción                                                                |
| ------------------- | ----------------------------------------------------------------- | -------------------------------------------------------------------------- |
| `id`                | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del ítem de la factura.                                |
| `invoice_id`        | `BIGINT UNSIGNED FK`                                              | Referencia a `invoices.id`.                                                |
| `client_service_id` | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `client_services.id` (para ítems de renovación de servicios). |
| `description`       | `VARCHAR(255)`                                                    | Descripción del concepto facturado.                                        |
| `quantity`          | `INTEGER DEFAULT 1`                                               | Cantidad del concepto.                                                     |
| `unit_price`        | `DECIMAL(10, 2)`                                                  | Precio unitario del concepto.                                              |
| `total_price`       | `DECIMAL(10, 2)`                                                  | Precio total del concepto (unit_price \* quantity).                        |
| `taxable`           | `BOOLEAN DEFAULT TRUE`                                            | Indica si este ítem está sujeto a impuestos.                               |
| `created_at`        | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                     |
| `updated_at`        | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                      |

**Tabla: `transactions`**
**Propósito:** Registra todos los movimientos de dinero (pagos, reembolsos, etc.).

| Campo                    | Tipo                                                                           | Descripción                                                                     |
| ------------------------ | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------- |
| `id`                     | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                            | Identificador único de la transacción.                                          |
| `invoice_id`             | `BIGINT UNSIGNED NULLABLE FK`                                                  | Referencia a `invoices.id` (si la transacción está asociada a una factura).     |
| `client_id`              | `BIGINT UNSIGNED FK`                                                           | Referencia al `users.id` del cliente.                                           |
| `reseller_id`            | `BIGINT UNSIGNED NULLABLE INDEX FK`                                            | Referencia al `users.id` del revendedor (si aplica).                            |
| `gateway_slug`           | `VARCHAR(255) INDEX`                                                           | Identificador de la pasarela de pago (ej: 'paypal', 'stripe', 'manual_credit'). |
| `gateway_transaction_id` | `VARCHAR(255) NULLABLE INDEX`                                                  | ID de la transacción en la pasarela de pago externa.                            |
| `type`                   | `ENUM('payment', 'refund', 'chargeback', 'credit_added', 'credit_used') INDEX` | Tipo de transacción.                                                            |
| `amount`                 | `DECIMAL(10, 2)`                                                               | Monto de la transacción.                                                        |
| `currency_code`          | `VARCHAR(3)`                                                                   | Código de moneda de la transacción (ISO 4217).                                  |
| `status`                 | `ENUM('pending', 'completed', 'failed', 'reversed') INDEX`                     | Estado de la transacción.                                                       |
| `fees_amount`            | `DECIMAL(10, 2) NULLABLE`                                                      | Comisiones de la pasarela de pago (si aplica).                                  |
| `description`            | `VARCHAR(255) NULLABLE`                                                        | Descripción breve de la transacción.                                            |
| `transaction_date`       | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                          | Fecha y hora de la transacción.                                                 |
| `created_at`             | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                          | Fecha y hora de creación del registro.                                          |
| `updated_at`             | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`              | Fecha y hora de la última actualización del registro.                           |

**Tabla: `support_departments`**
**Propósito:** Define los departamentos de soporte a los que se pueden dirigir los tickets.

| Campo                 | Tipo                                                              | Descripción                                                                                         |
| --------------------- | ----------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| `id`                  | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del departamento.                                                               |
| `name`                | `VARCHAR(255)`                                                    | Nombre del departamento (ej: "Soporte Técnico", "Ventas").                                          |
| `email_address`       | `VARCHAR(255) NULLABLE UNIQUE`                                    | Dirección de correo para crear tickets automáticamente en este departamento.                        |
| `is_public`           | `BOOLEAN DEFAULT TRUE`                                            | Indica si el departamento es visible para los clientes.                                             |
| `reseller_id`         | `BIGINT UNSIGNED NULLABLE INDEX FK`                               | `NULL` para departamentos globales. ID del revendedor para departamentos específicos de revendedor. |
| `auto_assign_user_id` | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `users.id` del agente al que se asignarán automáticamente los tickets.                 |
| `display_order`       | `INTEGER DEFAULT 0`                                               | Orden de visualización del departamento.                                                            |
| `created_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                              |
| `updated_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                               |

**Tabla: `support_tickets`**
**Propósito:** Almacena los tickets de soporte creados por los clientes.

| Campo                 | Tipo                                                                                    | Descripción                                                                            |
| --------------------- | --------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| `id`                  | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                     | Identificador único del ticket.                                                        |
| `ticket_number`       | `VARCHAR(255) UNIQUE`                                                                   | Número de ticket único y legible.                                                      |
| `client_id`           | `BIGINT UNSIGNED FK`                                                                    | Referencia al `users.id` del cliente que creó el ticket.                               |
| `reseller_id`         | `BIGINT UNSIGNED NULLABLE INDEX FK`                                                     | Referencia al `users.id` del revendedor (si el ticket es de un cliente de revendedor). |
| `department_id`       | `BIGINT UNSIGNED FK`                                                                    | Referencia a `support_departments.id`.                                                 |
| `assigned_to_user_id` | `BIGINT UNSIGNED NULLABLE FK`                                                           | Referencia al `users.id` del agente asignado al ticket.                                |
| `subject`             | `VARCHAR(255)`                                                                          | Asunto del ticket.                                                                     |
| `status`              | `ENUM('open', 'client_reply', 'staff_reply', 'on_hold', 'in_progress', 'closed') INDEX` | Estado actual del ticket.                                                              |
| `priority`            | `ENUM('low', 'medium', 'high', 'critical') INDEX`                                       | Prioridad del ticket.                                                                  |
| `last_reply_at`       | `TIMESTAMP NULLABLE`                                                                    | Fecha y hora de la última respuesta.                                                   |
| `last_replier_name`   | `VARCHAR(255) NULLABLE`                                                                 | Nombre de la persona que realizó la última respuesta.                                  |
| `created_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                   | Fecha y hora de creación del registro.                                                 |
| `updated_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                       | Fecha y hora de la última actualización del registro.                                  |

**Tabla: `support_ticket_replies`**
**Propósito:** Almacena las respuestas (de clientes o staff) a los tickets de soporte.

| Campo            | Tipo                                                              | Descripción                                                                                   |
| ---------------- | ----------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| `id`             | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único de la respuesta.                                                          |
| `ticket_id`      | `BIGINT UNSIGNED FK`                                              | Referencia a `support_tickets.id`.                                                            |
| `user_id`        | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia al `users.id` de quien respondió. `NULL` si es respuesta automática o del sistema. |
| `message`        | `TEXT`                                                            | Contenido del mensaje de la respuesta.                                                        |
| `ip_address`     | `VARCHAR(45) NULLABLE`                                            | Dirección IP desde la que se envió la respuesta.                                              |
| `is_staff_reply` | `BOOLEAN DEFAULT FALSE`                                           | Indica si la respuesta fue realizada por un miembro del staff.                                |
| `created_at`     | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                        |
| `updated_at`     | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                         |

**Tabla: `support_ticket_attachments`**
**Propósito:** Almacena los archivos adjuntos a los tickets de soporte o a sus respuestas.

| Campo                | Tipo                                                              | Descripción                                                                       |
| -------------------- | ----------------------------------------------------------------- | --------------------------------------------------------------------------------- |
| `id`                 | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del adjunto.                                                  |
| `reply_id`           | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `support_ticket_replies.id` si el adjunto pertenece a una respuesta. |
| `ticket_id`          | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `support_tickets.id` si el adjunto se subió al crear el ticket.      |
| `file_name_original` | `VARCHAR(255)`                                                    | Nombre original del archivo.                                                      |
| `file_path_stored`   | `VARCHAR(255)`                                                    | Ruta donde se almacena el archivo en el servidor.                                 |
| `mime_type`          | `VARCHAR(255)`                                                    | Tipo MIME del archivo.                                                            |
| `file_size_bytes`    | `INTEGER UNSIGNED`                                                | Tamaño del archivo en bytes.                                                      |
| `created_at`         | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                            |
| `updated_at`         | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                             |

**Tabla: `domains`**
**Propósito:** Gestiona la información de los dominios registrados o transferidos a través de la plataforma.

| Campo                   | Tipo                                                                                                | Descripción                                                                              |
| ----------------------- | --------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `id`                    | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                                                 | Identificador único del dominio.                                                         |
| `client_id`             | `BIGINT UNSIGNED FK`                                                                                | Referencia al `users.id` del cliente propietario del dominio.                            |
| `reseller_id`           | `BIGINT UNSIGNED NULLABLE INDEX FK`                                                                 | Referencia al `users.id` del revendedor (si el dominio es de un cliente de revendedor).  |
| `order_id`              | `BIGINT UNSIGNED NULLABLE FK`                                                                       | Referencia a `orders.id` de la orden de compra/renovación del dominio.                   |
| `client_service_id`     | `BIGINT UNSIGNED NULLABLE UNIQUE FK`                                                                | Referencia a `client_services.id` si el dominio se gestiona como un servicio facturable. |
| `domain_name`           | `VARCHAR(255) UNIQUE`                                                                               | Nombre del dominio (ej: "example.com").                                                  |
| `registrar_module_slug` | `VARCHAR(255) NULLABLE INDEX`                                                                       | Identificador del módulo de registrador utilizado (ej: 'enom', 'namecheap').             |
| `registration_date`     | `DATE`                                                                                              | Fecha de registro del dominio.                                                           |
| `expiry_date`           | `DATE INDEX`                                                                                        | Fecha de expiración del dominio.                                                         |
| `next_due_date`         | `DATE INDEX`                                                                                        | Próxima fecha de pago para la renovación del dominio.                                    |
| `status`                | `ENUM('pending_registration', 'pending_transfer', 'active', 'expired', 'cancelled', 'fraud') INDEX` | Estado actual del dominio.                                                               |
| `auto_renew_enabled`    | `BOOLEAN DEFAULT FALSE`                                                                             | Indica si la renovación automática está habilitada para el dominio.                      |
| `id_protection_enabled` | `BOOLEAN DEFAULT FALSE`                                                                             | Indica si la protección de ID (privacidad WHOIS) está habilitada.                        |
| `epp_code_encrypted`    | `TEXT NULLABLE`                                                                                     | Código EPP (Auth Code) encriptado para transferencias de dominio.                        |
| `nameserver1`           | `VARCHAR(255) NULLABLE`                                                                             | Nameserver 1.                                                                            |
| `nameserver2`           | `VARCHAR(255) NULLABLE`                                                                             | Nameserver 2.                                                                            |
| `nameserver3`           | `VARCHAR(255) NULLABLE`                                                                             | Nameserver 3 (opcional).                                                                 |
| `nameserver4`           | `VARCHAR(255) NULLABLE`                                                                             | Nameserver 4 (opcional).                                                                 |
| `admin_notes`           | `TEXT NULLABLE`                                                                                     | Notas administrativas sobre el dominio.                                                  |
| `created_at`            | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                                               | Fecha y hora de creación del registro.                                                   |
| `updated_at`            | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`                                   | Fecha y hora de la última actualización del registro.                                    |

**Tabla: `servers`**
**Propósito:** Almacena la información de los servidores físicos o virtuales donde se aprovisionan los servicios de hosting.

| Campo                           | Tipo                                                              | Descripción                                                                                                                                |
| ------------------------------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `id`                            | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único del servidor.                                                                                                          |
| `name`                          | `VARCHAR(255) UNIQUE`                                             | Nombre descriptivo del servidor.                                                                                                           |
| `hostname_or_ip`                | `VARCHAR(255)`                                                    | Hostname o dirección IP principal del servidor.                                                                                            |
| `reseller_id`                   | `BIGINT UNSIGNED NULLABLE INDEX FK`                               | FK a `users.id`. `NULL` si el servidor pertenece a la plataforma. ID del revendedor si es un servidor gestionado/propiedad del revendedor. |
| `server_group_id`               | `BIGINT UNSIGNED NULLABLE FK`                                     | Referencia a `server_groups.id` al que pertenece el servidor.                                                                              |
| `module_slug`                   | `VARCHAR(255) INDEX`                                              | Identificador del módulo de servidor (ej: 'cpanel', 'plesk', 'directadmin').                                                               |
| `api_username`                  | `VARCHAR(255) NULLABLE`                                           | Nombre de usuario para la API del servidor.                                                                                                |
| `api_password_or_key_encrypted` | `TEXT NULLABLE`                                                   | Contraseña o clave API encriptada para el servidor.                                                                                        |
| `api_port`                      | `INTEGER UNSIGNED NULLABLE`                                       | Puerto para la conexión API del servidor.                                                                                                  |
| `api_use_ssl`                   | `BOOLEAN DEFAULT TRUE`                                            | Indica si se debe usar SSL para la conexión API.                                                                                           |
| `status_url`                    | `VARCHAR(255) NULLABLE`                                           | URL para verificar el estado del servidor (opcional).                                                                                      |
| `max_accounts`                  | `INTEGER UNSIGNED NULLABLE`                                       | Número máximo de cuentas que puede alojar el servidor.                                                                                     |
| `current_accounts_count`        | `INTEGER UNSIGNED DEFAULT 0`                                      | Número actual de cuentas aprovisionadas en el servidor.                                                                                    |
| `is_active`                     | `BOOLEAN DEFAULT TRUE INDEX`                                      | Indica si el servidor está activo y disponible para aprovisionamiento.                                                                     |
| `notes`                         | `TEXT NULLABLE`                                                   | Notas administrativas sobre el servidor.                                                                                                   |
| `created_at`                    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                                                                     |
| `updated_at`                    | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                                                                      |

**Tabla: `server_groups`**
**Propósito:** Agrupa servidores para la asignación automática de nuevas cuentas de hosting.

| Campo        | Tipo                                                                     | Descripción                                                                                                             |
| ------------ | ------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| `id`         | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                      | Identificador único del grupo de servidores.                                                                            |
| `name`       | `VARCHAR(255) UNIQUE`                                                    | Nombre del grupo de servidores.                                                                                         |
| `fill_type`  | `ENUM('fill_sequentially', 'fill_until_full_then_next', 'random') INDEX` | Estrategia para asignar nuevas cuentas al grupo (llenar secuencialmente, hasta llenar y pasar al siguiente, aleatorio). |
| `created_at` | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                                    | Fecha y hora de creación del registro.                                                                                  |
| `updated_at` | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`        | Fecha y hora de la última actualización del registro.                                                                   |

**Tabla: `promotions`**
**Propósito:** Gestiona descuentos, cupones y ofertas especiales aplicables a productos u órdenes.

| Campo                 | Tipo                                                              | Descripción                                                                                         |
| --------------------- | ----------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| `id`                  | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único de la promoción.                                                                |
| `name`                | `VARCHAR(255)`                                                    | Nombre interno/descriptivo de la promoción.                                                         |
| `code`                | `VARCHAR(50) UNIQUE NULLABLE`                                     | Código que el cliente puede ingresar para aplicar la promoción. `NULL` si es automática.            |
| `description`         | `TEXT NULLABLE`                                                   | Descripción de la promoción visible para el cliente.                                                |
| `type`                | `ENUM('percentage', 'fixed_amount')`                              | Tipo de descuento: porcentual o un monto fijo.                                                      |
| `value`               | `DECIMAL(10, 2)`                                                  | Valor del descuento (ej: 20.00 para 20% o $20.00).                                                  |
| `applies_to`          | `ENUM('order', 'product', 'category')`                            | A qué se aplica el descuento (total de la orden, productos específicos, categorías de productos).   |
| `product_ids`         | `JSON NULLABLE`                                                   | Si `applies_to` es 'product', lista de IDs de `products` a los que aplica.                          |
| `category_ids`        | `JSON NULLABLE`                                                   | Si `applies_to` es 'category', lista de IDs de categorías de productos.                             |
| `min_order_amount`    | `DECIMAL(10, 2) NULLABLE`                                         | Monto mínimo de la orden para que la promoción sea aplicable.                                       |
| `max_uses`            | `INTEGER UNSIGNED NULLABLE`                                       | Número máximo de veces que esta promoción puede ser usada en total.                                 |
| `max_uses_per_client` | `INTEGER UNSIGNED NULLABLE`                                       | Número máximo de veces que un cliente puede usar esta promoción.                                    |
| `current_uses`        | `INTEGER UNSIGNED DEFAULT 0`                                      | Contador de cuántas veces se ha usado la promoción.                                                 |
| `start_date`          | `DATETIME NULLABLE`                                               | Fecha y hora de inicio de validez de la promoción.                                                  |
| `end_date`            | `DATETIME NULLABLE`                                               | Fecha y hora de fin de validez de la promoción.                                                     |
| `requires_code`       | `BOOLEAN DEFAULT TRUE`                                            | Indica si se necesita ingresar un código para aplicar la promoción.                                 |
| `is_active`           | `BOOLEAN DEFAULT TRUE INDEX`                                      | Indica si la promoción está activa y puede ser utilizada.                                           |
| `reseller_id`         | `BIGINT UNSIGNED NULLABLE INDEX FK`                               | `NULL` si es una promoción de la plataforma. ID del revendedor si la promoción es específica de él. |
| `created_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                              |
| `updated_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                               |

_Nota: Podría ser necesaria una tabla pivote `order_promotions` para rastrear qué promociones se aplicaron a qué órdenes y el monto descontado._

**Tabla: `email_templates`**
**Propósito:** Almacena las plantillas de correo electrónico utilizadas por el sistema para diversas notificaciones.

| Campo                         | Tipo                                                               | Descripción                                                                                             |
| ----------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------- |
| `id`                          | `BIGINT UNSIGNED AUTO_INCREMENT PK`                                | Identificador único de la plantilla de correo.                                                          |
| `name`                        | `VARCHAR(255) UNIQUE`                                              | Nombre descriptivo de la plantilla (ej: "Bienvenida Hosting Compartido").                               |
| `slug`                        | `VARCHAR(255) UNIQUE INDEX`                                        | Identificador único para uso interno (ej: 'welcome.shared_hosting').                                    |
| `type`                        | `ENUM('general', 'product', 'support', 'invoice', 'domain') INDEX` | Tipo de plantilla para categorización.                                                                  |
| `subject`                     | `VARCHAR(255)`                                                     | Asunto del correo electrónico.                                                                          |
| `body_html`                   | `TEXT`                                                             | Contenido HTML del correo electrónico.                                                                  |
| `body_text`                   | `TEXT NULLABLE`                                                    | Contenido de texto plano del correo electrónico (alternativa).                                          |
| `language_code`               | `VARCHAR(10) DEFAULT 'es' INDEX`                                   | Código de idioma de la plantilla (ej: 'es', 'en-US').                                                   |
| `is_customizable_by_reseller` | `BOOLEAN DEFAULT FALSE`                                            | Indica si los revendedores pueden personalizar esta plantilla.                                          |
| `reseller_id`                 | `BIGINT UNSIGNED NULLABLE INDEX FK`                                | `NULL` para plantillas globales. ID del revendedor si es una plantilla personalizada por un revendedor. |
| `created_at`                  | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                              | Fecha y hora de creación del registro.                                                                  |
| `updated_at`                  | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`  | Fecha y hora de la última actualización del registro.                                                   |

**Tabla: `activity_logs`**
**Propósito:** Registra acciones importantes realizadas en el sistema para auditoría.

| Campo                 | Tipo                                  | Descripción                                                                                     |
| --------------------- | ------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `id`                  | `BIGINT UNSIGNED AUTO_INCREMENT PK`   | Identificador único del registro de actividad.                                                  |
| `user_id`             | `BIGINT UNSIGNED NULLABLE FK`         | Referencia al `users.id` del usuario que realizó la acción. `NULL` si la acción es del sistema. |
| `reseller_context_id` | `BIGINT UNSIGNED NULLABLE INDEX FK`   | Referencia al `users.id` del revendedor en cuyo contexto se realizó la acción (si aplica).      |
| `loggable_type`       | `VARCHAR(255) NULLABLE INDEX`         | Nombre de la clase del modelo relacionado con el log (polimórfico).                             |
| `loggable_id`         | `BIGINT UNSIGNED NULLABLE INDEX`      | ID del modelo relacionado con el log (polimórfico).                                             |
| `action`              | `VARCHAR(255) INDEX`                  | Descripción de la acción realizada (ej: 'created_client', 'updated_service_status').            |
| `description`         | `TEXT`                                | Detalles adicionales de la acción.                                                              |
| `ip_address`          | `VARCHAR(45) NULLABLE`                | Dirección IP desde la que se realizó la acción.                                                 |
| `user_agent`          | `TEXT NULLABLE`                       | User agent del navegador o cliente que realizó la acción.                                       |
| `created_at`          | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | Fecha y hora en que se registró la actividad.                                                   |

**Tabla: `settings`**
**Propósito:** Almacén flexible de configuraciones clave-valor para la plataforma y para cada revendedor.

| Campo          | Tipo                                                              | Descripción                                                                                                              |
| -------------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `id`           | `BIGINT UNSIGNED AUTO_INCREMENT PK`                               | Identificador único de la configuración.                                                                                 |
| `group_slug`   | `VARCHAR(255) DEFAULT 'general' INDEX`                            | Grupo al que pertenece la configuración (ej: 'general', 'billing', 'mail').                                              |
| `key`          | `VARCHAR(255) INDEX`                                              | Clave única de la configuración dentro de su grupo y contexto de revendedor.                                             |
| `value`        | `TEXT NULLABLE`                                                   | Valor de la configuración.                                                                                               |
| `is_encrypted` | `BOOLEAN DEFAULT FALSE`                                           | Indica si el valor está encriptado en la base de datos.                                                                  |
| `reseller_id`  | `BIGINT UNSIGNED NULLABLE INDEX FK`                               | `NULL` para configuraciones globales de la plataforma. ID del revendedor para configuraciones específicas de revendedor. |
| `created_at`   | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`                             | Fecha y hora de creación del registro.                                                                                   |
| `updated_at`   | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Fecha y hora de la última actualización del registro.                                                                    |

_Constraint: Índice único en (`group_slug`, `key`, `reseller_id`)_

Consideraciones Clave para Multi-Tenancy (Revendedores):

reseller_id en Tablas Relevantes: Es la piedra angular. Filtra datos para que un revendedor solo vea y gestione lo suyo.
Productos de Plataforma vs. Productos de Revendedor: La tabla products con owner_id permite esto.
Configuraciones Específicas de Revendedor: reseller_profiles y settings con reseller_id.
Políticas de Acceso (Laravel Policies): Serán cruciales para asegurar que un usuario (admin, cliente, revendedor) solo pueda realizar las acciones permitidas sobre los recursos correctos.
Rutas y Controladores: La estructura de directorios Admin/, Client/, Reseller/ ayudará a organizar la lógica. Se pueden usar prefijos de ruta o subdominios (más complejo) para separar los paneles. Dado que "usarán la misma web", los prefijos de ruta (/admin, /reseller-panel, /client-area) son lo más sencillo.
