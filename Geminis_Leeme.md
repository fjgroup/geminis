Geminis - Documentación de la Estructura

1. Introducción y Objetivos del Sistema

Este documento describe la arquitectura de la base de datos y la estructura general del proyecto "Geminis", una plataforma de gestión de hosting y automatización de clientes con capacidades multi-tenant para revendedores.

Objetivo Principal: Crear un sistema robusto, escalable y fácil de mantener que permita: * Administradores de Plataforma: Gestionar toda la infraestructura, productos globales, clientes directos, revendedores y configuraciones del sistema. * Revendedores: Utilizar la misma plataforma para gestionar sus propios clientes, productos (personalizados o revendidos de la plataforma), facturación, soporte y marca, operando de forma independiente dentro del sistema. * Clientes Finales: Contratar y gestionar sus servicios (hosting, dominios, etc.), ver facturas, pagar y obtener soporte, ya sea como clientes directos de la plataforma o de un revendedor.

2. Filosofía de Diseño

Modularidad: Aunque monolítico inicialmente (Laravel), se busca una separación lógica de componentes (Admin, Reseller, Client) para facilitar el desarrollo y futuras refactorizaciones.
Escalabilidad: Diseño de base de datos que soporte un crecimiento en usuarios, revendedores y servicios. Uso de colas para tareas pesadas.
Seguridad: Encriptación de datos sensibles, políticas de acceso estrictas, y seguimiento de buenas prácticas.
Experiencia de Usuario (UX): Interfaces claras y eficientes para cada tipo de usuario, potenciadas por Vue.js e Inertia.js.
Automatización: Integración con módulos de aprovisionamiento y registradores para automatizar tareas comunes.
3. Documentación Detallada de Tablas y Campos

(Aquí se expandiría cada tabla definida en "Gemenis_Estructura.md" con una explicación de su propósito y el de sus campos más importantes. Por ejemplo:)

Tabla: users
Propósito: Almacena la información de todos los individuos que interactúan con el sistema. Es la tabla central para la identificación y roles.
Campos Clave y su Significado:
role: Determina el tipo de acceso y funcionalidades disponibles ('admin', 'client', 'reseller'). Se mantiene para una identificación rápida del tipo de usuario principal; Spatie puede integrarse después para una gestión de permisos más granular.
reseller_id: Crucial para la lógica multi-tenant.
Para un 'client', si no es NULL, indica que este cliente pertenece al revendedor con ese id.
Para un 'reseller', puede ser NULL o su propio id para identificar su "espacio" o "tenant".
Los 'admin' siempre tendrán reseller_id = NULL.
status: Controla si la cuenta del usuario está activa, inactiva o suspendida.
language_code, currency_code: Preferencias del usuario para la interfaz y transacciones.

Tabla: reseller_profiles
Propósito: Extiende la tabla users para almacenar configuraciones y personalizaciones específicas de los revendedores, como su marca, dominio personalizado (si se ofrece), y permisos especiales.
Campos Clave y su Significado:
user_id: Enlace directo al registro del revendedor en la tabla users.
brand_name, logo_url: Permiten al revendedor personalizar la apariencia de su panel y comunicaciones.
allow_custom_products: Define si el revendedor puede crear sus propios productos además de revender los de la plataforma.

Tabla: products
Propósito: Catálogo de todos los servicios y artículos que se pueden vender en la plataforma.
Campos Clave y su Significado:
type: Clasifica el producto (ej: 'shared_hosting', 'domain_registration') para aplicar lógica de aprovisionamiento o gestión específica.
module_name: Indica qué módulo de software (interno o externo) se encarga de la automatización de este producto (ej: 'cpanel' para crear cuentas de hosting).
owner_id:
Si es NULL, es un producto ofrecido directamente por la plataforma.
Si contiene un user_id de un revendedor, es un producto personalizado creado y gestionado por ese revendedor (si reseller_profiles.allow_custom_products es true para él).
is_resellable_by_default: Para productos de plataforma, indica si los revendedores pueden ofrecerlos por defecto.

Tabla: client_services
Propósito: Representa cada instancia activa de un producto o servicio que un cliente ha contratado. Es el núcleo de la gestión de "suscripciones".
Campos Clave y su Significado:
client_id: El cliente que posee el servicio.
reseller_id: Si no es NULL, indica que este servicio es gestionado por ese revendedor (y el client_id es un cliente de ese revendedor).
product_id, product_pricing_id: Definen qué producto y con qué ciclo de facturación se contrató.
status: Estado actual del servicio (activo, suspendido, etc.), crucial para la automatización y facturación.
next_due_date: Fecha para la próxima factura de renovación.

Tabla: orders e invoices
Propósito: orders registra la intención de compra inicial. invoices representa la obligación de pago formal generada a partir de una orden o para renovaciones.
Campos Clave y su Significado:
Ambas tablas contienen client_id y reseller_id para mantener el contexto del tenant.
orders.invoice_id: Vincula una orden a su factura correspondiente.
status en ambas tablas rastrea el ciclo de vida de la orden/factura.

Tabla: support_tickets
Propósito: Gestiona las solicitudes de soporte de los clientes.
Campos Clave y su Significado:
client_id y reseller_id: Aseguran que los tickets se asocien correctamente y sean visibles solo por las partes relevantes (cliente, su revendedor, o administradores de plataforma).
department_id: Permite categorizar y dirigir tickets. Los departamentos pueden ser globales o específicos de un revendedor.

