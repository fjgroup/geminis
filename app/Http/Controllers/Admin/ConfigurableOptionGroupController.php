<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConfigurableOptionGroupRequest;
use App\Http\Requests\Admin\UpdateConfigurableOptionGroupRequest;
use App\Models\BillingCycle;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOptionPricing;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ConfigurableOptionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Add authorization check, e.g., $this->authorize('viewAny', ConfigurableOptionGroup::class);

        $groups = ConfigurableOptionGroup::with(['products:id,name', 'options'])
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15)
            ->through(fn($group) => [
                'id'             => $group->id,
                'name'           => $group->name,
                'slug'           => $group->slug,
                'description'    => $group->description,
                'products_names' => $group->products->pluck('name')->join(', ') ?: 'Global',
                'products_count' => $group->products->count(),
                'display_order'  => $group->display_order,
                'is_active'      => $group->is_active,
                'is_required'    => $group->is_required,
                'options_count'  => $group->options->count(),
                'created_at'     => $group->created_at,
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
        $validated = $request->validated();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Remove any product_ids from validated data since we'll handle products separately
        $productIds = $validated['product_ids'] ?? [];
        unset($validated['product_ids']);

        $group = ConfigurableOptionGroup::create($validated);

        // Attach selected products if any
        if (! empty($productIds)) {
            $group->products()->attach($productIds);
        }
        return redirect()->route('admin.configurable-option-groups.index')
            ->with('success', 'Grupo de opciones configurable creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConfigurableOptionGroup $configurableOptionGroup)
    {
        // TODO: Add authorization check, e.g., $this->authorize('view', $configurableOptionGroup);

        $group = $configurableOptionGroup->load([
            'options.pricings.billingCycle',
            'products',
        ]);

        $billingCycles = BillingCycle::ordered()->get(['id', 'name', 'slug']);
        $products      = Product::active()->ordered()->get(['id', 'name']);

        return Inertia::render('Admin/ConfigurableOptionGroups/Show', [
            'group'         => [
                'id'            => $group->id,
                'name'          => $group->name,
                'slug'          => $group->slug,
                'description'   => $group->description,
                'display_order' => $group->display_order,
                'is_active'     => $group->is_active,
                'is_required'   => $group->is_required,
                'created_at'    => $group->created_at,
                'updated_at'    => $group->updated_at,
                'products'      => $group->products,
                'options'       => $group->options->map(fn($option) => [
                    'id'            => $option->id,
                    'name'          => $option->name,
                    'slug'          => $option->slug,
                    'value'         => $option->value,
                    'description'   => $option->description,
                    'option_type'   => $option->option_type,
                    'is_required'   => $option->is_required,
                    'is_active'     => $option->is_active,
                    'min_value'     => $option->min_value,
                    'max_value'     => $option->max_value,
                    'display_order' => $option->display_order,
                    'pricings'      => $option->pricings->map(fn($pricing) => [
                        'id'                 => $pricing->id,
                        'billing_cycle_id'   => $pricing->billing_cycle_id,
                        'billing_cycle_name' => $pricing->billingCycle->name,
                        'price'              => $pricing->price,
                        'setup_fee'          => $pricing->setup_fee,
                        'currency_code'      => $pricing->currency_code,
                        'is_active'          => $pricing->is_active,
                    ]),
                ]),
            ],
            'billingCycles' => $billingCycles,
            'products'      => $products,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConfigurableOptionGroup $configurableOptionGroup)
    {
        // TODO: Add authorization check, e.g., $this->authorize('update', $configurableOptionGroup);

        $configurableOptionGroup->load([
            'options.pricings.billingCycle',
            'products',
        ]);

        $products      = Product::orderBy('name')->get(['id', 'name']);
        $billingCycles = BillingCycle::ordered()->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/ConfigurableOptionGroups/Edit', [
            'group'         => [
                'id'                => $configurableOptionGroup->id,
                'name'              => $configurableOptionGroup->name,
                'slug'              => $configurableOptionGroup->slug,
                'description'       => $configurableOptionGroup->description,
                'display_order'     => $configurableOptionGroup->display_order,
                'is_active'         => $configurableOptionGroup->is_active,
                'is_required'       => $configurableOptionGroup->is_required,
                'selected_products' => $configurableOptionGroup->products->pluck('id')->toArray(),
                'options'           => $configurableOptionGroup->options->map(fn($option) => [
                    'id'            => $option->id,
                    'name'          => $option->name,
                    'slug'          => $option->slug,
                    'value'         => $option->value,
                    'description'   => $option->description,
                    'option_type'   => $option->option_type,
                    'is_required'   => $option->is_required,
                    'is_active'     => $option->is_active,
                    'min_value'     => $option->min_value,
                    'max_value'     => $option->max_value,
                    'display_order' => $option->display_order,
                    'pricings'      => $option->pricings->map(fn($pricing) => [
                        'id'                 => $pricing->id,
                        'billing_cycle_id'   => $pricing->billing_cycle_id,
                        'billing_cycle_name' => $pricing->billingCycle->name,
                        'price'              => $pricing->price,
                        'setup_fee'          => $pricing->setup_fee,
                        'currency_code'      => $pricing->currency_code,
                        'is_active'          => $pricing->is_active,
                    ]),
                ]),
            ],
            'products'      => $products,
            'billingCycles' => $billingCycles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigurableOptionGroupRequest $request, ConfigurableOptionGroup $configurableOptionGroup): RedirectResponse
    {
        // Authorization is handled by UpdateConfigurableOptionGroupRequest
        $validated = $request->validated();

        // Handle products separately
        $productIds = $validated['product_ids'] ?? [];
        unset($validated['product_ids']);

        $configurableOptionGroup->update($validated);

        // Sync products
        $configurableOptionGroup->products()->sync($productIds);

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

    /**
     * Add an option to the group
     */
    public function addOption(Request $request, ConfigurableOptionGroup $configurableOptionGroup)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'slug'                        => 'nullable|string|max:255|unique:configurable_options,slug',
            'value'                       => 'nullable|string|max:255',
            'description'                 => 'nullable|string|max:1000',
            'option_type'                 => 'required|in:dropdown,radio,checkbox,quantity,text',
            'is_required'                 => 'boolean',
            'is_active'                   => 'boolean',
            'min_value'                   => 'nullable|numeric|min:0',
            'max_value'                   => 'nullable|numeric|min:0',
            'display_order'               => 'integer|min:0',
            'pricings'                    => 'array',
            'pricings.*.billing_cycle_id' => 'required|exists:billing_cycles,id',
            'pricings.*.price'            => 'required|numeric|min:0',
            'pricings.*.setup_fee'        => 'nullable|numeric|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['group_id'] = $configurableOptionGroup->id;
        $pricings              = $validated['pricings'] ?? [];
        unset($validated['pricings']);

        $option = ConfigurableOption::create($validated);

        // Create pricings
        foreach ($pricings as $pricing) {
            ConfigurableOptionPricing::create([
                'configurable_option_id' => $option->id,
                'billing_cycle_id'       => $pricing['billing_cycle_id'],
                'price'                  => $pricing['price'],
                'setup_fee'              => $pricing['setup_fee'] ?? 0,
                'currency_code'          => 'USD',
                'is_active'              => true,
            ]);
        }

        return redirect()->route('admin.configurable-option-groups.show', $configurableOptionGroup)
            ->with('success', 'Opción agregada exitosamente.');
    }

    /**
     * Remove an option from the group
     */
    public function removeOption(ConfigurableOptionGroup $configurableOptionGroup, ConfigurableOption $option)
    {
        $option->delete();

        return redirect()->route('admin.configurable-option-groups.show', $configurableOptionGroup)
            ->with('success', 'Opción eliminada exitosamente.');
    }
}
