<?php

/*
|--------------------------------------------------------------------------
| IMPORTANT: Clear Caches and Regenerate Routes
|--------------------------------------------------------------------------
|
| After making changes to routes or related configuration, and especially
| if you encounter Ziggy errors (like 'route ... is not in the route list'),
| run the following commands in your terminal:
|
| 1. php artisan route:clear
| 2. php artisan config:clear
| 3. php artisan view:clear
| 4. php artisan ziggy:generate
| 5. npm run build (or npm run dev, depending on your workflow)
|
| This ensures that all caches are cleared and Ziggy's route list is
| up-to-date with the latest route definitions.
|
*/

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Importaciones de controladores
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ConfigurableOptionGroupController;
use App\Http\Controllers\Admin\ConfigurableOptionController;
use App\Http\Controllers\Admin\SearchController;
// use App\Http\Controllers\Admin\AdminOrderController; // Removed
use App\Http\Controllers\Admin\AdminInvoiceController; // Import the admin invoice controller
use App\Http\Controllers\Admin\AdminTransactionController; // Import the admin transaction controller
use App\Http\Controllers\Admin\AdminClientServiceController; // Import the admin client service controller
use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Client\ClientDashboardController;
// use App\Http\Controllers\Client\ClientOrderController; // Removed
use App\Http\Controllers\Client\ClientCheckoutController; // Added
use App\Http\Controllers\Client\ClientServiceController; // Import the client service controller
use App\Http\Controllers\Client\InvoicePaymentController as ClientInvoicePaymentController; // Import the client invoice payment controller and alias it
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController; // Import the client invoice controller and alias it
use App\Http\Controllers\Client\TransactionController as ClientTransactionController; // Import the client transaction controller and alias it
use App\Http\Controllers\Client\ClientManualPaymentController; // Import the manual payment controller
use App\Http\Controllers\Client\ClientFundAdditionController; // Import the fund addition controller
use App\Http\Controllers\Client\PayPalController; // Import the PayPalController
use App\Http\Controllers\Client\PayPalPaymentController; // Import the new PayPalPaymentController
use App\Http\Controllers\Admin\AdminProductTypeController; // Import the new ProductTypeController
use App\Http\Controllers\LandingPageController; // Import the new LandingPageController
use App\Http\Controllers\Reseller\ResellerClientController;
use App\Http\Controllers\Reseller\ResellerDashboardController; // Added for reseller dashboard

// Rutas para la administración
// Aplicamos el middleware 'admin' para proteger estas rutas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);

    Route::resource('configurable-option-groups', ConfigurableOptionGroupController::class)->except(['show']); // Show no se usa

    // Rutas anidadas para las opciones dentro de un grupo
    Route::resource('configurable-option-groups.options', ConfigurableOptionController::class)->shallow()->except(['index', 'show', 'create', 'edit']);

    // Rutas para Client Services
    Route::resource('client-services', AdminClientServiceController::class);
    Route::post('/client-services/{client_service}/retry-provisioning', [AdminClientServiceController::class, 'retryProvisioning'])
        ->name('client-services.retryProvisioning');

    // Rutas para Facturas de Administración
    Route::resource('invoices', AdminInvoiceController::class);
    Route::post('/invoices/{invoice}/activate-services', [AdminInvoiceController::class, 'activateServices'])->name('invoices.activateServices');
    Route::post('/invoices/{invoice}/manual-transaction', [AdminInvoiceController::class, 'storeManualTransaction'])->name('invoices.storeManualTransaction');

    Route::resource('products', AdminProductController::class);

    // Route::get('/products/{product}/pricings', [SearchController::class, 'getProductPricings'])->name('products.search.pricings');
    Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');

    Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');

    Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');

    Route::get('/products/{product}/pricings', [AdminClientServiceController::class, 'getProductPricings'])->name('products.getPricings');

    Route::get('/project-progress', function () {
        // Aquí no necesitas pasar datos porque el componente los tiene o los carga de localStorage
        return Inertia::render('Admin/ProjectProgress');
    })->middleware(['auth', 'verified', /* tu middleware de admin si es necesario */])->name('project.progress');

    // Route for storing transactions for an invoice
    Route::post('/invoices/{invoice}/transactions', [AdminTransactionController::class, 'store'])->name('invoices.transactions.store');

    // Route for listing transactions
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions/{transaction}/confirm', [AdminTransactionController::class, 'confirm'])->name('transactions.confirm');
    Route::post('transactions/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('transactions.reject');
    // Ruta para confirmar manualmente un pago
    Route::post('/transactions/confirm-manual', [AdminTransactionController::class, 'confirmManualPayment'])->name('transactions.confirmManualPayment');

    // Payment Methods Route
    Route::resource('payment-methods', AdminPaymentMethodController::class);
    // Product Types Route
    Route::resource('product-types', AdminProductTypeController::class);
});