Tabla: settings
Propósito: Almacén flexible de configuraciones clave-valor para la plataforma y para cada revendedor.
Campos Clave y su Significado:
reseller_id: Si es NULL, es una configuración global de la plataforma. Si tiene un user_id de un revendedor, es una configuración específica para ese revendedor, permitiéndole sobrescribir o definir valores propios (ej: configuración de pasarela de pago específica del revendedor).

Tabla: promotions
Propósito: Gestiona descuentos, cupones y ofertas especiales aplicables a productos, órdenes o categorías.
Campos Clave y su Significado:
code: Código único que los clientes pueden usar para aplicar la promoción (si no es automática).
type y value: Definen si el descuento es un porcentaje o un monto fijo, y el valor correspondiente.
applies_to: Especifica si la promoción se aplica al total de la orden, a productos específicos, o a categorías de productos.
start_date, end_date: Definen el período de validez de la promoción.
reseller_id: Permite que las promociones sean globales (NULL) o específicas de un revendedor, dándoles la capacidad de crear sus propias ofertas.

Tabla: servers
Propósito: Almacena información de los servidores físicos o virtuales donde se aprovisionan los servicios de hosting.
Campos Clave y su Significado:
hostname_or_ip: Identificador del servidor.
module_slug: Indica el tipo de servidor o panel de control (ej: 'cpanel', 'plesk') para la automatización.
reseller_id: Si no es NULL, indica que el servidor es gestionado o propiedad de un revendedor específico, permitiéndoles usar su propia infraestructura para sus clientes.

Tabla: servers
Propósito: Almacena información de los servidores físicos o virtuales donde se aprovisionan los servicios de hosting.
Campos Clave y su Significado:
hostname_or_ip: Identificador del servidor.
module_slug: Indica el tipo de servidor o panel de control (ej: 'cpanel', 'plesk') para la automatización.
reseller_id: Si no es NULL, indica que el servidor es gestionado o propiedad de un revendedor específico, permitiéndoles usar su propia infraestructura para sus clientes.

(Se continuaría esta documentación para todas las tablas importantes, explicando su rol en el ecosistema y cómo interactúan, especialmente en el contexto multi-tenant).

4. Flujo de Datos y Lógica Multi-Tenant

Autenticación y Autorización:
Un usuario se loguea. El sistema identifica su role (admin, client, reseller).
Si es reseller, todas las consultas a datos de clientes, servicios, facturas, etc., se filtrarán automáticamente por WHERE reseller_id = LOGGED_IN_RESELLER_ID.
Si es client, las consultas se filtrarán por WHERE client_id = LOGGED_IN_CLIENT_ID. Si este cliente tiene un reseller_id asociado, el revendedor también podrá ver sus datos.
Si es admin, tiene acceso a todos los datos, pero puede tener interfaces para filtrar por revendedor.
Creación de Clientes por Revendedores:
Un revendedor crea un nuevo cliente. El users.reseller_id del nuevo cliente se establece al ID del revendedor.
Gestión de Productos:
El Admin crea productos globales (owner_id = NULL).
Un revendedor (si tiene permiso) puede crear productos propios (owner_id = RESELLER_ID).
Un revendedor puede elegir qué productos de plataforma revender.
Visibilidad: Las Políticas de Laravel serán fundamentales para asegurar que un revendedor no pueda ver ni modificar datos de otro revendedor o de clientes directos de la plataforma (a menos que sea una funcionalidad explícita).
5. Consideraciones de Escalabilidad y Mantenimiento Futuro

Indexación: Se definirán índices en todas las claves foráneas y columnas frecuentemente usadas en búsquedas y filtros (ej: status, email, domain_name, reseller_id, next_due_date).
Tareas en Cola (Jobs): Operaciones como aprovisionamiento de servicios, envío de emails masivos, generación de facturas de renovación, se manejarán asíncronamente para no degradar el rendimiento de la aplicación web.
Pruebas: Se enfatizará la creación de pruebas unitarias y de integración para asegurar la estabilidad a medida que el sistema crece.
Documentación Continua: Este documento es un punto de partida. La documentación del código (PHPDoc) y de la API (si se desarrolla) será crucial.

Las Normas

Sección 1 (Tecnologías Base): Podemos añadir las versiones específicas si las tenemos claras (aunque mencionaste Laravel 12, la última estable es la 11, pero podemos ajustarlo).

Sección 2 (Estructura de Directorios): La estructura que propusiste es muy similar. Podemos asegurar que routes/client.php esté explícito y mencionar los directorios de tests.
Nueva Sección: Convenciones de Nombres: Añadiríamos tus reglas para controladores, modelos, vistas y rutas.
Nueva Sección: Reglas para Migraciones y Modelos: Incluiríamos la obligatoriedad de timestamps, softDeletes, documentación de relaciones y uso de factories/seeders.
Nueva Sección: Políticas de Acceso y Roles: Especificar el uso de Spatie desde el inicio y la documentación de políticas.
Nueva Sección: Versionado y Ramas: Añadir tus directrices de Git.
Nueva Sección: Ejemplo de Estructura de un Módulo: Incluir tu ejemplo del módulo de usuarios.
