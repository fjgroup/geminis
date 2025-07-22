# 🏗️ Estructura Completa de la Aplicación - Arquitectura Hexagonal

## 📋 Estructura Actual vs Estructura Objetivo

### 🔄 Estado Actual (Parcialmente Migrado)

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                    # ⚠️ A migrar a dominios
│   │   │   ├── AdminProductController.php
│   │   │   ├── AdminUserController.php
│   │   │   ├── AdminInvoiceController.php
│   │   │   ├── AdminTransactionController.php
│   │   │   └── SearchController.php
│   │   ├── Client/                   # ⚠️ A migrar a dominios
│   │   │   ├── ClientDashboardController.php
│   │   │   ├── ClientServiceController.php
│   │   │   ├── ClientInvoiceController.php
│   │   │   └── ClientTransactionController.php
│   │   ├── Reseller/                 # ⚠️ A migrar a dominios
│   │   │   ├── ResellerDashboardController.php
│   │   │   └── ResellerClientController.php
│   │   ├── Auth/                     # ✅ Mantener aquí (transversal)
│   │   ├── Api/                      # ⚠️ A migrar a dominios
│   │   └── Webhook/                  # ⚠️ A migrar a dominios
│   ├── Middleware/
│   └── Requests/
├── Jobs/
├── Models/                           # ✅ Modelos de compatibilidad
├── Notifications/
├── Policies/
├── Providers/
└── Domains/                          # ✅ Estructura hexagonal implementada
    ├── Products/
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   ├── Product.php
    │   │   │   ├── ProductPricing.php
    │   │   │   ├── ProductType.php
    │   │   │   ├── BillingCycle.php
    │   │   │   ├── ConfigurableOption.php
    │   │   │   ├── ConfigurableOptionGroup.php
    │   │   │   ├── ConfigurableOptionPricing.php
    │   │   │   └── DiscountPercentage.php
    │   │   └── ValueObjects/
    │   │       ├── ProductPrice.php
    │   │       ├── ProductStatus.php
    │   │       └── BillingCycle.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   ├── CreateProductUseCase.php
    │   │   │   ├── UpdateProductUseCase.php
    │   │   │   └── DeleteProductUseCase.php
    │   │   ├── Commands/
    │   │   │   ├── CreateProductCommand.php
    │   │   │   └── UpdateProductCommand.php
    │   │   └── Queries/
    │   │       ├── GetProductQuery.php
    │   │       └── SearchProductsQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminProductController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientProductController.php
    │   │   │   │   ├── Api/
    │   │   │   │   │   └── ProductApiController.php
    │   │   │   │   └── Public/
    │   │   │   │       └── ProductCatalogController.php
    │   │   │   └── Requests/
    │   │   │       ├── StoreProductRequest.php
    │   │   │       └── UpdateProductRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentProductRepository.php
    │   │       └── Models/
    │   │           └── Product.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── ProductRepositoryInterface.php
    │   │   └── Application/
    │   │       ├── CreateProductUseCaseInterface.php
    │   │       └── ProductQueryServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   ├── DataTransferObjects/      # ✅ Implementado
    │   └── ProductServiceProvider.php
    │
    ├── Users/
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   ├── User.php
    │   │   │   └── ResellerProfile.php
    │   │   └── ValueObjects/
    │   │       ├── UserRole.php
    │   │       ├── Email.php
    │   │       └── UserStatus.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   ├── RegisterUserUseCase.php
    │   │   │   ├── UpdateUserUseCase.php
    │   │   │   └── ChangeUserRoleUseCase.php
    │   │   ├── Commands/
    │   │   │   ├── RegisterUserCommand.php
    │   │   │   └── UpdateUserCommand.php
    │   │   └── Queries/
    │   │       ├── GetUserQuery.php
    │   │       └── SearchUsersQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminUserController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientProfileController.php
    │   │   │   │   ├── Reseller/
    │   │   │   │   │   └── ResellerProfileController.php
    │   │   │   │   └── Api/
    │   │   │   │       └── UserApiController.php
    │   │   │   └── Requests/
    │   │   │       ├── RegisterUserRequest.php
    │   │   │       └── UpdateUserRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentUserRepository.php
    │   │       └── Models/
    │   │           └── User.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── UserRepositoryInterface.php
    │   │   └── Application/
    │   │       └── UserServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   ├── DataTransferObjects/      # ✅ Implementado
    │   └── UserServiceProvider.php
    │
    ├── Invoices/
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   ├── Invoice.php
    │   │   │   └── InvoiceItem.php
    │   │   └── ValueObjects/
    │   │       ├── InvoiceNumber.php
    │   │       ├── InvoiceStatus.php
    │   │       └── InvoiceAmount.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   ├── GenerateInvoiceUseCase.php
    │   │   │   ├── PayInvoiceUseCase.php
    │   │   │   └── CancelInvoiceUseCase.php
    │   │   ├── Commands/
    │   │   │   ├── GenerateInvoiceCommand.php
    │   │   │   └── PayInvoiceCommand.php
    │   │   └── Queries/
    │   │       ├── GetInvoiceQuery.php
    │   │       └── SearchInvoicesQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminInvoiceController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientInvoiceController.php
    │   │   │   │   └── Api/
    │   │   │   │       └── InvoiceApiController.php
    │   │   │   └── Requests/
    │   │   │       ├── GenerateInvoiceRequest.php
    │   │   │       └── PayInvoiceRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentInvoiceRepository.php
    │   │       └── Models/
    │   │           └── Invoice.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── InvoiceRepositoryInterface.php
    │   │   └── Application/
    │   │       └── InvoiceServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   ├── DataTransferObjects/      # ✅ Implementado
    │   └── InvoiceServiceProvider.php
    │
    ├── BillingAndPayments/           # ✅ Completamente implementado
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   ├── Transaction.php
    │   │   │   └── PaymentMethod.php
    │   │   └── ValueObjects/
    │   │       ├── TransactionAmount.php
    │   │       └── TransactionStatus.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   └── CreateTransactionUseCase.php
    │   │   ├── Commands/
    │   │   │   └── CreateTransactionCommand.php
    │   │   └── Queries/
    │   │       └── GetTransactionQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminTransactionController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientTransactionController.php
    │   │   │   │   └── Api/
    │   │   │   │       └── TransactionApiController.php
    │   │   │   └── Requests/
    │   │   │       ├── CreateTransactionRequest.php
    │   │   │       └── UpdateTransactionRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentTransactionRepository.php
    │   │       └── Models/
    │   │           └── Transaction.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── TransactionRepositoryInterface.php
    │   │   └── Application/
    │   │       └── TransactionServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   └── BillingAndPaymentsServiceProvider.php
    │
    ├── Orders/                       # ✅ Estructura creada
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   ├── Order.php
    │   │   │   ├── OrderItem.php
    │   │   │   └── OrderConfigurableOption.php
    │   │   └── ValueObjects/
    │   │       ├── OrderStatus.php
    │   │       └── OrderTotal.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   ├── CreateOrderUseCase.php
    │   │   │   └── ProcessOrderUseCase.php
    │   │   ├── Commands/
    │   │   │   └── CreateOrderCommand.php
    │   │   └── Queries/
    │   │       └── GetOrderQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminOrderController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientOrderController.php
    │   │   │   │   └── Public/
    │   │   │   │       └── CheckoutController.php
    │   │   │   └── Requests/
    │   │   │       └── CreateOrderRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentOrderRepository.php
    │   │       └── Models/
    │   │           └── Order.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── OrderRepositoryInterface.php
    │   │   └── Application/
    │   │       └── OrderServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   └── OrderServiceProvider.php
    │
    ├── ClientServices/               # ✅ Implementado
    │   ├── Domain/
    │   │   ├── Entities/
    │   │   │   └── ClientService.php
    │   │   └── ValueObjects/
    │   │       ├── ServiceStatus.php
    │   │       └── ServiceConfiguration.php
    │   ├── Application/
    │   │   ├── UseCases/
    │   │   │   ├── ProvisionServiceUseCase.php
    │   │   │   └── SuspendServiceUseCase.php
    │   │   ├── Commands/
    │   │   │   └── ProvisionServiceCommand.php
    │   │   └── Queries/
    │   │       └── GetClientServiceQuery.php
    │   ├── Infrastructure/
    │   │   ├── Http/
    │   │   │   ├── Controllers/
    │   │   │   │   ├── Admin/
    │   │   │   │   │   └── AdminClientServiceController.php
    │   │   │   │   ├── Client/
    │   │   │   │   │   └── ClientServiceController.php
    │   │   │   │   └── Api/
    │   │   │   │       └── ClientServiceApiController.php
    │   │   │   └── Requests/
    │   │   │       └── ProvisionServiceRequest.php
    │   │   └── Persistence/
    │   │       ├── Eloquent/
    │   │       │   └── EloquentClientServiceRepository.php
    │   │       └── Models/
    │   │           └── ClientService.php (Eloquent Model)
    │   ├── Interfaces/
    │   │   ├── Domain/
    │   │   │   └── ClientServiceRepositoryInterface.php
    │   │   └── Application/
    │   │       └── ClientServiceInterface.php
    │   ├── Models/                   # ✅ Compatibilidad
    │   ├── Services/                 # ✅ Implementado
    │   ├── DataTransferObjects/      # ✅ Implementado
    │   └── ClientServiceServiceProvider.php
    │
    └── Shared/                       # ✅ Completamente implementado
        ├── Domain/
        │   ├── ValueObjects/
        │   │   ├── Money.php
        │   │   ├── Email.php
        │   │   ├── PhoneNumber.php
        │   │   └── Address.php
        │   └── Events/
        │       ├── DomainEvent.php
        │       └── EventDispatcher.php
        ├── Application/
        │   ├── Services/
        │   │   ├── EventBus.php
        │   │   └── QueryBus.php
        │   └── DTOs/
        │       ├── PaginationDTO.php
        │       └── FilterDTO.php
        ├── Infrastructure/
        │   ├── Http/
        │   │   ├── Middleware/
        │   │   │   ├── DomainMiddleware.php
        │   │   │   └── LoggingMiddleware.php
        │   │   └── Responses/
        │   │       └── ApiResponse.php
        │   ├── Persistence/
        │   │   ├── Eloquent/
        │   │   │   └── BaseRepository.php
        │   │   └── Cache/
        │   │       └── CacheRepository.php
        │   └── External/
        │       ├── PaymentGateways/
        │       │   ├── PayPalAdapter.php
        │       │   └── StripeAdapter.php
        │       └── EmailServices/
        │           └── MailgunAdapter.php
        ├── Interfaces/
        │   ├── Domain/
        │   │   ├── RepositoryInterface.php
        │   │   └── EventInterface.php
        │   └── Application/
        │       ├── ServiceInterface.php
        │       └── UseCaseInterface.php
        ├── Services/
        │   └── SearchService.php     # ✅ Implementado
        ├── Traits/
        │   └── ApiResponseTrait.php  # ✅ Implementado
        ├── Exceptions/
        │   ├── DomainException.php
        │   └── ValidationException.php
        └── SharedServiceProvider.php
