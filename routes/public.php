<?php

// ✅ ARQUITECTURA HEXAGONAL - Controladores migrados a Infrastructure
use App\Domains\Orders\Infrastructure\Http\Controllers\Public\CheckoutController;

// ⚠️ PENDIENTES DE MIGRAR - Aún en estructura Laravel tradicional
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Here is where you can register public routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. These routes are accessible
| to all users without authentication.
|
*/

// Sales Landing Page Routes (New conversion-focused landing)
Route::get('/', [App\Http\Controllers\SalesLandingController::class, 'showHome'])->name('sales.home');
Route::get('/sales', [App\Http\Controllers\SalesLandingController::class, 'showHome'])->name('sales.index');
Route::get('/para/{useCaseSlug}', [App\Http\Controllers\SalesLandingController::class, 'showUseCase'])->name('sales.usecase');
Route::post('/empezar-compra', [App\Http\Controllers\SalesLandingController::class, 'startPurchase'])->name('sales.start-purchase');

// Specific Use Case Pages
Route::get('/para-educadores', [App\Http\Controllers\SalesLandingController::class, 'showEducators'])->name('sales.educators');
Route::get('/para-emprendedores', [App\Http\Controllers\SalesLandingController::class, 'showEntrepreneurs'])->name('sales.entrepreneurs');
Route::get('/para-profesionales', [App\Http\Controllers\SalesLandingController::class, 'showProfessionals'])->name('sales.professionals');
Route::get('/para-negocios', [App\Http\Controllers\SalesLandingController::class, 'showSmallBusiness'])->name('sales.small-business');
Route::get('/para-diseñadores-web', [App\Http\Controllers\SalesLandingController::class, 'showWebDesigners'])->name('sales.web-designers');
Route::get('/technical-resellers', [App\Http\Controllers\SalesLandingController::class, 'showTechnicalResellers'])->name('sales.technical-resellers');

// Public Checkout Flow (for non-authenticated users)
Route::prefix('checkout')->name('public.checkout.')->group(function () {
    // Ruta para iniciar flujo público con contexto de prueba
    Route::get('/start', function () {
        session([
            'purchase_context' => [
                'use_case'     => 'entrepreneurs',
                'plan'         => 'professional',
                'product_slug' => 'hosting-web',
                'source'       => 'direct_access',
            ],
        ]);
        return redirect()->route('public.checkout.domain');
    })->name('start');

    // Rutas hexagonales usando CheckoutController
    Route::get('/domain', [CheckoutController::class, 'showDomainVerification'])->name('domain');
    Route::post('/domain', [CheckoutController::class, 'processDomainVerification'])->name('domain.process');
    Route::get('/register', [CheckoutController::class, 'showRegistration'])->name('register');
    Route::post('/register', [CheckoutController::class, 'processRegistration'])->name('register.process');
    Route::get('/payment', [CheckoutController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [CheckoutController::class, 'processPayment'])->name('payment.process');
});

// Public Registration with Sales Context (HEXAGONAL)
Route::get('/registro-con-contexto', [CheckoutController::class, 'showRegistrationWithContext'])->name('public.register.with-context');
Route::post('/registro-con-contexto', [CheckoutController::class, 'processRegistrationWithContext'])->name('public.register.with-context.process');

// Special email verification route for purchase flow (HEXAGONAL)
Route::get('/verify-purchase-email/{id}/{hash}', [CheckoutController::class, 'verifyPurchaseEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify.purchase');

// Old Technical Landing Page Routes (for reference/backup)
Route::get('/servicios-tecnicos', [LandingPageController::class, 'showHome'])->name('landing.home');
Route::get('/servicios-tecnicos/{categorySlug}', [LandingPageController::class, 'showCategory'])->name('landing.category');
