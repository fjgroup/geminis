<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Client\PayPalController; // Old controller
use App\Http\Controllers\Webhook\PayPalWebhookController; // New controller

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
