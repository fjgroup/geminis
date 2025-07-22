# 🏗️ Estructura de Dominios - Fase 3

Esta carpeta contiene la organización por dominios de negocio del proyecto, siguiendo los principios de Domain-Driven Design (DDD) y preparando el terreno para una futura arquitectura hexagonal.

## 📁 Estructura de Dominios

### 🛍️ Products
**Responsabilidad**: Gestión completa del catálogo de productos
- **Models/**: Product.php, ProductPricing.php, ProductType.php
- **Services/**: ProductCreator.php, ProductUpdater.php, ProductDeleter.php
- **DataTransferObjects/**: CreateProductDTO.php, UpdateProductDTO.php
- **Actions/**: CreateProductAction.php, UpdateProductAction.php

### 👥 Users  
**Responsabilidad**: Gestión de usuarios, autenticación y roles
- **Models/**: User.php, ResellerProfile.php
- **Services/**: UserCreator.php, UserRoleManager.php, UserAuthenticator.php
- **DataTransferObjects/**: CreateUserDTO.php, UpdateUserDTO.php
- **Actions/**: CreateUserAction.php, ChangeUserRoleAction.php

### 🧾 Invoices
**Responsabilidad**: Facturación, generación y gestión de facturas
- **Models/**: Invoice.php, InvoiceItem.php, Transaction.php
- **Services/**: InvoiceGenerator.php, InvoiceValidator.php, InvoiceCalculator.php
- **DataTransferObjects/**: CreateInvoiceDTO.php, InvoiceItemDTO.php
- **Actions/**: GenerateInvoiceAction.php, ProcessPaymentAction.php

### 🔧 ClientServices
**Responsabilidad**: Servicios contratados por clientes y su gestión
- **Models/**: ClientService.php
- **Services/**: ClientServiceProvisioner.php, ClientServiceManager.php
- **DataTransferObjects/**: CreateClientServiceDTO.php, UpdateClientServiceDTO.php
- **Actions/**: ProvisionServiceAction.php, SuspendServiceAction.php

### 🔄 Shared
**Responsabilidad**: Elementos compartidos entre dominios
- **ValueObjects/**: Money.php, Email.php, Status.php
- **Exceptions/**: DomainException.php, ValidationException.php
- **Traits/**: HasUuid.php, Timestampable.php
- **Enums/**: UserRole.php, ServiceStatus.php

## 🎯 Principios Aplicados

### Single Responsibility Principle (SRP)
- Cada dominio tiene una responsabilidad específica y bien definida
- Los servicios dentro de cada dominio manejan una sola operación

### Open/Closed Principle (OCP)
- Nuevas funcionalidades se añaden creando nuevos servicios
- Los dominios son extensibles sin modificar código existente

### Dependency Inversion Principle (DIP)
- Los servicios reciben dependencias via constructor
- Preparado para interfaces en la futura Fase 4

## 📋 Convenciones de Nomenclatura

### Servicios
- **Creator**: Para operaciones de creación (ej: ProductCreator)
- **Updater**: Para operaciones de actualización (ej: ProductUpdater)
- **Deleter**: Para operaciones de eliminación (ej: ProductDeleter)
- **Manager**: Para operaciones complejas (ej: UserRoleManager)
- **Validator**: Para validaciones específicas (ej: InvoiceValidator)

### DTOs
- **CreateXDTO**: Para datos de creación
- **UpdateXDTO**: Para datos de actualización
- **XDetailsDTO**: Para transferir detalles completos

### Actions
- **CreateXAction**: Comandos de creación
- **UpdateXAction**: Comandos de actualización
- **ProcessXAction**: Comandos de procesamiento

## 🔄 Migración desde Estructura Actual

### Estado Actual (Fase 2)
```
app/
├── Services/           # 23 servicios mezclados
├── Models/            # Todos los modelos juntos
├── Http/Controllers/  # Controladores por rol
```

### Estado Objetivo (Fase 3)
```
app/Domains/
├── Products/          # Todo relacionado con productos
├── Users/            # Todo relacionado con usuarios
├── Invoices/         # Todo relacionado con facturación
├── ClientServices/   # Todo relacionado con servicios
└── Shared/           # Elementos compartidos
```

## 🚀 Plan de Migración

1. **Migrar Dominio Products** (Piloto)
2. **Migrar Dominio Users** (Crítico)
3. **Migrar Dominio Invoices** (Complejo)
4. **Migrar Dominio ClientServices** (Específico)
5. **Crear elementos Shared** (Transversal)

## 📝 Notas Importantes

- **No romper funcionalidades**: La migración es organizacional, no funcional
- **Mantener tests**: Todos los tests existentes deben seguir funcionando
- **Actualizar imports**: Cambiar namespaces conforme se migran archivos
- **Service Providers**: Cada dominio tendrá su propio Service Provider

## 🎯 Preparación para Fase 4 (Arquitectura Hexagonal)

Esta estructura prepara el terreno para:
- **Puertos e Interfaces**: Cada servicio tendrá su interfaz
- **Adaptadores**: Implementaciones específicas de infraestructura
- **Entidades de Dominio**: Modelos puros sin dependencias de framework
- **Casos de Uso**: Servicios convertidos en handlers de comandos/queries

---

**Fecha de creación**: 2025-01-22  
**Estado**: Estructura base creada, listo para migración  
**Próximo paso**: Migrar dominio Products
