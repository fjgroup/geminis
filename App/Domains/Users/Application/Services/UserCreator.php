<?php

namespace App\Domains\Users\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Users\DataTransferObjects\CreateUserDTO;
use App\Domains\Users\Events\UserCreated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Servicio especializado para creación de usuarios
 * 
 * Aplica Single Responsibility Principle - solo se encarga de crear usuarios
 * Ubicado en Application layer según arquitectura hexagonal
 */
class UserCreator
{
    /**
     * Crear un nuevo usuario
     */
    public function create(CreateUserDTO $userData): array
    {
        try {
            DB::beginTransaction();

            // Validar que el email no exista
            if (User::where('email', $userData->email)->exists()) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado',
                    'user' => null
                ];
            }

            // Crear el usuario
            $user = User::create([
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => Hash::make($userData->password),
                'role' => $userData->role ?? 'client',
                'reseller_id' => $userData->reseller_id,
                'status' => $userData->status ?? 'active',
                'language_code' => $userData->language_code ?? config('app.locale', 'es'),
                'currency_code' => $userData->currency_code ?? 'USD',
                'company_name' => $userData->company_name,
                'phone' => $userData->phone,
                'country' => $userData->country,
                'address' => $userData->address,
                'city' => $userData->city,
                'state' => $userData->state,
                'postal_code' => $userData->postal_code,
                'tax_id' => $userData->tax_id,
            ]);

            // Crear perfil de reseller si es necesario
            if ($user->role === 'reseller') {
                $this->createResellerProfile($user, $userData);
            }

            // Disparar evento de usuario creado
            event(new UserCreated($user));

            DB::commit();

            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user' => $user
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creando usuario', [
                'error' => $e->getMessage(),
                'email' => $userData->email,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
                'user' => null
            ];
        }
    }

    /**
     * Crear usuario cliente por reseller
     */
    public function createClientByReseller(CreateUserDTO $userData, int $resellerId): array
    {
        // Validar que el reseller existe y está activo
        $reseller = User::where('id', $resellerId)
            ->where('role', 'reseller')
            ->where('status', 'active')
            ->first();

        if (!$reseller) {
            return [
                'success' => false,
                'message' => 'Reseller no encontrado o inactivo',
                'user' => null
            ];
        }

        // Asignar el reseller al usuario
        $userData->reseller_id = $resellerId;
        $userData->role = 'client';

        return $this->create($userData);
    }

    /**
     * Crear usuario admin
     */
    public function createAdmin(CreateUserDTO $userData): array
    {
        // Solo admins pueden crear otros admins
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return [
                'success' => false,
                'message' => 'Solo administradores pueden crear otros administradores',
                'user' => null
            ];
        }

        $userData->role = 'admin';
        $userData->status = 'active';

        return $this->create($userData);
    }

    /**
     * Crear usuario reseller
     */
    public function createReseller(CreateUserDTO $userData): array
    {
        // Solo admins pueden crear resellers
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return [
                'success' => false,
                'message' => 'Solo administradores pueden crear resellers',
                'user' => null
            ];
        }

        $userData->role = 'reseller';
        $userData->status = 'active';

        return $this->create($userData);
    }

    /**
     * Crear perfil de reseller
     */
    private function createResellerProfile(User $user, CreateUserDTO $userData): void
    {
        // TODO: Implementar cuando exista modelo ResellerProfile
        // Por ahora solo logueamos que se necesita crear el perfil
        Log::info('Perfil de reseller pendiente de crear', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Validar datos de usuario antes de crear
     */
    public function validateUserData(CreateUserDTO $userData): array
    {
        $errors = [];

        // Validar email único
        if (User::where('email', $userData->email)->exists()) {
            $errors[] = 'El email ya está registrado';
        }

        // Validar formato de email
        if (!filter_var($userData->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
        }

        // Validar longitud de contraseña
        if (strlen($userData->password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        // Validar rol válido
        $validRoles = ['admin', 'reseller', 'client'];
        if ($userData->role && !in_array($userData->role, $validRoles)) {
            $errors[] = 'Rol no válido';
        }

        // Validar estado válido
        $validStatuses = ['active', 'inactive', 'suspended'];
        if ($userData->status && !in_array($userData->status, $validStatuses)) {
            $errors[] = 'Estado no válido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Crear usuario desde registro público
     */
    public function createFromPublicRegistration(array $registrationData): array
    {
        $userData = new CreateUserDTO([
            'name' => $registrationData['name'],
            'email' => $registrationData['email'],
            'password' => $registrationData['password'],
            'role' => 'client',
            'status' => 'active',
            'language_code' => config('app.locale', 'es'),
            'currency_code' => 'USD',
            'company_name' => $registrationData['company_name'] ?? null,
            'phone' => $registrationData['phone'] ?? null,
            'country' => $registrationData['country'] ?? null,
        ]);

        return $this->create($userData);
    }

    /**
     * Obtener estadísticas de creación de usuarios
     */
    public function getCreationStats(): array
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('status', 'active')->count();
            $clientsCount = User::where('role', 'client')->count();
            $resellersCount = User::where('role', 'reseller')->count();
            $adminsCount = User::where('role', 'admin')->count();
            $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'clients_count' => $clientsCount,
                'resellers_count' => $resellersCount,
                'admins_count' => $adminsCount,
                'recent_users' => $recentUsers,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de usuarios', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_users' => 0,
                'active_users' => 0,
                'clients_count' => 0,
                'resellers_count' => 0,
                'admins_count' => 0,
                'recent_users' => 0,
            ];
        }
    }
}
