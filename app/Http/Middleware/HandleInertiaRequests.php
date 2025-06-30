<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
// Added Log facade

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
        return [
             ...parent::share($request), // Usar el operador de propagación
            'auth'                => [
                'user' => $request->user(), // Volver a la forma simple de pasar el objeto user completo
            ],
            'flash'               => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn()   => $request->session()->get('error'),
            ],
            // Información de impersonation para mostrar botón "Volver al Admin"
            'impersonating_admin' => fn() => $request->session()->has('impersonating_admin_id'),
            // Puedes añadir Ziggy aquí si lo necesitas y no está en parent::share
            // 'ziggy' => fn () => [
            //     ...(new Ziggy)->toArray(),
            //     'location' => $request->route()->uri(),
            // ],
        ];
    }
}
