export const projectPhases = [
  {
    id: 'Fase -1',
    title: 'Control de Versiones (Git y GitHub)',
    tasks: [
      // No hay tareas con ID -1.x en el índice.
    ]
  },
  {
    id: 'Fase 0',
    title: 'Configuración Inicial del Proyecto',
    tasks: [
      { id: '0.1', description: 'Creación del Proyecto Laravel (Si es nuevo)' },
      { id: '0.2', description: 'Configuración del Entorno' },
      { id: '0.3', description: 'Instalación de Laravel Breeze o Jetstream (para Autenticación Base e Inertia/Vue)' },
      { id: '0.4', description: 'Limpieza Inicial de Rutas y Vistas de Breeze (Opcional, para nuestro enfoque)' },
    ]
  },
  {
    id: 'Fase 1',
    title: 'Módulo de Usuarios (CRUD Básico - Panel de Administración)',
    tasks: [
      { id: '1.1', description: 'Migración de la Tabla `users`' },
      { id: '1.2', description: 'Modelo `User`' },
      { id: '1.3', description: 'Controlador de Usuarios para Administración (`Admin\\UserController`)' },
      { id: '1.4', description: 'Rutas para el CRUD de Usuarios (Administración)' },
      { id: '1.5', description: 'Vista de Listado de Usuarios (`resources/js/Pages/Admin/Users/Index.vue`)' },
      { id: '1.6', description: 'Vista y Formulario de Creación de Usuarios (`resources/js/Pages/Admin/Users/Create.vue`)' },
      { id: '1.7', description: 'Lógica de Almacenamiento de Usuarios (`store` en `Admin\\UserController`) y Validación' },
      { id: '1.8', description: 'Vista y Formulario de Edición de Usuarios (`resources/js/Pages/Admin/Users/Edit.vue`)' },
      { id: '1.9', description: 'Lógica de Actualización de Usuarios (`update` en `Admin\\UserController`) y Validación' },
      { id: '1.10', description: 'Lógica de Eliminación de Usuarios (`destroy` en `Admin\\UserController`)' },
    ]
  },
    {
    id: 'Fase 2',
    title: 'Layout y Permisos (Panel de Administración)',
    tasks: [
      { id: '2.1', description: 'Creación de un Layout Básico para el Panel de Administración (`AdminLayout.vue`)' },
      { id: '2.2', description: 'Integrar `AdminLayout.vue` en las Vistas de Usuarios' },
      { id: '2.3', description: 'Aplicar Middleware de Autenticación a las Rutas de Administración' },
      { id: '2.4', description: 'Crear y Aplicar Middleware de Rol de Administrador (`EnsureUserIsAdmin`)' },
      { id: '2.5', description: 'Creación de un Dashboard Básico para el Administrador' },
      { id: '2.6', description: 'Implementación de Políticas de Laravel para Usuarios (`UserPolicy`)' },
      { id: '2.7', description: 'Aplicar `UserPolicy` en `Admin\\UserController`' },
      { id: '2.8', description: 'Implementación de la Funcionalidad "Ver Usuario" (`show` en `Admin\\UserController`)' },
    ]
  },
  {
    id: 'Fase 3',
    title: 'Módulo de Productos (CRUD Básico - Panel de Administración)',
    tasks: [
      { id: '3.1', description: 'Migración de la Tabla `products`' },
      { id: '3.2', description: 'Modelo `Product`' },
      { id: '3.3', description: 'Controlador de Productos para Administración (`Admin\\ProductController`)' },
      { id: '3.4', description: 'Rutas para el CRUD de Productos (Administración)' },
      { id: '3.5', description: 'Vista de Listado de Productos (`resources/js/Pages/Admin/Products/Index.vue`)' },
      { id: '3.6', description: 'Migración de la Tabla `products`' },
      { id: '3.7', description: 'Modelo `Product`' },
      { id: '3.8', description: 'Migración de la Tabla `product_pricing`' },
      { id: '3.9', description: 'Modelo `ProductPricing`' },
      { id: '3.10', description: 'Controlador para `ProductPricing` (Integrado en `AdminProductController`)' },
      { id: '3.11', description: 'Rutas para Precios de Productos (Anidadas o Acciones en `AdminProductController`)' },
      { id: '3.12', description: 'Vistas para Precios de Productos (Integradas en `Admin/Products/Edit.vue`)' },
      { id: '3.13', description: 'Lógica en `AdminProductController` para Precios' },
      { id: '3.14', description: 'Políticas de Acceso para `ProductPricing` (Opcional, o integrada en `ProductPolicy`)' },
      { id: '3.15', description: 'FormRequests para Precios (Opcional, o validación en controlador)' },
    ]
  },
  {
    id: 'Fase 4',
    title: 'Capacidades de Revendedor y Configuración Avanzada de Productos',
    tasks: [
      { id: '4.1', description: 'Migración de la Tabla `reseller_profiles`' },
      { id: '4.2', description: 'Modelo `ResellerProfile`' },
      { id: '4.3', description: 'CRUD Básico para `ResellerProfile` (Integrado en `Admin\\UserController`)' },
      { id: '4.4', description: 'Migración de la Tabla `configurable_option_groups`' },
      { id: '4.5', description: 'Modelo `ConfigurableOptionGroup`' },
      { id: '4.6', description: 'Migración de la Tabla `configurable_options`' },
      { id: '4.7', description: 'Modelo `ConfigurableOption`' },
      { id: '4.8', description: 'Migración de la Tabla `configurable_option_pricing`' },
      { id: '4.9', description: 'Modelo `ConfigurableOptionPricing`' },
      { id: '4.10', description: 'Migración Tabla Pivote `product_configurable_option_group`' },
      { id: '4.11', description: 'CRUD para `ConfigurableOptionGroup` (Admin)' },
      { id: '4.12', description: 'CRUD para `ConfigurableOption` (Admin - Anidado o en vista de Grupo)' },
      { id: '4.13', description: 'Asignación de Grupos de Opciones Configurables a Productos' },
      { id: '4.14', description: 'Gestión de Precios para `ConfigurableOption`' },
      { id: '4.15', description: '(Opcional) Políticas de Acceso para Opciones Configurables y sus Precios' },
    ]
  },
  {
    id: 'Fase 5',
    title: 'Módulo de Servicios de Cliente',
    tasks: [
      { id: '5.1', description: 'Migración de la Tabla `client_services`' },
      { id: '5.2', description: 'Modelo `ClientService`' },
      { id: '5.3', description: 'CRUD Básico para `ClientService` (Admin)' },
      { id: '5.4', description: 'Lógica de Estados para `ClientService`' },
      { id: '5.5', description: 'Migración Tabla `client_service_configurable_options`' },
      { id: '5.6', description: 'Panel de Cliente: Listado de Servicios (Básico)' },
    ]
  },
  {
    id: 'Fase 6',
    title: 'Proceso de Compra y Facturación',
    tasks: [
      { id: '6.1', description: 'Migración de la Tabla `orders`' },
      { id: '6.2', description: 'Modelo `Order`' },
      { id: '6.3', description: 'Migración de la Tabla `order_items`' },
      { id: '6.4', description: 'Modelo `OrderItem`' },
      { id: '6.5', description: 'Proceso de Creación de Órdenes (Cliente)' },
      { id: '6.6', description: 'Listado de Órdenes (Admin)' },
      { id: '6.7', description: 'Listado de Órdenes (Cliente)' },
      { id: '6.8', description: 'Migración de la Tabla `invoices`' },
      { id: '6.9', description: 'Modelo `Invoice`' },
      { id: '6.10', description: 'Migración de la Tabla `invoice_items`' },
      { id: '6.11', description: 'Modelo `InvoiceItem`' },
      { id: '6.12', description: 'Generación de Facturas a partir de Órdenes' },
      { id: '6.13', description: 'Generación de Facturas para Renovaciones (Conceptual - Job Futuro)' },
      { id: '6.14', description: 'Listado de Facturas (Admin)' },
      { id: '6.15', description: 'Listado de Facturas (Cliente)' },
      { id: '6.16', description: '(Opcional) Descarga de Factura en PDF' },
      { id: '6.17', description: 'Migración de la Tabla `transactions`' },
      { id: '6.18', description: 'Modelo `Transaction`' },
      { id: '6.19', description: 'Registro Manual de Pagos (Admin)' },
      { id: '6.20', description: 'Listado de Transacciones (Admin)' },
      { id: '6.21', description: 'Aplicación de Códigos de Promoción en el Proceso de Orden' },
      { id: '6.22', description: 'Guardar Promoción Aplicada en la Orden' },
      { id: '6.23', description: 'Mostrar Descuento en Detalles de Orden y Factura' },
      { id: '6.24', description: 'Job para Generar Facturas de Renovación (`GenerateRenewalInvoicesJob`)' },
      { id: '6.25', description: 'Servicio de Facturación (`InvoiceService`) - Método de Renovación' },
      { id: '6.26', description: 'Programación del Job (`Kernel.php`)' },
      { id: '6.26', description: 'Programación del Job (`bootstrap/app.php`)' },
    ]
  },
  {
    id: 'Fase 7',
    title: 'Sistema de Soporte',
    tasks: [
      { id: '7.1', description: 'Migración de la Tabla `support_departments`' },
      { id: '7.2', description: 'Modelo `SupportDepartment`' },
      { id: '7.3', description: 'Migración de la Tabla `support_tickets`' },
      { id: '7.4', description: 'Modelo `SupportTicket`' },
      { id: '7.5', description: 'CRUD para `SupportDepartment` (Admin)' },
      { id: '7.6', description: 'Creación de Tickets de Soporte (Cliente)' },
      { id: '7.7', description: 'Listado de Tickets de Soporte (Cliente)' },
      { id: '7.8', description: 'Listado de Tickets de Soporte (Admin)' },
      { id: '7.9', description: 'Migración de la Tabla `support_ticket_replies`' },
      { id: '7.10', description: 'Modelo `SupportTicketReply`' },
      { id: '7.11', description: 'Migración de la Tabla `support_ticket_attachments`' },
      { id: '7.12', description: 'Modelo `SupportTicketAttachment`' },
      { id: '7.13', description: 'Funcionalidad de Respuesta a Tickets (Cliente y Admin)' },
      { id: '7.14', description: 'Asignación de Tickets a Agentes (Admin/Reseller)' },
      { id: '7.15', description: 'Notas Internas en Tickets' },
      { id: '7.16', description: '(Opcional) Respuestas Predefinidas' },
      { id: '7.17', description: 'Filtros Avanzados en Listado de Tickets (Admin)' },
    ]
  },
  {
    id: 'Fase 8',
    title: 'Módulos Adicionales',
    tasks: [
      { id: '8.1', description: 'Migración de la Tabla `domains`' },
      { id: '8.2', description: 'Modelo `Domain`' },
      { id: '8.3', description: 'CRUD Básico para `Domain` (Admin)' },
      { id: '8.4', description: 'Migración de la Tabla `server_groups`' },
      { id: '8.5', description: 'Modelo `ServerGroup`' },
      { id: '8.6', description: 'Migración de la Tabla `servers`' },
      { id: '8.7', description: 'Modelo `Server`' },
      { id: '8.8', description: 'CRUD Básico para `ServerGroup` y `Server` (Admin)' },
      { id: '8.9', description: 'Migración de la Tabla `promotions`' },
      { id: '8.10', description: 'Modelo `Promotion`' },
      { id: '8.11', description: 'CRUD Básico para `Promotion` (Admin)' },
      { id: '8.12', description: 'Migración de la Tabla `email_templates`' },
      { id: '8.13', description: 'Modelo `EmailTemplate`' },
      { id: '8.14', description: 'CRUD Básico para `EmailTemplate` (Admin)' },
      { id: '8.15', description: 'Migración de la Tabla `activity_logs`' },
      { id: '8.16', description: 'Modelo `ActivityLog`' },
      { id: '8.17', description: 'Migración de la Tabla `settings`' },
      { id: '8.18', description: 'Modelo `Setting`' },
      { id: '8.19', description: 'Implementación Básica de Logs y Settings (Admin)' },
    ]
  },
  {
    id: 'Fase 9',
    title: 'Panel de Revendedor',
    tasks: [
      { id: '9.1', description: 'Middleware para Revendedores (`EnsureUserIsReseller`)' },
      { id: '9.2', description: 'Layout para el Panel de Revendedor (`ResellerLayout.vue`)' },
      { id: '9.3', description: 'Dashboard Básico del Revendedor' },
      { id: '9.4', description: 'Gestión de Clientes por Revendedor (CRUD)' },
      { id: '9.5', description: 'Gestión de Servicios de Clientes por Revendedor' },
      { id: '9.6', description: 'Listado de Productos para Revendedor' },
      { id: '9.7', description: 'Creación/Edición de Productos Propios por Revendedor' },
      { id: '9.8', description: '(Opcional) Selección de Productos de Plataforma para Revender' },
      { id: '9.9', description: 'Gestión de Precios para Productos de Revendedor' },
      { id: '9.10', description: 'Gestión del Perfil de Revendedor (`ResellerProfile`)' },
      { id: '9.11', description: 'Gestión de Configuraciones Específicas del Revendedor (`Settings`)' },
      { id: '9.12', description: '(Opcional) Personalización de Plantillas de Correo por Revendedor' },
    ]
  },
  {
    id: 'Fase 10',
    title: 'Panel de Cliente',
    tasks: [
      { id: '10.1', description: 'Middleware para Clientes (`EnsureUserIsClient`)' },
      { id: '10.2', description: 'Layout para el Panel de Cliente (`ClientLayout.vue`)' },
      { id: '10.3', description: 'Dashboard Básico del Cliente' },
      { id: '10.4', description: 'Visualización de Servicios del Cliente' },
      { id: '10.5', description: 'Visualización de Facturas del Cliente' },
      { id: '10.6', description: 'Gestión de Métodos de Pago del Cliente (Stripe)' },
      { id: '10.7', description: '(Opcional) Gestión de Métodos de Pago (PayPal)' },
      { id: '10.8', description: 'Proceso de Pago de Facturas por el Cliente' },
      { id: '10.9', description: '(Opcional) Pago con PayPal' },
      { id: '10.10', description: 'Visualización de Dominios del Cliente' },
      { id: '10.11', description: '(Opcional) Gestión de Nameservers por Cliente' },
    ]
  },
  {
    id: 'Fase 11',
    title: 'Automatización y Módulos Externos',
    tasks: [
      { id: '11.1', description: 'Definición de Interfaces para Módulos de Aprovisionamiento' },
      { id: '11.2', description: 'Creación de un Servicio de Aprovisionamiento (Manager)' },
      { id: '11.3', description: 'Clases Platzhalter para Módulos Específicos (Ej. CpanelModule)' },
      { id: '11.4', description: 'Implementación de `CpanelModule->createAccount()`' },
      { id: '11.5', description: 'Integración de `createAccount` en el Flujo de Activación de Servicio' },
      { id: '11.6', description: 'Implementación de `CpanelModule->suspendAccount()`' },
      { id: '11.7', description: 'Implementación de `CpanelModule->unsuspendAccount()`' },
      { id: '11.8', description: 'Implementación de `CpanelModule->terminateAccount()`' },
      { id: '11.9', description: 'Integración de Acciones de Módulo en Controladores de Servicio (Admin/Reseller)' },
    ]
  },
  {
    id: 'Fase 12',
    title: 'Notificaciones y Comunicaciones',
    tasks: [
      { id: '12.1', description: 'Servicio de Envío de Correos' },
      { id: '12.2', description: 'Configuración de Correo en `.env`' },
      { id: '12.3', description: 'Integración de Notificaciones por Email' },
    ]
  },
    {
    id: 'Fase 13',
    title: 'API y Servicios Externos',
    tasks: [
      { id: '13.1', description: 'Autenticación API (Sanctum)' },
      { id: '13.2', description: 'Endpoints API para Clientes' },
      { id: '13.3', description: 'Endpoints API para Revendedores' },
      { id: '13.4', description: 'Configuración de Pasarelas de Pago (Stripe/PayPal - Lado Servidor)' },
      { id: '13.5', description: 'Controlador de Webhooks Genérico' },
    ]
  },
  {
    id: 'Fase 14',
    title: 'Pruebas y Refinamiento',
    tasks: [
      { id: '14.1', description: 'Configuración del Entorno de Pruebas' },
      { id: '14.2', description: 'Pruebas Unitarias para Modelos y Servicios' },
      { id: '14.3', description: 'Pruebas de Integración (Feature Tests) para Funcionalidades Clave' },
      { id: '14.4', description: 'Revisión y Mejoras de UX/UI' },
      { id: '14.5', description: 'Chequeos Básicos de Seguridad' },
    ]
  },
   {
    id: 'Fase 15',
    title: 'Base de Conocimiento y Contenido',
    tasks: [
      { id: '15.1', description: 'Migración de la Tabla `kb_categories`' },
      { id: '15.2', description: 'Modelo `KnowledgeBaseCategory`' },
      { id: '15.3', description: 'Migración de la Tabla `kb_articles`' },
      { id: '15.4', description: 'Modelo `KnowledgeBaseArticle`' },
      { id: '15.5', description: 'CRUD Admin para `KnowledgeBaseCategory` y `KnowledgeBaseArticle`' },
      { id: '15.6', description: 'Visualización de la Base de Conocimiento (Cliente)' },
    ]
  },
  {
    id: 'Fase 16',
    title: 'Sistema de Afiliados',
    tasks: [
      { id: '16.1', description: 'Migración de la Tabla `affiliates`' },
      { id: '16.2', description: 'Modelo `Affiliate`' },
      { id: '16.3', description: 'Migraciones para Tablas de Seguimiento de Afiliados' },
      { id: '16.4', description: 'Gestión de Afiliados (Admin)' },
      { id: '16.5', description: 'Lógica de Seguimiento de Clics y Referidos (Básica)' },
    ]
  },
  {
    id: 'Fase 17',
    title: 'Despliegue y Mantenimiento',
    tasks: [
      { id: '17.1', description: 'Revisión de Configuración para Producción' },
      { id: '17.2', description: 'Optimizaciones de Laravel' },
      { id: '17.3', description: 'Configuración del Servidor Web (Nginx/Apache)' },
      { id: '17.4', description: 'Configuración del Supervisor para Workers de Cola' },
    ]
  },
];