```

## 📊 Estado de Migración

### ✅ Completamente Implementado
- **BillingAndPayments Domain** - Arquitectura hexagonal completa
- **Shared Domain** - Servicios y traits compartidos
- **Value Objects** - TransactionAmount, TransactionStatus
- **Use Cases** - CreateTransactionUseCase
- **Interfaces** - Para inversión de dependencias

### 🔄 Parcialmente Implementado
- **Products Domain** - Modelos migrados, falta estructura hexagonal completa
- **Users Domain** - Modelos migrados, falta estructura hexagonal completa
- **Invoices Domain** - Modelos migrados, falta estructura hexagonal completa
- **ClientServices Domain** - Modelos migrados, falta estructura hexagonal completa
- **Orders Domain** - Estructura creada, falta implementación completa

### ⚠️ Pendiente de Migración
- **Controladores** - Mover de app/Http/Controllers a Infrastructure/Http/Controllers
- **Requests** - Mover a Infrastructure/Http/Requests de cada dominio
- **APIs** - Reorganizar por dominio
- **Webhooks** - Mover a dominios correspondientes

## 🎯 Próximos Pasos

1. **Migrar controladores** a Infrastructure/Http/Controllers de cada dominio
2. **Crear Use Cases** para cada operación de negocio
3. **Implementar Commands y Queries** en Application layer
4. **Crear Value Objects** específicos para cada dominio
5. **Completar interfaces** para todos los servicios

---

**Estado**: 🔄 Migración en Progreso (70% completado)  
**Próximo**: Migración de controladores a Infrastructure layer
