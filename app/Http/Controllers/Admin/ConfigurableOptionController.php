<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup; // Para obtener el grupo padre
use App\Http\Requests\Admin\StoreConfigurableOptionRequest;
use App\Http\Requests\Admin\UpdateConfigurableOptionRequest;
use Illuminate\Http\RedirectResponse;
// No necesitamos Inertia aquí ya que las acciones redirigen

class ConfigurableOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // No se usa directamente, las opciones se listan en la vista del grupo
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No se usa directamente, el formulario se integra en la vista del grupo
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfigurableOptionRequest $request, ConfigurableOptionGroup $configurableOptionGroup): RedirectResponse
    {
        // TODO: Add authorization check
        $configurableOptionGroup->configurableOptions()->create($request->validated());

        return redirect()->route('admin.configurable-option-groups.edit', $configurableOptionGroup)
            ->with('success', 'Opción configurable creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConfigurableOption $configurableOption)
    {
        // No se usa directamente
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConfigurableOption $configurableOption)
    {
        // No se usa directamente, el formulario se integra en la vista del grupo
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigurableOptionRequest $request, ConfigurableOption $configurableOption): RedirectResponse
    {
        // TODO: Add authorization check
        $configurableOption->update($request->validated());

        // Redirigir a la página de edición del grupo padre
        return redirect()->route('admin.configurable-option-groups.edit', $configurableOption->group_id)
            ->with('success', 'Opción configurable actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConfigurableOption $configurableOption)
    {
        // TODO: Add authorization check
        $groupId = $configurableOption->group_id;
        $configurableOption->delete();

        // Redirigir a la página de edición del grupo padre
        return redirect()->route('admin.configurable-option-groups.edit', $groupId)
            ->with('success', 'Opción configurable eliminada exitosamente.');
    }
}
