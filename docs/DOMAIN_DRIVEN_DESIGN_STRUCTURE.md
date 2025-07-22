# 🏗️ Estructura Domain-Driven Design (DDD)

## 📁 Nueva Estructura de Carpetas Propuesta

```
app/
├── Domains/                         # Dominios de negocio (ESTRUCTURA ACTUAL)
│   ├── Users/                       # Dominio de usuarios
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   └── ResellerProfile.php
│   │   ├── Services/
│   │   │   ├── UserCreator.php
│   │   │   ├── UserManagementService.php
│   │   │   └── UserDeletionService.php
│   │   ├── DataTransferObjects/
│   │   │   ├── CreateUserDTO.php
│   │   │   └── UpdateUserDTO.php
│   │   ├── Actions/
│   │   │   ├── CreateUserAction.php
│   │   │   └── ChangeUserRoleAction.php
│   │   └── UserServiceProvider.php
│   ├── Products/                    # Dominio de productos
│   │   ├── Models/
│   │   │   └── Product.php
│   │   ├── Services/
│   │   │   ├── ProductCreator.php
│   │   │   ├── ProductUpdater.php
│   │   │   └── ProductManagementService.php
│   │   ├── DataTransferObjects/
│   │   │   ├── CreateProductDTO.php
│   │   │   └── UpdateProductDTO.php
│   │   └── ProductServiceProvider.php
│   ├── Invoices/                    # Dominio de facturación
│   │   ├── Models/
│   │   │   ├── Invoice.php
│   │   │   └── InvoiceItem.php
│   │   ├── Services/
│   │   │   ├── InvoiceGenerator.php
│   │   │   └── InvoiceManagementService.php
│   │   ├── DataTransferObjects/
│   │   │   ├── CreateInvoiceDTO.php
│   │   │   └── InvoiceItemDTO.php
│   │   └── InvoiceServiceProvider.php
│   │   │   ├── UserDeleted.php
│   │   │   └── UserRoleChanged.php
│   │   └── Policies/
│   │       └── UserPolicy.php
│   │
│   ├── Invoice/                     # Dominio de facturación
│   │   ├── Models/
│   │   │   ├── Invoice.php
│   │   │   ├── InvoiceItem.php
│   │   │   └── Transaction.php
│   │   ├── Services/
│   │   │   ├── InvoiceNumberService.php
│   │   │   ├── InvoiceValidationService.php
│   │   │   └── InvoiceManagementService.php
│   │   ├── Contracts/
│   │   │   ├── InvoiceNumberServiceInterface.php
│   │   │   └── InvoiceValidationServiceInterface.php
│   │   ├── ValueObjects/
│   │   │   ├── Money.php
│   │   │   ├── InvoiceNumber.php
│   │   │   └── InvoiceStatus.php
│   │   ├── Commands/
│   │   │   ├── CreateInvoiceCommand.php
│   │   │   └── PayInvoiceCommand.php
│   │   └── Events/
│   │       ├── InvoiceCreated.php
│   │       └── InvoicePaid.php
│   │
│   ├── Product/                     # Dominio de productos
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   ├── ProductPricing.php
│   │   │   └── ProductType.php
│   │   ├── Services/
│   │   │   ├── ProductService.php
│   │   │   └── PricingCalculatorService.php
│   │   ├── Contracts/
│   │   │   └── ProductServiceInterface.php
│   │   ├── ValueObjects/
│   │   │   ├── ProductStatus.php
│   │   │   └── Price.php
│   │   └── Commands/
│   │       ├── CreateProductCommand.php
│   │       └── UpdateProductCommand.php
│   │
│   └── ClientService/               # Dominio de servicios del cliente
│       ├── Models/
│       │   └── ClientService.php
│       ├── Services/
│       │   └── ClientServiceBusinessService.php
│       ├── Contracts/
│       │   └── ClientServiceBusinessServiceInterface.php
│       ├── ValueObjects/
│       │   └── ServiceStatus.php
│       └── Commands/
│           ├── CreateClientServiceCommand.php
│           └── RenewClientServiceCommand.php
│
├── Infrastructure/                  # Infraestructura técnica
│   ├── Repositories/               # Implementaciones de repositorios
│   │   ├── Eloquent/
│   │   │   ├── EloquentUserRepository.php
│   │   │   ├── EloquentInvoiceRepository.php
│   │   │   └── EloquentProductRepository.php
│   │   └── Cache/
│   │       ├── CachedUserRepository.php
│   │       └── CachedProductRepository.php
│   ├── External/                   # Servicios externos
│   │   ├── PaymentGateways/
│   │   │   ├── PayPalGateway.php
│   │   │   └── StripeGateway.php
│   │   └── EmailProviders/
│   │       └── MailgunProvider.php
│   ├── Persistence/                # Configuración de persistencia
│   │   ├── Migrations/
│   │   └── Seeders/
│   └── Cache/                      # Configuración de cache
│       ├── CacheManager.php
│       └── CacheStrategies/
│
├── Application/                     # Capa de aplicación
│   ├── Services/                   # Servicios de aplicación
│   │   ├── UserApplicationService.php
│   │   ├── InvoiceApplicationService.php
│   │   └── ProductApplicationService.php
│   ├── Commands/                   # Comandos de aplicación
│   │   ├── CommandBus.php
│   │   └── CommandInterface.php
│   ├── Queries/                    # Consultas de aplicación
│   │   ├── QueryBus.php
│   │   └── QueryInterface.php
│   ├── DTOs/                       # Data Transfer Objects
│   │   ├── UserDTO.php
│   │   ├── InvoiceDTO.php
│   │   └── ProductDTO.php
│   └── Factories/                  # Factories de aplicación
│       ├── ServiceFactory.php
│       └── CommandFactory.php
│
├── Presentation/                    # Capa de presentación
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   ├── InvoiceController.php
│   │   │   │   │   └── ProductController.php
│   │   │   │   └── V2/
│   │   │   └── Web/
│   │   │       ├── Admin/
│   │   │       ├── Client/
│   │   │       └── Reseller/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   │   ├── User/
│   │   │   ├── Invoice/
│   │   │   └── Product/
│   │   └── Resources/
│   │       ├── User/
│   │       ├── Invoice/
│   │       └── Product/
│   └── Console/
│       └── Commands/
│
└── Shared/                          # Código compartido
    ├── ValueObjects/               # Value Objects compartidos
    │   ├── Email.php
    │   ├── PhoneNumber.php
    │   └── Address.php
    ├── Exceptions/                 # Excepciones personalizadas
    │   ├── DomainException.php
    │   ├── ValidationException.php
    │   └── BusinessRuleException.php
    ├── Traits/                     # Traits reutilizables
    │   ├── HasUuid.php
    │   └── HasTimestamps.php
    ├── Enums/                      # Enumeraciones
    │   ├── UserRole.php
    │   ├── InvoiceStatus.php
    │   └── ProductStatus.php
    └── Utilities/                  # Utilidades
        ├── DateHelper.php
        ├── StringHelper.php
        └── ArrayHelper.php
```

