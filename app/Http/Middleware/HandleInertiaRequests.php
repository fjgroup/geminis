<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log; // Added Log facade

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        Log::debug('Data being shared by HandleInertiaRequests for URI: ' . $request->getRequestUri(), [
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_role' => $request->user() ? $request->user()->role : null,
            'user_data_full' => $request->user() ? $request->user()->toArray() : null, // Log completo del usuario temporalmente
            'session_all' => $request->session()->all() // Para ver qué hay en la sesión, incluyendo flash
        ]);

        return array_merge(parent::share($request), [ // Usar array_merge es una forma común
            'auth' => [
                'user' => $request->user() ? [
                    // Solo exponer los campos necesarios del usuario
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    // añadir otros campos si son necesarios globalmente
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                // Puedes añadir otras claves de flash que uses, ej. 'warning', 'info'
            ],
            // Podrías añadir 'ziggy' aquí también si lo usas y no está ya en parent::share
            // 'ziggy' => fn () => [
            //     ...(new Ziggy)->toArray(),
            //     'location' => $request->route()->uri(),
            // ],
        ]);
    }
}
