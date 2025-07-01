<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ResellerProfile;
use App\Models\User;
use App\Traits\AuditLogging;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    use AuditLogging;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $currentUser = Auth::user();

        // Build query based on user role
        $query = User::latest();

        if ($currentUser->role === 'admin') {
            // Admins can see all users
            $query->with('reseller');
        } elseif ($currentUser->role === 'reseller') {
            // Resellers can only see their own clients (not other resellers or admins)
            $query->where('reseller_id', $currentUser->id)
                ->where('role', 'client') // Solo clientes, no otros resellers o admins
                ->with('reseller');

            Log::info('Reseller accessing users list', [
                'reseller_id'    => $currentUser->id,
                'reseller_email' => $currentUser->email,
                'filter_applied' => 'reseller_id = ' . $currentUser->id . ' AND role = client',
            ]);
        }

        $users = $query->paginate(10)
            ->through(fn($user) => [ // Mapea solo los campos que necesitas
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'role'                 => $user->role,
                'status'               => $user->status,
                'company_name'         => $user->company_name,
                'reseller_name'        => $user->reseller_id && $user->reseller ? $user->reseller->name : 'N/A',
                'created_at_formatted' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : null,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'       => $users,
            'userContext' => [
                'role'       => $currentUser->role,
                'isReseller' => $currentUser->role === 'reseller',
                'isAdmin'    => $currentUser->role === 'admin',
            ],
            // 'filters' => request()->all('search', 'role', 'status'), // Si tienes filtros
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name', 'company_name']);

        // Aquí podríamos pasar datos adicionales si fueran necesarios (ej: listas para selects)
        // $roles = [['value' => 'admin', 'label' => 'Admin'], ...];
        return Inertia::render('Admin/Users/Create', [
            'resellers' => $resellers->map(fn($reseller) => [
                'value' => $reseller->id,
                'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : ""),
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
                                                 // La autorización principal se maneja en StoreUserRequest
        $this->authorize('create', User::class); // Es bueno tenerlo aquí también por claridad
        $validatedData = $request->validated();
        // Asignar el usuario creado a la variable $user
        $user = User::create([
            'name'           => $validatedData['name'],
            'email'          => $validatedData['email'],
            'password'       => Hash::make($validatedData['password']),
            'role'           => $validatedData['role'],
            'reseller_id'    => $validatedData['reseller_id'] ?? null,
            'company_name'   => $validatedData['company_name'] ?? null,
            'phone_number'   => $validatedData['phone_number'] ?? null,
            'address_line1'  => $validatedData['address_line1'] ?? null,
            'address_line2'  => $validatedData['address_line2'] ?? null,
            'city'           => $validatedData['city'] ?? null,
            'state_province' => $validatedData['state_province'] ?? null,
            'postal_code'    => $validatedData['postal_code'] ?? null,
            'country'        => $validatedData['country'] ?? null,
            'status'         => $validatedData['status'],
            'language_code'  => $validatedData['language_code'] ?? 'es',
            'currency_code'  => $validatedData['currency_code'] ?? 'USD',
        ]);

        // Crear ResellerProfile si el rol es reseller y se proporcionan datos
        if ($user->role === 'reseller' && $request->has('reseller_profile')) {
            $profileData = $request->input('reseller_profile');
            // La validación de $profileData debería estar en StoreUserRequest
            $user->resellerProfile()->create($profileData);
        }

        // Log de auditoría
        $this->logAdminAction('user_created', $user, [
            'role'                 => $user->role,
            'has_reseller_profile' => $user->role === 'reseller' && $request->has('reseller_profile'),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
                                           // La autorización principal se maneja en UpdateUserRequest
        $this->authorize('update', $user); // Es bueno tenerlo aquí también por claridad
        $validatedData = $request->validated();

        $updateData = collect($validatedData)->except('password')->toArray();

        if (! empty($validatedData['password'])) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        // Preparamos los datos base para la actualización
        $finalUpdateData = array_merge($updateData, [
            'company_name'   => $validatedData['company_name'] ?? null,
            'phone_number'   => $validatedData['phone_number'] ?? null,
            'address_line1'  => $validatedData['address_line1'] ?? null,
            'address_line2'  => $validatedData['address_line2'] ?? null,
            'city'           => $validatedData['city'] ?? null,
            'state_province' => $validatedData['state_province'] ?? null,
            'postal_code'    => $validatedData['postal_code'] ?? null,
            'country'        => $validatedData['country'] ?? null,
            'status'         => $validatedData['status'],
            'language_code'  => $validatedData['language_code'] ?? 'es',
            'currency_code'  => $validatedData['currency_code'] ?? 'USD',
        ]);

        // Lógica para reseller_id al actualizar
        if ($validatedData['role'] === 'client') {
            $finalUpdateData['reseller_id'] = $validatedData['reseller_id'] ?? $user->reseller_id; // Mantener si no se envía, o permitir cambiar
        } elseif ($validatedData['role'] === 'reseller') {
            $finalUpdateData['reseller_id'] = null; // Un revendedor no tiene un reseller_id padre
        } else {                                // admin
            $finalUpdateData['reseller_id'] = null;
        }
        $user->update($finalUpdateData);

        // Gestionar ResellerProfile
        if ($user->role === 'reseller' && $request->has('reseller_profile')) {
            $profileData = $request->input('reseller_profile');
            // La validación de $profileData debería estar en UpdateUserRequest
            $user->resellerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        } elseif ($validatedData['role'] !== 'reseller' && $user->resellerProfile) {
            // Si el rol cambia de reseller a otro y existe un perfil, eliminarlo.
            $user->resellerProfile->delete();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user); // O 'view', $user si tienes un método view en la policy

        if ($user->role === 'reseller') {
            $user->load('resellerProfile');
        }
        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name', 'company_name']);

        return Inertia::render('Admin/Users/Edit', [
            'user'      => $user,
            'resellers' => $resellers->map(fn($reseller) => [
                'value' => $reseller->id,
                'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : ""),
            ]),
        ]);
    }

    /** Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Guardar información para auditoría antes de eliminar
        $userInfo = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        $user->delete();

        // Log de auditoría
        $this->logAdminAction('user_deleted', null, $userInfo);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
