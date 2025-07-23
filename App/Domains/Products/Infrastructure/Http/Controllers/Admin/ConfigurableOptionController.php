<?php
namespace App\Domains\Products\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Products\Infrastructure\Http\Requests\StoreConfigurableOptionRequest;
use App\Domains\Products\Infrastructure\Http\Requests\UpdateConfigurableOptionRequest;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionGroup;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// No necesitamos Inertia aquí ya que las acciones redirigen, se eliminó la importación de Inertia

class ConfigurableOptionController extends Controller
{
    // Los métodos index() y create() no se utilizan ya que las opciones
    // se gestionan directamente desde la vista del grupo

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

    // Los métodos show() y edit() no se utilizan ya que las opciones
    // se gestionan directamente desde la vista del grupo

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateConfigurableOptionRequest $request, ConfigurableOption $option): RedirectResponse {

        // TODO: Add authorization check

        $validatedData = $request->validated();
        $groupId       = $option->group_id;

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

    /**
     * Update pricings for a configurable option.
     */
    public function updatePricings(Request $request, ConfigurableOption $option): RedirectResponse
    {
        $request->validate([
            'pricings'                    => 'required|array',
            'pricings.*.billing_cycle_id' => 'required|exists:billing_cycles,id',
            'pricings.*.price'            => 'required|numeric|min:0',
            'pricings.*.setup_fee'        => 'nullable|numeric|min:0',
            'pricings.*.currency_code'    => 'required|string|max:3',
            'pricings.*.is_active'        => 'boolean',
        ]);

        foreach ($request->pricings as $pricingData) {
            ConfigurableOptionPricing::updateOrCreate(
                [
                    'configurable_option_id' => $option->id,
                    'billing_cycle_id'       => $pricingData['billing_cycle_id'],
                ],
                [
                    'price'         => $pricingData['price'],
                    'setup_fee'     => $pricingData['setup_fee'] ?? 0,
                    'currency_code' => $pricingData['currency_code'],
                    'is_active'     => $pricingData['is_active'] ?? true,
                ]
            );
        }

        return back()->with('success', 'Precios actualizados exitosamente.');
    }
}
