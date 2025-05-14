<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOptionGroup;
use App\Models\Product; // Para el dropdown de productos
use App\Http\Requests\Admin\StoreConfigurableOptionGroupRequest;
use App\Http\Requests\Admin\UpdateConfigurableOptionGroupRequest;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class ConfigurableOptionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Add authorization check, e.g., $this->authorize('viewAny', ConfigurableOptionGroup::class);

        $groups = ConfigurableOptionGroup::with('product:id,name') // Carga el producto asociado
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(10)
            ->through(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'product_name' => $group->product ? $group->product->name : 'Global',
                'product_id' => $group->product_id,
                'display_order' => $group->display_order,
            ]);

        return Inertia::render('Admin/ConfigurableOptionGroups/Index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Add authorization check, e.g., $this->authorize('create', ConfigurableOptionGroup::class);

        $products = Product::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Admin/ConfigurableOptionGroups/Create', [
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfigurableOptionGroupRequest $request): RedirectResponse
    {
        // Authorization is handled by StoreConfigurableOptionGroupRequest
        ConfigurableOptionGroup::create($request->validated());
        return redirect()->route('admin.configurable-option-groups.index')
            ->with('success', 'Grupo de opciones configurable creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConfigurableOptionGroup $configurableOptionGroup)
    {
        // TODO: Add authorization check, e.g., $this->authorize('view', $configurableOptionGroup);
        // Typically not used for CRUDs with Inertia, redirecting to edit is common.
        return redirect()->route('admin.configurable-option-groups.edit', $configurableOptionGroup);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConfigurableOptionGroup $configurableOptionGroup)
    {
        // TODO: Add authorization check, e.g., $this->authorize('update', $configurableOptionGroup);

        $products = Product::orderBy('name')->get(['id', 'name']);
        // Cargar producto si existe y también las opciones configurables asociadas
        $configurableOptionGroup->load(['product:id,name', 'configurableOptions' => function ($query) {
            $query->orderBy('display_order')->orderBy('name');
        }]);

        return Inertia::render('Admin/ConfigurableOptionGroups/Edit', [
            'group' => [
                'id' => $configurableOptionGroup->id,
                'name' => $configurableOptionGroup->name,
                'description' => $configurableOptionGroup->description,
                'product_id' => $configurableOptionGroup->product_id,
                'display_order' => $configurableOptionGroup->display_order,
                // Mapear las opciones para pasarlas a la vista
                'options' => $configurableOptionGroup->configurableOptions->map(fn ($option) => [
                    'id' => $option->id,
                    'name' => $option->name,
                    'value' => $option->value,
                    'display_order' => $option->display_order,
                    'group_id' => $option->group_id, // Incluir el group_id
                ])->all(), // Asegúrate de usar ->all() o ->toArray() si es una colección
            ],
            'products' => $products,
            // 'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object) [], // Para pasar errores de validación de opciones
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigurableOptionGroupRequest $request, ConfigurableOptionGroup $configurableOptionGroup): RedirectResponse
    {
        // Authorization is handled by UpdateConfigurableOptionGroupRequest
        $configurableOptionGroup->update($request->validated());
        return redirect()->route('admin.configurable-option-groups.index')
            ->with('success', 'Grupo de opciones configurable actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConfigurableOptionGroup $configurableOptionGroup)
    {
        // TODO: Add authorization check, e.g., $this->authorize('delete', $configurableOptionGroup);
        // Considerar si hay opciones configurables asociadas y cómo manejarlas (onDelete cascade ya está en la migración de opciones)
        $configurableOptionGroup->delete();
        return redirect()->route('admin.configurable-option-groups.index')
            ->with('success', 'Grupo de opciones configurable eliminado exitosamente.');
    }
}
