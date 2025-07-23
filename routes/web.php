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

use App\Domains\Users\Infrastructure\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Ruta de prueba simple
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'FJGroup Application is running with Hexagonal Architecture!',
        'timestamp' => now(),
        'architecture' => 'DDD + Hexagonal + SOLID'
    ]);
});

// Profile routes for regular users (clients) - mantener ambas rutas para compatibilidad
Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta principal para clientes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas alternativas con prefijo client
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