// Rutas para el Panel de Revendedor
Route::middleware(['auth', 'verified', 'role.reseller'])->prefix('reseller-panel')->name('reseller.')->group(function () {
    // Dashboard del revendedor
    Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

    // CRUD de Clientes para el revendedor
    Route::get('/clients', [ResellerClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ResellerClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ResellerClientController::class, 'store'])->name('clients.store');
    // Aquí añadirías las rutas para edit, update, show, destroy de clientes por el revendedor
});


// Rutas para el área de cliente
Route::prefix('client')->name('client.')->middleware(['auth', 'verified'])->group(function () { // Added 'verified' middleware
  // Ruta para el dashboard de cliente
  Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard'); // Route for client dashboard

  // Checkout Route
  Route::get('/checkout/product/{product}', [ClientCheckoutController::class, 'showProductCheckoutPage'])->name('checkout.product');
  Route::post('/checkout/product/{product}/submit', [ClientCheckoutController::class, 'submitProductCheckout'])->name('checkout.submit');


  // Rutas de Recurso para la gestión de servicios de cliente
  Route::resource('services', ClientServiceController::class); // Consolidated service routes using resource including index

  // Rutas para la gestión de facturas de cliente
  Route::resource('invoices', App\Http\Controllers\Client\ClientInvoiceController::class)->except(['create', 'store', 'edit', 'update', 'destroy']); // Use resource for invoices, exclude non-client actions
  Route::post('/invoices/{invoice}/pay-with-balance', [ App\Http\Controllers\Client\ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');

  // Manual Payment Routes for Client
  Route::get('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'showPaymentForm'])->name('invoices.manualPayment.create');
  Route::post('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'processManualPayment'])->name('invoices.manualPayment.store');
  Route::post('/invoices/{invoice}/cancel-payment-report', [App\Http\Controllers\Client\ClientInvoiceController::class, 'cancelPaymentReport'])->name('invoices.cancelPaymentReport');


  // Simulated payment route (if you keep it for other gateways)
  Route::post('/invoices/{invoice}/pay', [ClientInvoicePaymentController::class, 'store'])->name('invoices.payment.store');

  // Old PayPal Routes (Commented out)
  // Route::get('/paypal/checkout/{invoice}', [PayPalController::class, 'checkout'])->name('paypal.checkout');
  // Route::get('/paypal/return/{invoice}', [PayPalController::class, 'success'])->name('paypal.success'); // Changed from PayerReturn to success
  // Route::get('/paypal/cancel/{invoice}', [PayPalController::class, 'cancel'])->name('paypal.cancel');

  // New PayPal Payment Routes
  Route::get('/paypal/payment/create/{invoice}', [PayPalPaymentController::class, 'createPayment'])->name('paypal.payment.create');
  Route::get('/paypal/payment/success', [PayPalPaymentController::class, 'handlePaymentSuccess'])->name('paypal.payment.success');
  Route::get('/paypal/payment/cancel', [PayPalPaymentController::class, 'handlePaymentCancel'])->name('paypal.payment.cancel');

  // Rutas para la gestión de transacciones de cliente
  Route::get('/transactions', [App\Http\Controllers\Client\ClientTransactionController::class, 'index'])->name('transactions.index');

  // Rutas para Adición de Fondos
  Route::get('/add-funds', [ClientFundAdditionController::class, 'showAddFundsForm'])->name('funds.create');
  Route::post('/add-funds', [ClientFundAdditionController::class, 'processFundAddition'])->name('funds.store');

  // Client Fund Addition with PayPal
  Route::post('/funds/paypal/initiate', [ClientFundAdditionController::class, 'initiatePayPalPayment'])
      ->name('funds.paypal.initiate');

  Route::get('/funds/paypal/success', [ClientFundAdditionController::class, 'handlePayPalSuccess'])
      ->name('funds.paypal.success');

  Route::get('/funds/paypal/cancel', [ClientFundAdditionController::class, 'handlePayPalCancel'])
      ->name('funds.paypal.cancel');

  // Rutas para el listado de productos para clientes (handled by ClientDashboardController)
  Route::get('/products', [ClientDashboardController::class, 'listProducts'])->name('products.index');

  // Rutas adicionales para servicios de cliente (no incluidas en resource por defecto)
  // These routes point to the ClientServiceController methods
  Route::post('/services/{service}/request-cancellation', [ClientServiceController::class, 'requestCancellation'])->name('services.requestCancellation');
  Route::get('/services/{service}/upgrade-downgrade-options', [ClientServiceController::class, 'showUpgradeDowngradeOptions'])->name('services.showUpgradeDowngradeOptions');
  Route::post('/services/{service}/process-upgrade-downgrade', [ClientServiceController::class, 'processUpgradeDowngrade'])->name('services.processUpgradeDowngrade');
  Route::post('/services/{service}/request-renewal', [ClientServiceController::class, 'requestRenewal'])->name('services.requestRenewal');

    // Rutas para la gestión de facturas de cliente
    Route::get('/invoices', [App\Http\Controllers\Client\ClientInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\Client\ClientInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/pay-with-balance', [App\Http\Controllers\Client\ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');

    // Manual Payment Routes (already defined above, ensure correct placement relative to other invoice payment routes)
    // Route::get('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'showPaymentForm'])->name('client.invoices.manualPayment.create');
    // Route::post('/invoices/{invoice}/manual-payment', [ClientManualPaymentController::class, 'processManualPayment'])->name('client.invoices.manualPayment.store');

    // Simulated payment route (if you keep it for other gateways)
    // Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Client\InvoicePaymentController::class, 'store'])->name('invoices.payment.store'); // This is duplicated if PayPal routes are primary for /pay

    // PayPal Routes (already defined above, ensure correct placement and naming for client context)
    // Route::get('/paypal/checkout/{invoice}', [PayPalController::class, 'checkout'])->name('client.paypal.checkout');
    // Route::get('/paypal/return/{invoice}', [PayPalController::class, 'success'])->name('client.paypal.success');
    // Route::get('/paypal/cancel/{invoice}', [PayPalController::class, 'cancel'])->name('client.paypal.cancel');

    // Rutas para la gestión de transacciones de cliente
    Route::get('/transactions', [App\Http\Controllers\Client\ClientTransactionController::class, 'index'])->name('transactions.index');

    // Rutas para Adición de Fondos (already defined above, ensure correct placement and naming)
    // Route::get('/add-funds', [ClientFundAdditionController::class, 'showAddFundsForm'])->name('client.funds.create');
    // Route::post('/add-funds', [ClientFundAdditionController::class, 'processFundAddition'])->name('client.funds.store');

    // Rutas para el listado de productos para clientes
    Route::get('/products', [ClientDashboardController::class, 'listProducts'])->name('products.index');

    // Ruta para solicitar cancelación de servicio
    Route::post('/services/{service}/request-cancellation', [ClientServiceController::class, 'requestCancellation'])->name('services.requestCancellation');

    // Ruta para mostrar opciones de upgrade/downgrade de servicio
    Route::get('/services/{service}/upgrade-downgrade-options', [ClientServiceController::class, 'showUpgradeDowngradeOptions'])->name('services.showUpgradeDowngradeOptions');

    // Ruta para procesar el cambio de plan de servicio
    Route::post('/services/{service}/process-upgrade-downgrade', [ClientServiceController::class, 'processUpgradeDowngrade'])->name('services.processUpgradeDowngrade');

    // Ruta para solicitar renovación de servicio
    Route::post('/services/{service}/request-renewal', [ClientServiceController::class, 'requestRenewal'])->name('services.requestRenewal');

  // Ruta para actualizar la contraseña de un servicio
  Route::post('/services/{service}/update-password', [ClientServiceController::class, 'updatePassword'])->name('services.updatePassword');

  // Ruta para calcular prorrateo de servicio
  Route::post('/services/{service}/calculate-proration', [\App\Http\Controllers\Client\ClientServiceController::class, 'calculateProration'])
      ->name('services.calculateProration');

    // Ruta para solicitar la cancelación de una nueva orden/factura
    Route::post('/invoices/{invoice}/cancel-new-order', [App\Http\Controllers\Client\ClientInvoiceController::class, 'requestInvoiceCancellation'])->name('invoices.cancelNewOrder');
});

// Comment out or remove the existing landing page route
// Route::get('/', function () {
//     // Opcionalmente, podrías cargar services.json aquí y pasarlo como prop
//      $servicesData = json_decode(file_get_contents(public_path('data/services.json')), true);
//     return Inertia::render('LandingPage', [ // Cambiado de 'Welcome' a 'LandingPage'
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//         // Pass serviceData directly here if not using controller
//         // 'serviceData' => $servicesData,
//     ]);
// });

// New Landing Page Routes
Route::get('/', [LandingPageController::class, 'showHome'])->name('landing.home');
Route::get('/servicios/{categorySlug}', [LandingPageController::class, 'showCategory'])->name('landing.category');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
