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

use App\Http\Controllers\Admin\AdminClientServiceController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInvoiceController;

// Importaciones de controladores
use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProductTypeController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\ConfigurableOptionController;
use App\Http\Controllers\Admin\ConfigurableOptionGroupController;
use App\Http\Controllers\Admin\DiscountPercentageController;
use App\Http\Controllers\Admin\ProductController;
// use App\Http\Controllers\Admin\AdminOrderController; // Removed
use App\Http\Controllers\Admin\UserController as AdminUserController;   // Import the admin invoice controller
use App\Http\Controllers\Api\DomainApiController;                       // Import the admin transaction controller
use App\Http\Controllers\Api\ProductController as ApiProductController; // Import the admin client service controller
use App\Http\Controllers\Client\ClientCheckoutController;
use App\Http\Controllers\Client\ClientDashboardController;
// use App\Http\Controllers\Client\ClientOrderController; // Removed
use App\Http\Controllers\Client\ClientFundAdditionController;                               // Added
use App\Http\Controllers\Client\ClientManualPaymentController;                              // Import the client service controller
use App\Http\Controllers\Client\ClientServiceController;                                    // Import the client invoice payment controller and alias it
use App\Http\Controllers\Client\InvoicePaymentController as ClientInvoicePaymentController; // Import the client transaction controller and alias it
use App\Http\Controllers\Client\PayPalPaymentController;                                    // Import the fund addition controller
use App\Http\Controllers\LandingPageController;                                             // Import the new PayPalPaymentController
use App\Http\Controllers\ProfileController;                                                 // Import the new ProductTypeController
use App\Http\Controllers\Reseller\ResellerClientController;                                 // Import the new LandingPageController
use App\Http\Controllers\Reseller\ResellerDashboardController;
use Illuminate\Support\Facades\Route; // Import ApiProductController
use Inertia\Inertia;

// Import DomainApiController

// Rutas para la administración
// Aplicamos múltiples capas de seguridad para proteger estas rutas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin', 'admin.security', 'input.sanitize', 'inject.context'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);
    Route::resource('products', ProductController::class);

    // Ruta para calcular precios de productos
    Route::post('products/{product}/calculate-pricing', [ProductController::class, 'calculatePricing'])
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

    // Rutas para Client Services
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
    })->middleware(['auth', 'verified' /* tu middleware de admin si es necesario */])->name('project.progress');

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

    // Discount Percentages Route
    Route::resource('discount-percentages', DiscountPercentageController::class);
});

