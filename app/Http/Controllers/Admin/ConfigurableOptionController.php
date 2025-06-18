<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup; // Para obtener el grupo padre
use App\Http\Requests\Admin\StoreConfigurableOptionRequest;
use App\Http\Requests\Admin\UpdateConfigurableOptionRequest;
use Illuminate\Http\RedirectResponse;
// No necesitamos Inertia aquí ya que las acciones redirigen, se eliminó la importación de Inertia

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
        $configurableOptionGroup->options()->create($request->validated());

        return redirect()->route('admin.configurable-option-groups.edit', $configurableOptionGroup) // Aquí $configurableOptionGroup es el objeto del grupo, correcto.
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
    public function update(
        UpdateConfigurableOptionRequest $request, ConfigurableOption $option): RedirectResponse
    {
        
        // TODO: Add authorization check

        $validatedData = $request->validated();
        $groupId = $option->group_id;

        // Verificación crucial: Si groupId es null aquí, hay un problema fundamental.
        if (is_null($groupId)) {
            // Esto no debería suceder si la opción tiene un grupo asignado en la BD.
            // Detener y mostrar un error claro.
            // Podrías loguear esto también: \Log::error("ConfigurableOption ID {$option->id} no tiene un group_id.");
            abort(500, "Error en update: La opción configurable ID {$option->id} no tiene un grupo asociado (group_id es null). Verifica los datos en la base de datos o cómo se está cargando el modelo.");
        }

        // Asegurar que display_order no sea null si la columna de BD no lo permite
        // y tiene un default. La validación 'nullable|integer' convierte "" a null.
        if (array_key_exists('display_order', $validatedData) && $validatedData['display_order'] === null) {
            // Asignar el valor por defecto de la BD (0) si el campo se envió vacío/null.
            $validatedData['display_order'] = 0;
        }

        $option->update($validatedData);

        return redirect()->route('admin.configurable-option-groups.edit', ['configurable_option_group' => $groupId])
          ->with('success', 'Opción configurable actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConfigurableOption $option)
    {

        $groupId = $option->group_id;

        // Verificación crucial: Si groupId es null aquí, hay un problema fundamental.
        if (is_null($groupId)) {
            abort(500, "Error en destroy: La opción configurable ID {$option->id} no tiene un grupo asociado (group_id es null). Verifica los datos en la base de datos o cómo se está cargando el modelo.");
        }

        $option->delete();

 
        // Redirigir a la página de edición del grupo padre y corregir mensaje
        return redirect()->route('admin.configurable-option-groups.edit', ['configurable_option_group' => $groupId])
            ->with('success', 'Opción configurable eliminada exitosamente.');
    }
}
