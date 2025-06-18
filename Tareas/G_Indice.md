## Fase -1: Control de Versiones (Git y GitHub)
## Fase 0: Configuración Inicial del Proyecto
### 0.1. Creación del Proyecto Laravel (Si es nuevo)
### 0.2. Configuración del Entorno
### 0.3. Instalación de Laravel Breeze o Jetstream (para Autenticación Base e Inertia/Vue)
### 0.4. Limpieza Inicial de Rutas y Vistas de Breeze (Opcional, para nuestro enfoque)
## Fase 1: Módulo de Usuarios (CRUD Básico - Panel de Administración)
### 1.1. Migración de la Tabla `users`
### 1.2. Modelo `User`
### 1.3. Controlador de Usuarios para Administración (`Admin\UserController`)
### 1.4. Rutas para el CRUD de Usuarios (Administración)
### 1.5. Vista de Listado de Usuarios (`resources/js/Pages/Admin/Users/Index.vue`)
### 1.6. Vista y Formulario de Creación de Usuarios (`resources/js/Pages/Admin/Users/Create.vue`)
### 1.7. Lógica de Almacenamiento de Usuarios (`store` en `Admin\UserController`) y Validación
### 1.8. Vista y Formulario de Edición de Usuarios (`resources/js/Pages/Admin/Users/Edit.vue`)
### 1.9. Lógica de Actualización de Usuarios (`update` en `Admin\UserController`) y Validación
### 1.10. Lógica de Eliminación de Usuarios (`destroy` en `Admin\UserController`)
### 2.1. Creación de un Layout Básico para el Panel de Administración (`AdminLayout.vue`)
### 2.2. Integrar `AdminLayout.vue` en las Vistas de Usuarios
### 2.3. Aplicar Middleware de Autenticación a las Rutas de Administración
### 2.4. Crear y Aplicar Middleware de Rol de Administrador (`EnsureUserIsAdmin`)
### 2.5. Creación de un Dashboard Básico para el Administrador
### 2.6. Implementación de Políticas de Laravel para Usuarios (`UserPolicy`)
### 2.7. Aplicar `UserPolicy` en `Admin\UserController`
### 2.8. Implementación de la Funcionalidad "Ver Usuario" (`show` en `Admin\UserController`)
## Fase 3: Módulo de Productos (CRUD Básico - Panel de Administración)
### 3.1. Migración de la Tabla `products`
### 3.2. Modelo `Product`
### 3.3. Controlador de Productos para Administración (`Admin\ProductController`)
### 3.4. Rutas para el CRUD de Productos (Administración)
### 3.5. Vista de Listado de Productos (`resources/js/Pages/Admin/Products/Index.vue`)
### 3.6. Migración de la Tabla `products`
### 3.7. Modelo `Product`
### 3.8. Migración de la Tabla `product_pricing`
### 3.9. Modelo `ProductPricing`
### 3.10. Controlador para `ProductPricing` (Integrado en `AdminProductController`)
### 3.11. Rutas para Precios de Productos (Anidadas o Acciones en `AdminProductController`)
### 3.12. Vistas para Precios de Productos (Integradas en `Admin/Products/Edit.vue`)
### 3.13. Lógica en `AdminProductController` para Precios
### 3.14. Políticas de Acceso para `ProductPricing` (Opcional, o integrada en `ProductPolicy`)
### 3.15. FormRequests para Precios (Opcional, o validación en controlador)
## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos
### 4.1. Migración de la Tabla `reseller_profiles`
### 4.2. Modelo `ResellerProfile`
### 4.3. CRUD Básico para `ResellerProfile` (Integrado en `Admin\UserController`)
### 4.4. Migración de la Tabla `configurable_option_groups`
### 4.5. Modelo `ConfigurableOptionGroup`
### 4.6. Migración de la Tabla `configurable_options`
### 4.7. Modelo `ConfigurableOption`
### 4.8. Migración de la Tabla `configurable_option_pricing`
### 4.9. Modelo `ConfigurableOptionPricing`
### 4.10. Migración Tabla Pivote `product_configurable_option_group`
### 4.11. CRUD para `ConfigurableOptionGroup` (Admin)
### 4.12. CRUD para `ConfigurableOption` (Admin - Anidado o en vista de Grupo)
### 4.13. Asignación de Grupos de Opciones Configurables a Productos
### 4.14. Gestión de Precios para `ConfigurableOption`
### 4.15. (Opcional) Políticas de Acceso para Opciones Configurables y sus Precios
### 5.1. Migración de la Tabla `client_services`
### 5.2. Modelo `ClientService`
### 5.3. CRUD Básico para `ClientService` (Admin)
### 5.4. Lógica de Estados para `ClientService`
### 5.5. Migración Tabla `client_service_configurable_options`
### 5.6. Panel de Cliente: Listado de Servicios (Básico)
## Fase 6: Proceso de Compra y Facturación
### 6.1. Migración de la Tabla `orders`
### 6.2. Modelo `Order`
### 6.3. Migración de la Tabla `order_items`
### 6.4. Modelo `OrderItem`
### 6.5. Proceso de Creación de Órdenes (Cliente)
### 6.6. Listado de Órdenes (Admin)
### 6.7. Listado de Órdenes (Cliente)
### 6.8. Migración de la Tabla `invoices`
### 6.9. Modelo `Invoice`
### 6.10. Migración de la Tabla `invoice_items`
### 6.11. Modelo `InvoiceItem`
### 6.12. Generación de Facturas a partir de Órdenes
### 6.13. Generación de Facturas para Renovaciones (Conceptual - Job Futuro)
### 6.14. Listado de Facturas (Admin)
### 6.15. Listado de Facturas (Cliente)
### 6.16. (Opcional) Descarga de Factura en PDF
### 6.17. Migración de la Tabla `transactions`
### 6.18. Modelo `Transaction`
### 6.19. Registro Manual de Pagos (Admin)
### 6.20. Listado de Transacciones (Admin)
## Fase 7: Sistema de Soporte
### 7.1. Migración de la Tabla `support_departments`
### 7.2. Modelo `SupportDepartment`
### 7.3. Migración de la Tabla `support_tickets`
### 7.4. Modelo `SupportTicket`
### 7.5. CRUD para `SupportDepartment` (Admin)
### 7.6. Creación de Tickets de Soporte (Cliente)
### 7.7. Listado de Tickets de Soporte (Cliente)
### 7.8. Listado de Tickets de Soporte (Admin)
### 7.9. Migración de la Tabla `support_ticket_replies`
### 7.10. Modelo `SupportTicketReply`
### 7.11. Migración de la Tabla `support_ticket_attachments`
### 7.12. Modelo `SupportTicketAttachment`
### 7.13. Funcionalidad de Respuesta a Tickets (Cliente y Admin)
## Fase 8: Módulos Adicionales
### 8.1. Migración de la Tabla `domains`
### 8.2. Modelo `Domain`
### 8.3. CRUD Básico para `Domain` (Admin)
### 8.4. Migración de la Tabla `server_groups`
### 8.5. Modelo `ServerGroup`
### 8.6. Migración de la Tabla `servers`
### 8.7. Modelo `Server`
### 8.8. CRUD Básico para `ServerGroup` y `Server` (Admin)
### 8.9. Migración de la Tabla `promotions`
### 8.10. Modelo `Promotion`
### 8.11. CRUD Básico para `Promotion` (Admin)
### 8.12. Migración de la Tabla `email_templates`
### 8.13. Modelo `EmailTemplate`
### 8.14. CRUD Básico para `EmailTemplate` (Admin)
### 8.15. Migración de la Tabla `activity_logs`
### 8.16. Modelo `ActivityLog`
### 8.17. Migración de la Tabla `settings`
### 8.18. Modelo `Setting`
### 8.19. Implementación Básica de Logs y Settings (Admin)
## Fase 9: Panel de Revendedor
### 9.1. Middleware para Revendedores (`EnsureUserIsReseller`)
### 9.2. Layout para el Panel de Revendedor (`ResellerLayout.vue`)
### 9.3. Dashboard Básico del Revendedor
### 9.4. Gestión de Clientes por Revendedor (CRUD)
### 9.5. Gestión de Servicios de Clientes por Revendedor
## Fase 10: Panel de Cliente
### 10.1. Middleware para Clientes (`EnsureUserIsClient`)
### 10.2. Layout para el Panel de Cliente (`ClientLayout.vue`)
### 10.3. Dashboard Básico del Cliente
### 10.4. Visualización de Servicios del Cliente
### 10.5. Visualización de Facturas del Cliente
## Fase 11: Automatización y Módulos Externos
### 11.1. Definición de Interfaces para Módulos de Aprovisionamiento
### 11.2. Creación de un Servicio de Aprovisionamiento (Manager)
### 11.3. Clases Platzhalter para Módulos Específicos (Ej. CpanelModule)
### 6.21. Aplicación de Códigos de Promoción en el Proceso de Orden
### 6.22. Guardar Promoción Aplicada en la Orden
### 6.23. Mostrar Descuento en Detalles de Orden y Factura
## Fase 12: Notificaciones y Comunicaciones
### 12.1. Servicio de Envío de Correos
### 12.2. Configuración de Correo en `.env`
### 12.3. Integración de Notificaciones por Email
### 7.14. Asignación de Tickets a Agentes (Admin/Reseller)
### 7.15. Notas Internas en Tickets
### 7.16. (Opcional) Respuestas Predefinidas
### 7.17. Filtros Avanzados en Listado de Tickets (Admin)
### 6.24. Job para Generar Facturas de Renovación (`GenerateRenewalInvoicesJob`)
### 6.25. Servicio de Facturación (`InvoiceService`) - Método de Renovación
### 6.26. Programación del Job (`Kernel.php`)
### 6.26. Programación del Job (`bootstrap/app.php`)
### 9.6. Listado de Productos para Revendedor
### 9.7. Creación/Edición de Productos Propios por Revendedor
### 9.8. (Opcional) Selección de Productos de Plataforma para Revender
### 9.9. Gestión de Precios para Productos de Revendedor
### 9.10. Gestión del Perfil de Revendedor (`ResellerProfile`)
### 9.11. Gestión de Configuraciones Específicas del Revendedor (`Settings`)
### 9.12. (Opcional) Personalización de Plantillas de Correo por Revendedor
## Fase 13: API y Servicios Externos
### 13.1. Autenticación API (Sanctum)
### 13.2. Endpoints API para Clientes
### 13.3. Endpoints API para Revendedores
## Fase 14: Pruebas y Refinamiento
### 14.1. Configuración del Entorno de Pruebas
### 14.2. Pruebas Unitarias para Modelos y Servicios
### 14.3. Pruebas de Integración (Feature Tests) para Funcionalidades Clave
### 14.4. Revisión y Mejoras de UX/UI
### 14.5. Chequeos Básicos de Seguridad
### 13.4. Configuración de Pasarelas de Pago (Stripe/PayPal - Lado Servidor)
### 13.5. Controlador de Webhooks Genérico
### 10.6. Gestión de Métodos de Pago del Cliente (Stripe)
### 10.7. (Opcional) Gestión de Métodos de Pago (PayPal)
### 10.8. Proceso de Pago de Facturas por el Cliente
### 10.9. (Opcional) Pago con PayPal
### 11.4. Implementación de `CpanelModule->createAccount()`
### 11.5. Integración de `createAccount` en el Flujo de Activación de Servicio
### 11.6. Implementación de `CpanelModule->suspendAccount()`
### 11.7. Implementación de `CpanelModule->unsuspendAccount()`
### 11.8. Implementación de `CpanelModule->terminateAccount()`
### 11.9. Integración de Acciones de Módulo en Controladores de Servicio (Admin/Reseller)
### 10.10. Visualización de Dominios del Cliente
### 10.11. (Opcional) Gestión de Nameservers por Cliente
## Fase 15: Base de Conocimiento y Contenido
### 15.1. Migración de la Tabla `kb_categories`
### 15.2. Modelo `KnowledgeBaseCategory`
### 15.3. Migración de la Tabla `kb_articles`
### 15.4. Modelo `KnowledgeBaseArticle`
### 15.5. CRUD Admin para `KnowledgeBaseCategory` y `KnowledgeBaseArticle`
### 15.6. Visualización de la Base de Conocimiento (Cliente)
## Fase 16: Sistema de Afiliados
### 16.1. Migración de la Tabla `affiliates`
### 16.2. Modelo `Affiliate`
### 16.3. Migraciones para Tablas de Seguimiento de Afiliados
### 16.4. Gestión de Afiliados (Admin)
### 16.5. Lógica de Seguimiento de Clics y Referidos (Básica)
## Fase 17: Despliegue y Mantenimiento
### 17.1. Revisión de Configuración para Producción
### 17.2. Optimizaciones de Laravel
### 17.3. Configuración del Servidor Web (Nginx/Apache)
### 17.4. Configuración del Supervisor para Workers de Cola