// Ruta especial para volver al panel de admin desde impersonation
// Esta ruta NO está dentro del grupo admin para permitir que los clientes impersonados puedan usarla
Route::post('/admin/stop-impersonation', [AdminClientServiceController::class, 'stopImpersonation'])
    ->middleware(['auth', 'verified'])
    ->name('admin.stop-impersonation');

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
Route::prefix('client')->name('client.')->middleware(['auth', 'verified'])->group(function () { // Restored security middleware
                                                                                                    // Ruta para el dashboard de cliente
    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');                // Route for client dashboard

    // Checkout Route (Legacy or direct product link)
    Route::get('/checkout/product/{product}', [ClientCheckoutController::class, 'showProductCheckoutPage'])->name('checkout.product');
    // Route::post('/checkout/product/{product}/submit', [ClientCheckoutController::class, 'submitProductCheckout'])->name('checkout.submit'); // Replaced by new flow submit

                                                                                                                       // New Checkout Flow Routes
    Route::post('/checkout/submit', [ClientCheckoutController::class, 'submitCurrentOrder'])->name('checkout.submit'); // New submit for cart-based checkout
    Route::get('/checkout/select-domain', [ClientCheckoutController::class, 'showSelectDomainPage'])->name('checkout.selectDomain');
    Route::get('/checkout/select-services', [ClientCheckoutController::class, 'showSelectServicesPage'])->name('checkout.selectServices');
    Route::get('/checkout/confirm', [ClientCheckoutController::class, 'showConfirmOrderPage'])->name('checkout.confirm');

                                                                 // Rutas de Recurso para la gestión de servicios de cliente
    Route::resource('services', ClientServiceController::class); // Consolidated service routes using resource including index

                                                                                                                                                       // Rutas para la gestión de facturas de cliente
    Route::resource('invoices', App\Http\Controllers\Client\ClientInvoiceController::class)->except(['create', 'store', 'edit', 'update', 'destroy']); // Use resource for invoices, exclude non-client actions
    Route::post('/invoices/{invoice}/pay-with-balance', [App\Http\Controllers\Client\ClientInvoiceController::class, 'payWithBalance'])->name('invoices.payWithBalance');

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

    // Rutas para el listado de productos para clientes (DESHABILITADO - usar checkout en su lugar)
    // Route::get('/products', [ClientDashboardController::class, 'listProducts'])->name('products.index');

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

    // Rutas para el listado de productos para clientes (DESHABILITADO - usar checkout en su lugar)
    // Route::get('/products', [ClientDashboardController::class, 'listProducts'])->name('products.index');

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

    // Rutas para el Carrito de Compras
    // Cart routes with rate limiting
    Route::prefix('cart')->name('cart.')->middleware(['cart.ratelimit:20,1'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\ClientCartController::class, 'getCart'])->name('get');
        Route::post('/add', [\App\Http\Controllers\Client\ClientCartController::class, 'addItem'])->name('add');
        Route::post('/update', [\App\Http\Controllers\Client\ClientCartController::class, 'updateItem'])->name('update');
        Route::post('/remove', [\App\Http\Controllers\Client\ClientCartController::class, 'removeItem'])->name('remove');
        Route::post('/clear', [\App\Http\Controllers\Client\ClientCartController::class, 'clearCart'])->name('clear');
        Route::post('/account/set-domain', [\App\Http\Controllers\Client\ClientCartController::class, 'setDomainForAccount'])->name('account.setDomain');
        Route::post('/account/remove-domain', [\App\Http\Controllers\Client\ClientCartController::class, 'removeDomainFromAccount'])->name('account.removeDomain');
        Route::post('/account/set-primary-service', [\App\Http\Controllers\Client\ClientCartController::class, 'setPrimaryServiceForAccount'])->name('account.setPrimaryService');
        Route::post('/account/remove-primary-service', [\App\Http\Controllers\Client\ClientCartController::class, 'removePrimaryServiceFromAccount'])->name('account.removePrimaryService');
    });
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

    Route::get('/domain', [App\Http\Controllers\PublicCheckoutController::class, 'showDomainVerification'])->name('domain');
    Route::post('/domain', [App\Http\Controllers\PublicCheckoutController::class, 'processDomainVerification'])->name('domain.process');
    Route::get('/register', [App\Http\Controllers\PublicCheckoutController::class, 'showRegistration'])->name('register');
    Route::post('/register', [App\Http\Controllers\PublicCheckoutController::class, 'processRegistration'])->name('register.process');
    Route::get('/payment', [App\Http\Controllers\PublicCheckoutController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [App\Http\Controllers\PublicCheckoutController::class, 'processPayment'])->name('payment.process');
});

// Public Registration with Sales Context
Route::get('/registro-con-contexto', [App\Http\Controllers\PublicCheckoutController::class, 'showRegistrationWithContext'])->name('public.register.with-context');
Route::post('/registro-con-contexto', [App\Http\Controllers\PublicCheckoutController::class, 'processRegistrationWithContext'])->name('public.register.with-context.process');

