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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\SearchController;



// Rutas para la administración
// Aplicamos el middleware 'admin' para proteger estas rutas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);

    Route::resource('products', AdminProductController::class);

    Route::resource('configurable-option-groups', ConfigurableOptionGroupController::class)->except(['show']); // Show no se usa
    // Rutas anidadas para las opciones dentro de un grupo
    Route::resource('configurable-option-groups.options', ConfigurableOptionController::class)->shallow()->except(['index', 'show', 'create', 'edit']);

    // Rutas para Client Services
    Route::resource('client-services', ClientServiceController::class);

    Route::get('/products/{product}/pricings', [SearchController::class, 'getProductPricings'])->name('products.search.pricings');

    Route::post('products/{product}/pricing', [AdminProductController::class, 'storePricing'])->name('products.pricing.store');
    Route::put('products/{product}/pricing/{pricing}', [AdminProductController::class, 'updatePricing'])->name('products.pricing.update');
    Route::delete('products/{product}/pricing/{pricing}', [AdminProductController::class, 'destroyPricing'])->name('products.pricing.destroy');
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


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
