<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reseller Routes
|--------------------------------------------------------------------------
|
| Here is where you can register reseller routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group with reseller-specific middleware.
|
*/

// Reseller Panel Routes (uses same admin panel but with reseller context)
Route::prefix('reseller')->name('reseller.')->middleware(['auth', 'verified', 'admin.or.reseller', 'reseller.security', 'inject.context', 'input.sanitize'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Reseller Profile Routes
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');

    // User management (scoped to reseller's clients)
    Route::resource('users', AdminUserController::class)->names([
        'index'   => 'users.index',
        'create'  => 'users.create',
        'store'   => 'users.store',
        'show'    => 'users.show',
        'edit'    => 'users.edit',
        'update'  => 'users.update',
        'destroy' => 'users.destroy',
    ]);

    // Product management (same as admin for now)
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/calculate-pricing', [ProductController::class, 'calculatePricing'])
        ->name('products.calculate-pricing');
    Route::resource('products', ProductController::class)->names([
        'index'   => 'products.index',
        'create'  => 'products.create',
        'store'   => 'products.store',
        'show'    => 'products.show',
        'edit'    => 'products.edit',
        'update'  => 'products.update',
        'destroy' => 'products.destroy',
    ]);

    // Ruta para calcular precios de productos
    Route::post('products/{product}/calculate-pricing', [ProductController::class, 'calculatePricing'])
        ->name('products.calculate-pricing');
});
