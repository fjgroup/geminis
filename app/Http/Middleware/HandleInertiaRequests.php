<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
