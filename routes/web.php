<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ConfigurableOptionGroupController;
use App\Http\Controllers\Reseller\ResellerClientController;
use App\Http\Controllers\Admin\ConfigurableOptionController;
use App\Http\Controllers\Admin\ClientServiceController; // Añadir esta línea
use App\Http\Controllers\Admin\OrderController; // Añadir esta línea para el controlador de Admin
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Client\OrderController as ClientOrderController; // Renombrar el controlador de cliente para evitar conflicto
use App\Http\Controllers\Admin\InvoiceController; // Añadir esta línea para el controlador de Admin
use App\Http\Controllers\Admin\TransactionController; // Added TransactionController import


// Rutas para la administración
// Aplicamos el middleware 'admin' para proteger estas rutas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);

    Route::resource('configurable-option-groups', ConfigurableOptionGroupController::class)->except(['show']); // Show no se usa

    // Rutas anidadas para las opciones dentro de un grupo
    Route::resource('configurable-option-groups.options', ConfigurableOptionController::class)->shallow()->except(['index', 'show', 'create', 'edit']);

    // Rutas para Client Services
    Route::resource('client-services', ClientServiceController::class);

    // Rutas para Órdenes de Administración
    Route::resource('orders', OrderController::class);

    // Rutas para Facturas de Administración
    Route::resource('invoices', InvoiceController::class);

    Route::resource('products', AdminProductController::class);

    // Route::get('/products/{product}/pricings', [SearchController::class, 'getProductPricings'])->name('products.search.pricings');
    Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');

    Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');

    Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');

    Route::get('/products/{product}/pricings', [ClientServiceController::class, 'getProductPricings'])->name('products.getPricings');

    Route::get('/project-progress', function () {
        // Aquí no necesitas pasar datos porque el componente los tiene o los carga de localStorage
        return Inertia::render('Admin/ProjectProgress');
    })->middleware(['auth', 'verified', /* tu middleware de admin si es necesario */])->name('project.progress');

    // Route for storing transactions for an invoice
    Route::post('/invoices/{invoice}/transactions', [TransactionController::class, 'store'])->name('invoices.transactions.store');

    // Route for listing transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // Order Execution Routes
    Route::post('/orders/{order}/start-execution', [OrderController::class, 'startExecution'])->name('orders.startExecution');
    Route::post('/orders/{order}/complete-execution', [OrderController::class, 'completeExecution'])->name('orders.completeExecution');

    // Order Cancellation Approval Route
    Route::post('/orders/{order}/approve-cancellation', [OrderController::class, 'approveCancellationRequest'])->name('orders.approveCancellation');
});



// Rutas para el Panel de Revendedor
Route::middleware(['auth', 'verified', 'role.reseller'])->prefix('reseller-panel')->name('reseller.')->group(function () {
    // Dashboard del revendedor (ejemplo, necesitarás crear este controlador)
    // Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

    // CRUD de Clientes para el revendedor
    Route::get('/clients', [ResellerClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ResellerClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ResellerClientController::class, 'store'])->name('clients.store');
    // Aquí añadirías las rutas para edit, update, show, destroy de clientes por el revendedor
});


// Rutas para el área de cliente
Route::prefix('client')->name('client.')->middleware(['auth'])->group(function () {
    // Panel de cliente y lista de servicios
    Route::get('/services', [ClientDashboardController::class, 'index'])->name('services.index');

    // Rutas placeholder para la gestión de servicios
    Route::get('/services/create', function () {
        return Inertia::render('Client/Services/Create');
    })->name('services.create');

    Route::get('/services/{service}', function ($service) {
        // En una implementación real, aquí cargarías los datos del servicio
        return Inertia::render('Client/Services/Show', ['serviceId' => $service]);
    })->name('services.show');

    Route::get('/services/{service}/edit', function ($service) {
        // En una implementación real, aquí cargarías los datos del servicio
        return Inertia::render('Client/Services/Edit', ['serviceId' => $service]);
    })->name('services.edit');

    // Placeholder para eliminar servicio
    Route::delete('/services/{service}', function ($service) {
        // En una implementación real, aquí manejarías la eliminación
        return back()->with('success', 'Servicio eliminado (placeholder)');
    })->name('services.destroy');

    // Rutas para la creación de órdenes
    Route::get('/order/product/{product}', [OrderController::class, 'showOrderForm'])->name('order.showOrderForm');
    Route::post('/order/place/{product}', [OrderController::class, 'placeOrder'])->name('order.placeOrder');

    // Rutas para la gestión de órdenes de cliente
    Route::get('/orders', [ClientOrderController::class, 'index'])->name('orders.index');
    // Route for client to cancel their own order if it's pending payment
    Route::delete('/orders/{order}/cancel-prepayment', [ClientOrderController::class, 'cancelPrePaymentOrder'])->name('orders.cancelPrePayment');
    // Route for client to request cancellation for a paid order pending execution
    Route::post('/orders/{order}/request-cancellation', [ClientOrderController::class, 'requestPostPaymentCancellation'])->name('orders.requestPostPaymentCancellation');

    // Rutas para la gestión de facturas de cliente
    Route::get('/invoices', [\App\Http\Controllers\Client\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('invoices.show');
    // Route for simulated invoice payment
    Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Client\InvoicePaymentController::class, 'store'])->name('invoices.payment.store');
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
