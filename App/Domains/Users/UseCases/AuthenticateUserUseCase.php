<?php

namespace App\Domains\Users\UseCases;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Use Case para autenticación de usuarios
 * 
 * Aplica arquitectura hexagonal - contiene lógica de negocio pura
 * Maneja autenticación, rate limiting y logging de seguridad
 */
class AuthenticateUserUseCase
{
    /**
     * Autenticar usuario con credenciales
     */
    public function execute(array $credentials, bool $remember = false): array
    {
        try {
            $email = $credentials['email'];
            $password = $credentials['password'];

            // Verificar rate limiting
            $rateLimitKey = 'login.' . $email;
            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                
                Log::warning('Demasiados intentos de login', [
                    'email' => $email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'retry_after' => $seconds
                ]);

                return [
                    'success' => false,
                    'message' => "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
                    'user' => null,
                    'retry_after' => $seconds
                ];
            }

            // Buscar usuario por email
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->recordFailedAttempt($rateLimitKey, $email, 'Usuario no encontrado');
                
                return [
                    'success' => false,
                    'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                    'user' => null
                ];
            }

            // Verificar contraseña
            if (!Hash::check($password, $user->password)) {
                $this->recordFailedAttempt($rateLimitKey, $email, 'Contraseña incorrecta');
                
                return [
                    'success' => false,
                    'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                    'user' => null
                ];
            }

            // Verificar estado del usuario
            if ($user->status !== 'active') {
                $this->recordFailedAttempt($rateLimitKey, $email, 'Usuario inactivo');
                
                $statusMessages = [
                    'inactive' => 'Tu cuenta está inactiva. Contacta al administrador.',
                    'suspended' => 'Tu cuenta está suspendida. Contacta al administrador.',
                ];

                return [
                    'success' => false,
                    'message' => $statusMessages[$user->status] ?? 'Tu cuenta no está disponible.',
                    'user' => null
                ];
            }

            // Autenticación exitosa
            Auth::login($user, $remember);
            RateLimiter::clear($rateLimitKey);

            // Actualizar información de último login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            Log::info('Login exitoso', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'remember' => $remember
            ]);

            return [
                'success' => true,
                'message' => 'Autenticación exitosa',
                'user' => $user,
                'redirect_url' => $this->getRedirectUrl($user)
            ];

        } catch (\Exception $e) {
            Log::error('Error en autenticación', [
                'email' => $credentials['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'user' => null
            ];
        }
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout(): array
    {
        try {
            $user = Auth::user();
            
            if ($user) {
                Log::info('Logout exitoso', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip()
                ]);
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return [
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error en logout', [
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cerrar sesión'
            ];
        }
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Obtener usuario autenticado actual
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Verificar permisos del usuario
     */
    public function hasPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Lógica básica de permisos por rol
        switch ($permission) {
            case 'admin.access':
                return $user->role === 'admin';
                
            case 'reseller.access':
                return in_array($user->role, ['admin', 'reseller']);
                
            case 'client.access':
                return in_array($user->role, ['admin', 'reseller', 'client']);
                
            case 'user.manage':
                return $user->role === 'admin';
                
            case 'client.manage':
                return in_array($user->role, ['admin', 'reseller']);
                
            default:
                return false;
        }
    }

    /**
     * Verificar si el usuario puede impersonar a otro
     */
    public function canImpersonate(User $target): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Solo admins y resellers pueden impersonar
        if (!in_array($user->role, ['admin', 'reseller'])) {
            return false;
        }

        // Solo se puede impersonar a clientes
        if ($target->role !== 'client') {
            return false;
        }

        // El target debe estar activo
        if ($target->status !== 'active') {
            return false;
        }

        // Los resellers solo pueden impersonar a sus clientes
        if ($user->role === 'reseller' && $target->reseller_id !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Registrar intento fallido de autenticación
     */
    private function recordFailedAttempt(string $rateLimitKey, string $email, string $reason): void
    {
        RateLimiter::hit($rateLimitKey, 300); // 5 minutos de bloqueo

        Log::warning('Intento de login fallido', [
            'email' => $email,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'attempts' => RateLimiter::attempts($rateLimitKey)
        ]);
    }

    /**
     * Obtener URL de redirección según el rol del usuario
     */
    private function getRedirectUrl(User $user): string
    {
        switch ($user->role) {
            case 'admin':
                return route('admin.dashboard');
                
            case 'reseller':
                return route('reseller.dashboard');
                
            case 'client':
            default:
                return route('client.dashboard');
        }
    }

    /**
     * Validar formato de credenciales
     */
    public function validateCredentials(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
        }

        if (empty($credentials['password'])) {
            $errors[] = 'La contraseña es requerida';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtener estadísticas de autenticación
     */
    public function getAuthStats(): array
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('status', 'active')->count();
            $recentLogins = User::where('last_login_at', '>=', now()->subDays(7))->count();
            $onlineUsers = User::where('last_login_at', '>=', now()->subMinutes(15))->count();

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'recent_logins' => $recentLogins,
                'online_users' => $onlineUsers,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de autenticación', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_users' => 0,
                'active_users' => 0,
                'recent_logins' => 0,
                'online_users' => 0,
            ];
        }
    }
}
