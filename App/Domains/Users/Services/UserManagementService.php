<?php

namespace App\Domains\Users\Services;

use App\Models\ResellerProfile;
use App\Domains\Users\Models\User;
use App\Traits\AuditLogging;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class UserManagementService
 *
 * Servicio para la gestión administrativa de usuarios
 * Centraliza toda la lógica de negocio relacionada con la administración de usuarios
 */
class UserManagementService
{
    use AuditLogging;

    /**
     * Obtener usuarios con filtros y paginación
     */
    public function getUsers(array $filters = [], int $perPage = 15): array
    {
        try {
            $currentUser = Auth::user();
            $query = User::with(['reseller', 'resellerProfile']);

            // Aplicar filtros basados en el rol del usuario actual
            if ($currentUser->role === 'admin') {
                // Admins pueden ver todos los usuarios
                $query->latest();
            } elseif ($currentUser->role === 'reseller') {
                // Resellers solo pueden ver sus propios clientes
                $query->where('reseller_id', $currentUser->id)
                      ->where('role', 'client')
                      ->latest();

                Log::info('Reseller accediendo a lista de usuarios', [
                    'reseller_id' => $currentUser->id,
                    'filter_applied' => 'reseller_id = ' . $currentUser->id . ' AND role = client'
                ]);
            }

            // Aplicar filtros adicionales
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('company_name', 'LIKE', "%{$search}%");
                });
            }

            if (!empty($filters['role'])) {
                $query->where('role', $filters['role']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['reseller_id'])) {
                $query->where('reseller_id', $filters['reseller_id']);
            }

            $users = $query->paginate($perPage);

            // Formatear datos para la vista
            $users->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'company_name' => $user->company_name,
                    'reseller_name' => $user->reseller ? $user->reseller->name : 'N/A',
                    'created_at_formatted' => $user->created_at?->format('d/m/Y H:i'),
                ];
            });

            return [
                'success' => true,
                'data' => $users
            ];

        } catch (\Exception $e) {
            Log::error('Error en UserManagementService::getUsers', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Error obteniendo usuarios',
                'data' => collect()->paginate($perPage)
            ];
        }
    }

    /**
     * Crear un nuevo usuario
     */
    public function createUser(array $data): array
    {
        try {
            DB::beginTransaction();

            // Extraer datos del perfil de reseller
            $resellerProfileData = $data['reseller_profile'] ?? null;
            unset($data['reseller_profile']);

            // Hashear contraseña
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Crear usuario
            $user = User::create($data);

            // Crear perfil de reseller si es necesario
            if ($user->role === 'reseller' && $resellerProfileData) {
                $this->createResellerProfile($user, $resellerProfileData);
            }

            DB::commit();

            // Log de auditoría
            $this->logAdminAction('user_created', $user, [
                'role' => $user->role,
                'has_reseller_profile' => $user->role === 'reseller' && $resellerProfileData
            ]);

            Log::info('UserManagementService - Usuario creado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en UserManagementService::createUser', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Error creando usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar un usuario existente
     */
    public function updateUser(User $user, array $data): array
    {
        try {
            DB::beginTransaction();

            // Extraer datos del perfil de reseller
            $resellerProfileData = $data['reseller_profile'] ?? null;
            unset($data['reseller_profile']);

            // Hashear contraseña si se proporciona
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Actualizar usuario
            $user->update($data);

            // Gestionar perfil de reseller
            $this->manageResellerProfile($user, $resellerProfileData);

            DB::commit();

            // Log de auditoría
            $this->logAdminAction('user_updated', $user, [
                'updated_fields' => array_keys($data)
            ]);

            Log::info('UserManagementService - Usuario actualizado', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en UserManagementService::updateUser', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Error actualizando usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un usuario
     */
    public function deleteUser(User $user): array
    {
        try {
            // Verificar que no tenga servicios activos
            $activeServices = $user->clientServices()->where('status', 'active')->count();

            if ($activeServices > 0) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar un usuario con servicios activos'
                ];
            }

            // Verificar que no sea el último admin
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return [
                        'success' => false,
                        'message' => 'No se puede eliminar el último administrador'
                    ];
                }
            }

            DB::beginTransaction();

            // Guardar información para auditoría
            $userInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ];

            // Eliminar perfil de reseller si existe
            if ($user->resellerProfile) {
                $user->resellerProfile->delete();
            }

            // Eliminar usuario
            $user->delete();

            DB::commit();

            // Log de auditoría
            $this->logAdminAction('user_deleted', null, $userInfo);

            Log::info('UserManagementService - Usuario eliminado', $userInfo);

            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en UserManagementService::deleteUser', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return [
                'success' => false,
                'message' => 'Error eliminando usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos para formularios
     */
    public function getFormData(): array
    {
        try {
            return [
                'resellers' => User::where('role', 'reseller')
                    ->orderBy('name')
                    ->get(['id', 'name', 'company_name'])
                    ->map(fn($reseller) => [
                        'value' => $reseller->id,
                        'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : '')
                    ]),
                'roles' => [
                    ['value' => 'admin', 'label' => 'Administrador'],
                    ['value' => 'reseller', 'label' => 'Reseller'],
                    ['value' => 'client', 'label' => 'Cliente']
                ],
                'statuses' => [
                    ['value' => 'active', 'label' => 'Activo'],
                    ['value' => 'inactive', 'label' => 'Inactivo']
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en UserManagementService::getFormData', [
                'error' => $e->getMessage()
            ]);

            return [
                'resellers' => [],
                'roles' => [],
                'statuses' => []
            ];
        }
    }

    /**
     * Crear perfil de reseller
     */
    private function createResellerProfile(User $user, array $profileData): void
    {
        $user->resellerProfile()->create([
            'commission_rate' => $profileData['commission_rate'] ?? 0,
            'max_clients' => $profileData['max_clients'] ?? null,
            'allowed_products' => $profileData['allowed_products'] ?? null,
        ]);
    }

    /**
     * Gestionar perfil de reseller
     */
    private function manageResellerProfile(User $user, ?array $profileData): void
    {
        if ($user->role === 'reseller' && $profileData) {
            // Crear o actualizar perfil de reseller
            $user->resellerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'commission_rate' => $profileData['commission_rate'] ?? 0,
                    'max_clients' => $profileData['max_clients'] ?? null,
                    'allowed_products' => $profileData['allowed_products'] ?? null,
                ]
            );
        } elseif ($user->role !== 'reseller' && $user->resellerProfile) {
            // Si el rol cambió de reseller a otro, eliminar el perfil
            $user->resellerProfile->delete();
        }
    }
}