## 🎯 Beneficios de esta Estructura

### 1. **Separación Clara de Responsabilidades**
- **Domain**: Lógica de negocio pura
- **Application**: Orquestación de casos de uso
- **Infrastructure**: Detalles técnicos
- **Presentation**: Interfaz con el usuario

### 2. **Modularidad por Dominio**
- Cada dominio es independiente
- Fácil mantenimiento y testing
- Escalabilidad horizontal

### 3. **Testabilidad Mejorada**
- Dependencias claras
- Mocking simplificado
- Tests unitarios aislados

### 4. **Extensibilidad**
- Nuevos dominios sin afectar existentes
- Patrones consistentes
- Fácil onboarding de desarrolladores

## 🚀 Plan de Migración

### Fase 1: Crear Estructura Base
```bash
# ESTRUCTURA ACTUAL IMPLEMENTADA
mkdir -p app/Domains/{Users,Products,Invoices,ClientServices,Shared}
mkdir -p app/Domains/Users/{Models,Services,DataTransferObjects,Actions}
mkdir -p app/Domains/Products/{Models,Services,DataTransferObjects,Actions}
mkdir -p app/Domains/Invoices/{Models,Services,DataTransferObjects,Actions}
mkdir -p app/Domains/ClientServices/{Models,Services,DataTransferObjects,Actions}
mkdir -p app/Domains/Shared/{ValueObjects,Exceptions,Traits,Enums}
```

### Fase 2: Migrar Modelos por Dominio
- Mover modelos a sus respectivos dominios
- Actualizar namespaces
- Ajustar imports

### Fase 3: Migrar Servicios
- Reorganizar servicios por dominio
- Mantener interfaces en Contracts
- Actualizar ServiceProvider

### Fase 4: Migrar Controladores
- Separar por versión de API
- Organizar por dominio
- Implementar Resources

### Fase 5: Crear Value Objects
- Extraer lógica de validación
- Implementar inmutabilidad
- Mejorar type safety

## 📋 Checklist de Migración

- [ ] Crear estructura de carpetas
- [ ] Migrar modelos User
- [ ] Migrar servicios User
- [ ] Migrar modelos Invoice
- [ ] Migrar servicios Invoice
- [ ] Migrar modelos Product
- [ ] Migrar servicios Product
- [ ] Crear Value Objects
- [ ] Actualizar ServiceProviders
- [ ] Migrar controladores
- [ ] Actualizar tests
- [ ] Documentar cambios
