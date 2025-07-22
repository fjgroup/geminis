<?php

// ✅ ARQUITECTURA HEXAGONAL - Controladores migrados a Infrastructure
use App\Domains\Invoices\Infrastructure\Http\Controllers\Client\ClientInvoiceController;
use App\Domains\BillingAndPayments\Infrastructure\Http\Controllers\Client\ClientTransactionController;
use App\Domains\Orders\Infrastructure\Http\Controllers\Client\ClientCartController;

// ⚠️ PENDIENTES DE MIGRAR - Aún en estructura Laravel tradicional
use App\Http\Controllers\CartController;
use App\Http\Controllers\Client\ClientCheckoutControllerRefactored;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientFundAdditionControllerRefactored;
use App\Http\Controllers\Client\ClientManualPaymentController;
use App\Http\Controllers\Client\ClientServiceControllerRefactored;
use App\Http\Controllers\Client\InvoicePaymentController as ClientInvoicePaymentController;
use App\Http\Controllers\Client\PayPalPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| Here is where you can register client routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with client-specific middleware.
|
*/

// Rutas para el área de cliente
Route::prefix('client')->name('client.')->middleware(['auth', 'verified'])->group(function () {
    // Ruta para el dashboard de cliente
    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');

    // Checkout Routes (REFACTORIZADO)
    Route::get('/checkout/product/{product}', [ClientCheckoutControllerRefactored::class, 'showProductCheckoutPage'])->name('checkout.product');
    Route::post('/checkout/submit', [ClientCheckoutControllerRefactored::class, 'submitCurrentOrder'])->name('checkout.submit');
    Route::get('/checkout/select-domain', [ClientCheckoutControllerRefactored::class, 'showSelectDomainPage'])->name('checkout.selectDomain');
    Route::get('/checkout/select-services', [ClientCheckoutControllerRefactored::class, 'showSelectServicesPage'])->name('checkout.selectServices');
    Route::get('/checkout/confirm', [ClientCheckoutControllerRefactored::class, 'showConfirmOrderPage'])->name('checkout.confirm');

    // Rutas AJAX para checkout
    Route::get('/checkout/cart-summary', [ClientCheckoutControllerRefactored::class, 'getCartSummary'])->name('checkout.cartSummary');
    Route::get('/checkout/validate-cart', [ClientCheckoutControllerRefactored::class, 'validateCart'])->name('checkout.validateCart');
    Route::post('/checkout/clear-session', [ClientCheckoutControllerRefactored::class, 'clearCheckoutSession'])->name('checkout.clearSession');
    Route::get('/checkout/progress', [ClientCheckoutControllerRefactored::class, 'getCheckoutProgress'])->name('checkout.progress');

    // Rutas de Recurso para la gestión de servicios de cliente (REFACTORIZADO)
    Route::resource('services', ClientServiceControllerRefactored::class); // Consolidated service routes using resource including index

    // Rutas para la gestión de facturas de cliente (HEXAGONAL)
    Route::resource('invoices', ClientInvoiceController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/invoices/{invoice}/pay-with-balance', [ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');

    // Manual Payment Routes for Client
    Route::get('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'showPaymentForm'])->name('invoices.manualPayment.create');
    Route::post('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'processManualPayment'])->name('invoices.manualPayment.store');
    Route::post('/invoices/{invoice}/cancel-payment-report', [ClientInvoiceController::class, 'cancelPaymentReport'])->name('invoices.cancelPaymentReport');

    // Simulated payment route (if you keep it for other gateways)
    Route::post('/invoices/{invoice}/pay', [ClientInvoicePaymentController::class, 'store'])->name('invoices.payment.store');

    // New PayPal Payment Routes
    Route::get('/paypal/payment/create/{invoice}', [PayPalPaymentController::class, 'createPayment'])->name('paypal.payment.create');
    Route::get('/paypal/payment/success', [PayPalPaymentController::class, 'handlePaymentSuccess'])->name('paypal.payment.success');
    Route::get('/paypal/payment/cancel', [PayPalPaymentController::class, 'handlePaymentCancel'])->name('paypal.payment.cancel');

    // Rutas para la gestión de transacciones de cliente (HEXAGONAL)
    Route::get('/transactions', [ClientTransactionController::class, 'index'])->name('transactions.index');

    // Rutas para Adición de Fondos (REFACTORIZADO)
    Route::get('/add-funds', [ClientFundAdditionControllerRefactored::class, 'showAddFundsForm'])->name('funds.create');
    Route::post('/add-funds', [ClientFundAdditionControllerRefactored::class, 'processFundAddition'])->name('funds.store');
    Route::get('/funds/history', [ClientFundAdditionControllerRefactored::class, 'showHistory'])->name('funds.history');

    // PayPal Fund Addition Routes
    Route::post('/funds/paypal/initiate', [ClientFundAdditionControllerRefactored::class, 'initiatePayPalPayment'])->name('funds.paypal.initiate');
    Route::get('/funds/paypal/success', [ClientFundAdditionControllerRefactored::class, 'handlePayPalSuccess'])->name('funds.paypal.success');
    Route::get('/funds/paypal/cancel', [ClientFundAdditionControllerRefactored::class, 'handlePayPalCancel'])->name('funds.paypal.cancel');

    // AJAX Routes for Fund Addition
    Route::get('/funds/stats', [ClientFundAdditionControllerRefactored::class, 'getStats'])->name('funds.stats');
    Route::post('/funds/validate', [ClientFundAdditionControllerRefactored::class, 'validateFundAddition'])->name('funds.validate');
    Route::get('/funds/payment-methods', [ClientFundAdditionControllerRefactored::class, 'getPaymentMethods'])->name('funds.paymentMethods');
    Route::get('/funds/paypal/status', [ClientFundAdditionControllerRefactored::class, 'checkPayPalStatus'])->name('funds.paypal.status');
    Route::post('/funds/cancel-request', [ClientFundAdditionControllerRefactored::class, 'cancelPendingRequest'])->name('funds.cancelRequest');
    Route::get('/funds/minimum-amounts', [ClientFundAdditionControllerRefactored::class, 'getMinimumAmounts'])->name('funds.minimumAmounts');

    // Rutas adicionales para servicios de cliente (REFACTORIZADO)
    Route::post('/services/{service}/request-cancellation', [ClientServiceControllerRefactored::class, 'requestCancellation'])->name('services.requestCancellation');
    Route::get('/services/{service}/upgrade-downgrade-options', [ClientServiceControllerRefactored::class, 'showUpgradeDowngradeOptions'])->name('services.showUpgradeDowngradeOptions');
    Route::post('/services/{service}/change-password', [ClientServiceControllerRefactored::class, 'changePassword'])->name('services.changePassword');
    Route::post('/services/{service}/suspend', [ClientServiceControllerRefactored::class, 'suspend'])->name('services.suspend');
    Route::post('/services/{service}/renew', [ClientServiceControllerRefactored::class, 'renew'])->name('services.renew');
    Route::get('/services/{service}/download-config', [ClientServiceControllerRefactored::class, 'downloadConfig'])->name('services.downloadConfig');

    // Rutas AJAX para servicios
    Route::get('/services/{service}/details', [ClientServiceControllerRefactored::class, 'getServiceDetails'])->name('services.details');
    Route::get('/services/{service}/upgrade-options', [ClientServiceControllerRefactored::class, 'getUpgradeDowngradeOptionsAjax'])->name('services.upgradeOptions');

    // Rutas adicionales para facturas (HEXAGONAL)
    Route::post('/invoices/{invoice}/cancel-new-order', [ClientInvoiceController::class, 'requestInvoiceCancellation'])->name('invoices.cancelNewOrder');
    Route::get('/invoices/by-status', [ClientInvoiceController::class, 'getInvoicesByStatus'])->name('invoices.byStatus');
    Route::get('/invoices/stats', [ClientInvoiceController::class, 'getStats'])->name('invoices.stats');
    Route::get('/invoices/{invoice}/check-balance', [ClientInvoiceController::class, 'checkBalancePayment'])->name('invoices.checkBalance');
    Route::get('/invoices/{invoice}/download-pdf', [ClientInvoiceController::class, 'downloadPdf'])->name('invoices.downloadPdf');
    Route::get('/invoices/unpaid-count', [ClientInvoiceController::class, 'getUnpaidCount'])->name('invoices.unpaidCount');
    Route::get('/invoices/search', [ClientInvoiceController::class, 'search'])->name('invoices.search');

    // Rutas para el Carrito de Compras (REFACTORIZADO - UNIFICADO)
    // Cart routes with rate limiting
    Route::prefix('cart')->name('cart.')->middleware(['cart.ratelimit:20,1'])->group(function () {
        Route::get('/', [CartController::class, 'show'])->name('get');
        Route::get('/index', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'store'])->name('add');
        Route::post('/update', [CartController::class, 'update'])->name('update');
        Route::post('/remove', [CartController::class, 'destroy'])->name('remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/summary', [CartController::class, 'summary'])->name('summary');
        Route::get('/validate', [CartController::class, 'validateCart'])->name('validate');

        // Rutas legacy mantenidas temporalmente para compatibilidad (HEXAGONAL)
        // TODO: Migrar frontend para usar las nuevas rutas
        Route::post('/account/set-domain', [ClientCartController::class, 'setDomainForAccount'])->name('account.setDomain');
        Route::post('/account/remove-domain', [ClientCartController::class, 'removeDomainFromAccount'])->name('account.removeDomain');
        Route::post('/account/set-primary-service', [ClientCartController::class, 'setPrimaryServiceForAccount'])->name('account.setPrimaryService');
        Route::post('/account/remove-primary-service', [ClientCartController::class, 'removePrimaryServiceFromAccount'])->name('account.removePrimaryService');
    });
});