// Special email verification route for purchase flow
Route::get('/verify-purchase-email/{id}/{hash}', [App\Http\Controllers\PublicCheckoutController::class, 'verifyPurchaseEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify.purchase');

// Reseller Panel Routes (uses same admin panel but with reseller context)
Route::prefix('reseller')->name('reseller.')->middleware(['auth', 'verified', 'admin.or.reseller', 'inject.context'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->names([
        'index'   => 'users.index',
        'create'  => 'users.create',
        'store'   => 'users.store',
        'show'    => 'users.show',
        'edit'    => 'users.edit',
        'update'  => 'users.update',
        'destroy' => 'users.destroy',
    ]);
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

// Old Technical Landing Page Routes (for reference/backup)
Route::get('/servicios-tecnicos', [LandingPageController::class, 'showHome'])->name('landing.home');
Route::get('/servicios-tecnicos/{categorySlug}', [LandingPageController::class, 'showCategory'])->name('landing.category');

// API Routes for fetching products (used by checkout pages)
Route::prefix('api/products')->name('api.products.')->group(function () {
    Route::get('/main-services', [ApiProductController::class, 'getMainServices'])->name('mainServices');
    Route::get('/ssl-certificates', [ApiProductController::class, 'getSslCertificates'])->name('sslCertificates');
    Route::get('/software-licenses', [ApiProductController::class, 'getSoftwareLicenses'])->name('softwareLicenses');
    Route::get('/domain-registration', [ApiProductController::class, 'getDomainRegistrationProducts'])->name('domainRegistration');
    // Example for generic by type:
    // Route::get('/by-type/{typeIdentifier}', [ApiProductController::class, 'getProductsByType'])->name('byType');
});

// API Routes for Domain Operations (NameSilo, etc.)
Route::prefix('api/domain')->name('api.domain.')->group(function () {
    Route::get('/check-availability', [DomainApiController::class, 'checkAvailability'])->name('checkAvailability');
    Route::get('/tld-pricing', [DomainApiController::class, 'getTldPricingInfo'])->name('tldPricingInfo');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Ruta temporal para actualizar servicios
Route::get('/update-services', function () {
    $services = \App\Models\ClientService::with(['product', 'productPricing.billingCycle'])
        ->whereHas('product')
        ->whereHas('productPricing')
        ->get();

    $pricingCalculator = app(\App\Services\PricingCalculatorService::class);
    $updated           = 0;
    $results           = [];

    foreach ($services as $service) {
        try {
            $priceCalculation = $pricingCalculator->calculateProductPrice(
                $service->product_id,
                $service->billing_cycle_id,
                []
            );

            $newBillingAmount = $priceCalculation['total'];
            $oldBillingAmount = $service->billing_amount;

            $results[] = [
                'id'         => $service->id,
                'product'    => $service->product->name,
                'old_amount' => $oldBillingAmount,
                'new_amount' => $newBillingAmount,
                'updated'    => false,
            ];

            if (abs($newBillingAmount - $oldBillingAmount) > 0.01) {
                $service->billing_amount = $newBillingAmount;
                $service->save();
                $results[count($results) - 1]['updated'] = true;
                $updated++;
            }

        } catch (\Exception $e) {
            $results[] = [
                'id'      => $service->id,
                'product' => $service->product->name ?? 'Unknown',
                'error'   => $e->getMessage(),
            ];
        }
    }

    return response()->json([
        'message'        => 'Services updated successfully',
        'updated_count'  => $updated,
        'total_services' => $services->count(),
        'results'        => $results,
    ]);
})->middleware(['auth', 'admin']);

// Ruta temporal para crear usuarios (ELIMINAR EN PRODUCCIÓN)
Route::get('/create-test-users', function () {
    try {
        // Crear usuario administrador
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@fjgroupca.com'],
            [
                'name'              => 'Administrador',
                'email'             => 'admin@fjgroupca.com',
                'password'          => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role'              => 'admin',
                'company_name'      => 'Fj Group CA',
                'phone'             => '+58 412 8172337',
                'country'           => 'VE',
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario cliente de prueba
        $client = \App\Models\User::updateOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name'              => 'Cliente Prueba',
                'email'             => 'cliente@test.com',
                'password'          => \Illuminate\Support\Facades\Hash::make('cliente123'),
                'role'              => 'client',
                'company_name'      => 'Empresa Test',
                'phone'             => '+58 412 1234567',
                'country'           => 'VE',
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario reseller de prueba
        $reseller = \App\Models\User::updateOrCreate(
            ['email' => 'reseller@test.com'],
            [
                'name'              => 'Reseller Prueba',
                'email'             => 'reseller@test.com',
                'password'          => \Illuminate\Support\Facades\Hash::make('reseller123'),
                'role'              => 'reseller',
                'company_name'      => 'Reseller Company',
                'phone'             => '+58 412 7654321',
                'country'           => 'VE',
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'success'   => true,
            'message'   => '✅ Usuarios creados exitosamente!',
            'users'     => [
                'admin'    => 'admin@fjgroupca.com / admin123',
                'client'   => 'cliente@test.com / cliente123',
                'reseller' => 'reseller@test.com / reseller123',
            ],
            'login_url' => route('login'),
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
});

// Ruta temporal para verificar estado del usuario (ELIMINAR EN PRODUCCIÓN)
Route::get('/check-user-status', function () {
    if (! \Illuminate\Support\Facades\Auth::check()) {
        return response()->json([
            'authenticated' => false,
            'message'       => 'Usuario no autenticado',
        ]);
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    return response()->json([
        'authenticated'               => true,
        'user'                        => [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'created_at'        => $user->created_at,
        ],
        'can_access_client_dashboard' => ! is_null($user->email_verified_at),
        'dashboard_url'               => route('client.dashboard'),
    ]);
});

// Ruta temporal para dashboard de cliente sin verificación (ELIMINAR EN PRODUCCIÓN)
Route::get('/client-dashboard-temp', function () {
    if (! \Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('login');
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user->role !== 'client') {
        return redirect()->route('login');
    }

    return \Inertia\Inertia::render('Client/Dashboard', [
        'user'    => $user,
        'message' => 'Dashboard temporal - Email verificado: ' . ($user->email_verified_at ? 'Sí' : 'No'),
    ]);
})->name('client.dashboard.temp');

// Ruta temporal para logout rápido (ELIMINAR EN PRODUCCIÓN)
Route::get('/logout-quick', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login')->with('message', 'Sesión cerrada exitosamente');
})->name('logout.quick');

require __DIR__ . '/auth.php';
