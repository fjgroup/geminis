<?php

use App\Http\Controllers\Api\DomainApiController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Webhook\PayPalWebhookController;
// use App\Http\Controllers\Client\PayPalController; // Old controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// PayPal Webhook
Route::post('/webhooks/paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');

// Pricing Calculator API
Route::prefix('pricing')->group(function () {
    Route::post('/calculate-product', [\App\Http\Controllers\Api\PricingController::class, 'calculateProductPrice'])->name('api.pricing.calculate-product');
    Route::post('/calculate-cart', [\App\Http\Controllers\Api\PricingController::class, 'calculateCartTotal'])->name('api.pricing.calculate-cart');
    Route::get('/admin-price/{product_id}', [\App\Http\Controllers\Api\PricingController::class, 'getAdminProductPrice'])->name('api.pricing.admin-price');
    Route::get('/base-resources/{product_id}', [\App\Http\Controllers\Api\PricingController::class, 'getProductBaseResources'])->name('api.pricing.base-resources');
    Route::post('/bulk-pricing', [\App\Http\Controllers\Api\PricingController::class, 'getBulkPricing'])->name('api.pricing.bulk');
});

// Product API Routes (moved from web.php)
Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/main-services', [ApiProductController::class, 'getMainServices'])->name('mainServices');
    Route::get('/ssl-certificates', [ApiProductController::class, 'getSslCertificates'])->name('sslCertificates');
    Route::get('/software-licenses', [ApiProductController::class, 'getSoftwareLicenses'])->name('softwareLicenses');
    Route::get('/domain-registration', [ApiProductController::class, 'getDomainRegistrationProducts'])->name('domainRegistration');
    // Example for generic by type:
    // Route::get('/by-type/{typeIdentifier}', [ApiProductController::class, 'getProductsByType'])->name('byType');
});

// Domain API Routes (moved from web.php)
Route::prefix('domain')->name('api.domain.')->group(function () {
    Route::get('/check-availability', [DomainApiController::class, 'checkAvailability'])->name('checkAvailability');
    Route::get('/tld-pricing', [DomainApiController::class, 'getTldPricingInfo'])->name('tldPricingInfo');
});
