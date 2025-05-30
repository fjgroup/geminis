<?php

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
use App\Http\Controllers\Admin\AdminOrderController; // Añadir esta línea para el controlador de Admin
use App\Http\Controllers\Admin\AdminInvoiceController; // Import the admin invoice controller
use App\Http\Controllers\Admin\AdminTransactionController; // Import the admin transaction controller
use App\Http\Controllers\Admin\AdminClientServiceController; // Import the admin client service controller
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientOrderController; // Import the client order controller
use App\Http\Controllers\Client\ClientServiceController; // Import the client service controller
use App\Http\Controllers\Client\InvoicePaymentController as ClientInvoicePaymentController; // Import the client invoice payment controller and alias it
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController; // Import the client invoice controller and alias it
use App\Http\Controllers\Client\TransactionController as ClientTransactionController; // Import the client transaction controller and alias it
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

    // Rutas para Órdenes de Administración
    Route::resource('orders', AdminOrderController::class);

    // Rutas para Facturas de Administración
    Route::resource('invoices', AdminInvoiceController::class);

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

    // Order Execution Routes
    Route::post('/orders/{order}/start-execution', [AdminOrderController::class, 'startExecution'])->name('orders.startExecution');
    Route::post('/orders/{order}/complete-execution', [AdminOrderController::class, 'completeExecution'])->name('orders.completeExecution');

    // Order Cancellation Approval Route
    Route::post('/orders/{order}/approve-cancellation', [AdminOrderController::class, 'approveCancellationRequest'])->name('orders.approveCancellation');

    // Admin Confirm Payment Route
    Route::post('/orders/{order}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirmPayment');
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
Route::prefix('client')->name('client.')->middleware(['auth'])->group(function () {
  // Ruta para el dashboard de cliente
  Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard'); // Route for client dashboard

  // Rutas de Recurso para la gestión de servicios de cliente
  Route::resource('services', ClientServiceController::class); // Consolidated service routes using resource including index

  // Rutas para la creación de órdenes
  Route::get('/order/product/{product}', [ClientOrderController::class, 'showOrderForm'])->name('order.showOrderForm');
  Route::post('/order/place/{product}', [ClientOrderController::class, 'placeOrder'])->name('order.placeOrder');

  // Rutas para la gestión de órdenes de cliente
  Route::get('/orders', [ClientOrderController::class, 'index'])->name('orders.index');
  Route::get('/orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show'); // Added route for showing a client order
  Route::delete('/orders/{order}/cancel-prepayment', [ClientOrderController::class, 'cancelPrePaymentOrder'])->name('orders.cancelPrePayment');
  Route::post('/orders/{order}/request-cancellation', [ClientOrderController::class, 'requestPostPaymentCancellation'])->name('orders.requestPostPaymentCancellation');
  Route::get('/orders/{order}/edit-order', [ClientOrderController::class, 'editOrderForm'])->name('orders.editOrderForm');
  Route::put('/orders/{order}/update-order', [ClientOrderController::class, 'updateOrder'])->name('orders.updateOrder');

  // Rutas para la gestión de facturas de cliente
  Route::resource('invoices', App\Http\Controllers\Client\ClientInvoiceController::class)->except(['create', 'store', 'edit', 'update', 'destroy']); // Use resource for invoices, exclude non-client actions
  Route::post('/invoices/{invoice}/pay-with-balance', [ App\Http\Controllers\Client\ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');
  Route::post('/invoices/{invoice}/pay', [ClientInvoicePaymentController::class, 'store'])->name('invoices.payment.store');

  // Rutas para la gestión de transacciones de cliente
  Route::get('/transactions', [App\Http\Controllers\Client\ClientTransactionController::class, 'index'])->name('transactions.index');

  // Rutas para el listado de productos para clientes (handled by ClientDashboardController)
  Route::get('/products', [ClientDashboardController::class, 'listProducts'])->name('products.index');

  // Rutas adicionales para servicios de cliente (no incluidas en resource por defecto)
  // These routes point to the ClientServiceController methods
  Route::post('/services/{service}/request-cancellation', [ClientServiceController::class, 'requestCancellation'])->name('services.requestCancellation');
  Route::get('/services/{service}/upgrade-downgrade-options', [ClientServiceController::class, 'showUpgradeDowngradeOptions'])->name('services.showUpgradeDowngradeOptions');
  Route::post('/services/{service}/process-upgrade-downgrade', [ClientServiceController::class, 'processUpgradeDowngrade'])->name('services.processUpgradeDowngrade');
  Route::post('/services/{service}/request-renewal', [ClientServiceController::class, 'requestRenewal'])->name('services.requestRenewal');

    // Rutas para la gestión de órdenes de cliente
    Route::get('/orders', [ClientOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show'); // Added route for showing a client order
    // Route for client to cancel their own order if it's pending payment
    Route::delete('/orders/{order}/cancel-prepayment', [ClientOrderController::class, 'cancelPrePaymentOrder'])->name('orders.cancelPrePayment');
    // Route for client to request cancellation for a paid order pending execution
    Route::post('/orders/{order}/request-cancellation', [ClientOrderController::class, 'requestPostPaymentCancellation'])->name('orders.requestPostPaymentCancellation');

    // Routes for client to edit their pending payment orders
    Route::get('/orders/{order}/edit-order', [ClientOrderController::class, 'editOrderForm'])->name('orders.editOrderForm'); // Changed path to avoid conflict with potential future resource controller for orders
    Route::put('/orders/{order}/update-order', [ClientOrderController::class, 'updateOrder'])->name('orders.updateOrder'); // Changed path for clarity

    // Rutas para la gestión de facturas de cliente
    Route::get('/invoices', [App\Http\Controllers\Client\ClientInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\Client\ClientInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/pay-with-balance', [App\Http\Controllers\Client\ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');
    // Route for simulated invoice payment
    Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Client\InvoicePaymentController::class, 'store'])->name('invoices.payment.store');

    // Rutas para la gestión de transacciones de cliente
    Route::get('/transactions', [App\Http\Controllers\Client\ClientTransactionController::class, 'index'])->name('transactions.index');

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
});


Route::get('/', function () {

    // Opcionalmente, podrías cargar services.json aquí y pasarlo como prop
     $servicesData = json_decode(file_get_contents(public_path('data/services.json')), true);
    return Inertia::render('LandingPage', [ // Cambiado de 'Welcome' a 'LandingPage' 'canLogin' => Route::has('login'), 'canRegister' => Route::has('register'), 'laravelVersion' => Application::VERSION, 'phpVersion' => PHP_VERSION,
    ]);
});




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
