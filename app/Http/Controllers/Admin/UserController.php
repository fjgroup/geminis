<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $this->authorize('viewAny', User::class); // Descomentar cuando UserPolicy esté lista

        $users = User::latest()
            // ->with('reseller') // Opcional: si necesitas mostrar el nombre del revendedor
            ->paginate(10)
            ->through(fn ($user) => [ // Mapea solo los campos que necesitas
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                // 'company_name' => $user->company_name, // Añadir si se va a mostrar
                // 'reseller_name' => $user->reseller_id && $user->reseller ? $user->reseller->name : 'N/A', // Ejemplo
                'created_at_formatted' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : null,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            // 'filters' => request()->all('search', 'role', 'status'), // Si tienes filtros
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Aquí podríamos pasar datos adicionales si fueran necesarios (ej: listas para selects)
        // $roles = [['value' => 'admin', 'label' => 'Admin'], ...];
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'reseller_id' => $validatedData['reseller_id'] ?? null,
            'company_name' => $validatedData['company_name'] ?? null,
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address_line1' => $validatedData['address_line1'] ?? null,
            'address_line2' => $validatedData['address_line2'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state_province' => $validatedData['state_province'] ?? null,
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country' => $validatedData['country'] ?? null,
            'status' => $validatedData['status'],
            'language_code' => $validatedData['language_code'] ?? 'es',
            'currency_code' => $validatedData['currency_code'] ?? 'USD',
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
        $validatedData = $request->validated();

        $updateData = collect($validatedData)->except('password')->toArray();

        if (!empty($validatedData['password'])) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($updateData + [
            'reseller_id' => $validatedData['reseller_id'] ?? null,
            'company_name' => $validatedData['company_name'] ?? null,
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address_line1' => $validatedData['address_line1'] ?? null,
            'address_line2' => $validatedData['address_line2'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state_province' => $validatedData['state_province'] ?? null,
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country_code' => $validatedData['country_code'] ?? null,
            'status' => $validatedData['status'],
            'language_code' => $validatedData['language_code'] ?? 'es',
            'currency_code' => $validatedData['currency_code'] ?? 'USD',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
