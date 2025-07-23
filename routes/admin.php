<?php

// ✅ ARQUITECTURA HEXAGONAL - Controladores migrados a Infrastructure
use App\Domains\Products\Infrastructure\Http\Controllers\Admin\AdminProductController;
use App\Domains\Products\Infrastructure\Http\Controllers\Admin\AdminProductTypeController;
use App\Domains\Products\Infrastructure\Http\Controllers\Admin\ConfigurableOptionController;
use App\Domains\Products\Infrastructure\Http\Controllers\Admin\ConfigurableOptionGroupController;
use App\Domains\Products\Infrastructure\Http\Controllers\Admin\DiscountPercentageController;

use App\Domains\Users\Infrastructure\Http\Controllers\Admin\AdminUserController;
use App\Domains\Users\Infrastructure\Http\Controllers\Admin\AdminProfileController;

use App\Domains\Invoices\Infrastructure\Http\Controllers\Admin\AdminInvoiceController;

use App\Domains\BillingAndPayments\Infrastructure\Http\Controllers\Admin\AdminTransactionController;

use App\Domains\ClientServices\Infrastructure\Http\Controllers\Admin\AdminClientServiceController;

use App\Domains\Orders\Infrastructure\Http\Controllers\Admin\AdminOrderController;

// ⚠️ PENDIENTES DE MIGRAR - Aún en estructura Laravel tradicional
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPaymentMethodControllerRefactored;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with admin-specific middleware.
|
*/

// Rutas para la administración
// Aplicamos múltiples capas de seguridad para proteger estas rutas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin', 'admin.security', 'input.sanitize', 'inject.context'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Admin Profile Routes
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', AdminUserController::class);
    Route::resource('products', AdminProductController::class);

    // Ruta para calcular precios de productos
    Route::post('products/{product}/calculate-pricing', [AdminProductController::class, 'calculatePricing'])
        ->name('products.calculate-pricing');

    Route::resource('configurable-option-groups', ConfigurableOptionGroupController::class);

    // Rutas para gestionar opciones dentro de un grupo
    Route::post('configurable-option-groups/{configurableOptionGroup}/options', [ConfigurableOptionGroupController::class, 'addOption'])
        ->name('configurable-option-groups.add-option');
    Route::delete('configurable-option-groups/{configurableOptionGroup}/options/{option}', [ConfigurableOptionGroupController::class, 'removeOption'])
        ->name('configurable-option-groups.remove-option');

    // Rutas anidadas para las opciones dentro de un grupo (si necesitas el controlador separado)
    Route::resource('configurable-option-groups.options', ConfigurableOptionController::class)->shallow()->except(['index', 'show', 'create', 'edit']);

    // Ruta para actualizar precios de opciones configurables
    Route::post('configurable-options/{option}/update-pricings', [ConfigurableOptionController::class, 'updatePricings'])
        ->name('configurable-options.update-pricings');

    // Rutas para Client Services (HEXAGONAL)
    Route::resource('client-services', AdminClientServiceController::class);
    Route::post('/client-services/{client_service}/retry-provisioning', [AdminClientServiceController::class, 'retryProvisioning'])
        ->name('client-services.retryProvisioning');

    // Ruta para que el admin ingrese al panel del cliente
    Route::get('/client-services/{client_service}/impersonate', [AdminClientServiceController::class, 'impersonateClient'])
        ->name('client-services.impersonate');

    // Ruta para volver al panel de admin desde impersonation
    // Protegida por middleware admin con excepción de seguridad para impersonation
    Route::post('/stop-impersonation', [AdminClientServiceController::class, 'stopImpersonation'])
        ->name('stop-impersonation');

    // Orders Routes (HEXAGONAL)
    Route::resource('orders', AdminOrderController::class);
    Route::get('/orders-stats', [AdminOrderController::class, 'getOrderStats'])->name('orders.stats');

    // Rutas para Facturas de Administración (HEXAGONAL)
    Route::resource('invoices', AdminInvoiceController::class);
    Route::post('/invoices/{invoice}/activate-services', [AdminInvoiceController::class, 'activateServices'])->name('invoices.activateServices');
    Route::post('/invoices/{invoice}/manual-transaction', [AdminInvoiceController::class, 'storeManualTransaction'])->name('invoices.storeManualTransaction');

    // Rutas adicionales para Productos (HEXAGONAL)
    Route::get('/products/{product}/pricing-options', [AdminProductController::class, 'getPricingOptions'])->name('products.pricing-options');
    Route::post('/products/{product}/recalculate-prices', [AdminProductController::class, 'recalculatePrices'])->name('products.recalculate-prices');
    Route::get('/products/search', [AdminProductController::class, 'search'])->name('products.search');
    Route::get('/products/stats', [AdminProductController::class, 'getStats'])->name('products.stats');

    // Rutas legacy mantenidas temporalmente para compatibilidad
    // TODO: Migrar frontend para usar las nuevas rutas
    Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');
    Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');
    Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');

    Route::get('/products/{product}/pricings', [AdminClientServiceController::class, 'getProductPricings'])->name('products.getPricings');

    Route::get('/project-progress', function () {
        // Aquí no necesitas pasar datos porque el componente los tiene o los carga de localStorage
        return Inertia::render('Admin/ProjectProgress');
    })->middleware(['auth', 'verified' /* tu middleware de admin si es necesario */])->name('project.progress');

    // Rutas de Transacciones (HEXAGONAL)
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::post('transactions/{transaction}/confirm', [AdminTransactionController::class, 'confirm'])->name('transactions.confirm');
    Route::post('transactions/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('/transactions/confirm-manual', [AdminTransactionController::class, 'confirmManualPayment'])->name('transactions.confirmManualPayment');
    Route::post('/transactions/{transaction}/refund', [AdminTransactionController::class, 'createRefund'])->name('transactions.refund');

    // Rutas AJAX para transacciones
    Route::get('/transactions-stats', [AdminTransactionController::class, 'getStats'])->name('transactions.stats');
    Route::get('/transactions-export', [AdminTransactionController::class, 'export'])->name('transactions.export');
    Route::get('/transactions-search', [AdminTransactionController::class, 'search'])->name('transactions.search');

    // Payment Methods Route (REFACTORIZADO)
    Route::resource('payment-methods', AdminPaymentMethodControllerRefactored::class);
    Route::get('/payment-methods/validation-rules/{type}', [AdminPaymentMethodControllerRefactored::class, 'getValidationRules'])->name('payment-methods.validationRules');
    Route::get('/payment-methods-active', [AdminPaymentMethodControllerRefactored::class, 'getActivePaymentMethods'])->name('payment-methods.active');
    Route::post('/payment-methods/{paymentMethod}/toggle-status', [AdminPaymentMethodControllerRefactored::class, 'toggleStatus'])->name('payment-methods.toggleStatus');
    // Product Types Route
    Route::resource('product-types', AdminProductTypeController::class);

    // Discount Percentages Route
    Route::resource('discount-percentages', DiscountPercentageController::class);
});

// Ruta especial para volver al panel de admin desde impersonation
// Esta ruta NO está dentro del grupo admin para permitir que los clientes impersonados puedan usarla
Route::post('/admin/stop-impersonation', [AdminClientServiceController::class, 'stopImpersonation'])
    ->middleware(['auth', 'verified'])
    ->name('admin.stop-impersonation');
