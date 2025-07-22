<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Class ImpersonationService
 * 
 * Servicio para el manejo de impersonación de usuarios
 * Permite a los administradores acceder al panel de clientes
 */
class ImpersonationService
{
    private const SESSION_KEY = 'impersonating_admin_id';

    /**
     * Iniciar impersonación de un cliente
     */
    public function impersonateClient(User $client): array
    {
        try {
            // Verificar que el usuario actual es admin
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ];
            }

            // Verificar que el target es un cliente
            if ($client->role !== 'client') {
                return [
                    'success' => false,
                    'message' => 'Solo se puede impersonar a clientes'
                ];
            }

            // Verificar que el cliente está activo
            if ($client->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'No se puede impersonar a un cliente inactivo'
                ];
            }

            $admin = Auth::user();

            // TODO: Validar que si el admin es un reseller,
            // solo pueda acceder a clientes de su propiedad
            if ($admin->role === 'reseller' && $client->reseller_id !== $admin->id) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para acceder al panel de este cliente'
                ];
            }

            // Guardar el ID del admin original en la sesión
            Session::put(self::SESSION_KEY, $admin->id);

            // Hacer login como el cliente
            Auth::login($client);

            // Log de auditoría
            Log::info('Impersonación iniciada', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'admin_name' => $admin->name,
                'client_id' => $client->id,
                'client_email' => $client->email,
                'client_name' => $client->name,
                'timestamp' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return [
                'success' => true,
                'message' => 'Impersonación iniciada exitosamente',
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email
                    ],
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en ImpersonationService::impersonateClient', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'admin_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al iniciar impersonación'
            ];
        }
    }

    /**
     * Terminar impersonación y volver al admin original
     */
    public function stopImpersonation(): array
    {
        try {
            // Verificar que hay una sesión de impersonación activa
            if (!Session::has(self::SESSION_KEY)) {
                return [
                    'success' => false,
                    'message' => 'No hay una sesión de impersonación activa'
                ];
            }

            $adminId = Session::get(self::SESSION_KEY);
            $currentClient = Auth::user();

            // Buscar el admin original
            $admin = User::find($adminId);

            if (!$admin || !in_array($admin->role, ['admin', 'reseller'])) {
                // Limpiar la sesión y forzar logout
                Session::forget(self::SESSION_KEY);
                Auth::logout();

                Log::error('Admin original no encontrado durante stop impersonation', [
                    'admin_id' => $adminId,
                    'client_id' => $currentClient?->id
                ]);

                return [
                    'success' => false,
                    'message' => 'No se pudo encontrar el administrador original',
                    'force_logout' => true
                ];
            }

            // Log de auditoría
            Log::info('Impersonación terminada', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'admin_name' => $admin->name,
                'client_id' => $currentClient->id,
                'client_email' => $currentClient->email,
                'client_name' => $currentClient->name,
                'timestamp' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Limpiar la sesión de impersonación
            Session::forget(self::SESSION_KEY);

            // Hacer login como el admin original
            Auth::login($admin);

            return [
                'success' => true,
                'message' => 'Has vuelto al panel de administración',
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'role' => $admin->role
                    ],
                    'previous_client' => [
                        'id' => $currentClient->id,
                        'name' => $currentClient->name,
                        'email' => $currentClient->email
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en ImpersonationService::stopImpersonation', [
                'error' => $e->getMessage(),
                'admin_id' => Session::get(self::SESSION_KEY),
                'current_user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al terminar impersonación'
            ];
        }
    }

    /**
     * Verificar si hay una impersonación activa
     */
    public function isImpersonating(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Obtener información de la impersonación activa
     */
    public function getImpersonationInfo(): ?array
    {
        if (!$this->isImpersonating()) {
            return null;
        }

        try {
            $adminId = Session::get(self::SESSION_KEY);
            $admin = User::find($adminId);
            $currentUser = Auth::user();

            if (!$admin || !$currentUser) {
                return null;
            }

            return [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role
                ],
                'current_user' => [
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                    'email' => $currentUser->email,
                    'role' => $currentUser->role
                ],
                'started_at' => Session::get('impersonation_started_at', now())
            ];

        } catch (\Exception $e) {
            Log::error('Error en ImpersonationService::getImpersonationInfo', [
                'error' => $e->getMessage(),
                'admin_id' => Session::get(self::SESSION_KEY)
            ]);

            return null;
        }
    }

    /**
     * Validar permisos de impersonación
     */
    public function canImpersonate(User $admin, User $target): array
    {
        $errors = [];

        // Verificar que el admin tiene permisos
        if (!in_array($admin->role, ['admin', 'reseller'])) {
            $errors[] = 'Solo administradores y resellers pueden impersonar usuarios';
        }

        // Verificar que el target es un cliente
        if ($target->role !== 'client') {
            $errors[] = 'Solo se puede impersonar a clientes';
        }

        // Verificar que el target está activo
        if ($target->status !== 'active') {
            $errors[] = 'No se puede impersonar a un usuario inactivo';
        }

        // Verificar permisos de reseller
        if ($admin->role === 'reseller' && $target->reseller_id !== $admin->id) {
            $errors[] = 'Los resellers solo pueden impersonar a sus propios clientes';
        }

        // Verificar que no se está impersonando a sí mismo
        if ($admin->id === $target->id) {
            $errors[] = 'No puedes impersonarte a ti mismo';
        }

        return [
            'can_impersonate' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtener historial de impersonaciones (para auditoría)
     */
    public function getImpersonationHistory(User $admin, int $days = 30): array
    {
        try {
            // Esta funcionalidad requeriría una tabla de auditoría
            // Por ahora, retornamos un array vacío
            // En el futuro, se podría implementar con una tabla audit_logs

            Log::info('Solicitud de historial de impersonación', [
                'admin_id' => $admin->id,
                'days' => $days
            ]);

            return [
                'success' => true,
                'data' => [],
                'message' => 'Funcionalidad de historial pendiente de implementación'
            ];

        } catch (\Exception $e) {
            Log::error('Error en ImpersonationService::getImpersonationHistory', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener historial de impersonación'
            ];
        }
    }

    /**
     * Limpiar sesiones de impersonación huérfanas
     */
    public function cleanupOrphanedSessions(): int
    {
        try {
            // Esta funcionalidad requeriría acceso a la tabla de sesiones
            // Por ahora, solo loggeamos la acción
            
            Log::info('Limpieza de sesiones de impersonación huérfanas iniciada');

            // En el futuro, se podría implementar limpieza de sesiones
            // donde el admin original ya no existe o está inactivo

            return 0;

        } catch (\Exception $e) {
            Log::error('Error en ImpersonationService::cleanupOrphanedSessions', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }
}
