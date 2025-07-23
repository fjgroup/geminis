<?php
namespace App\Domains\Products\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionGroup;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(): Response
    {
        $products = Product::with(['productType', 'pricings.billingCycle'])
            ->ordered()
            ->paginate(15);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products->through(fn($product) => [
                'id'                    => $product->id,
                'name'                  => $product->name,
                'slug'                  => $product->slug,
                'description'           => $product->description,
                'product_type'          => $product->productType?->name,
                'status'                => $product->status,
                'is_publicly_available' => $product->is_publicly_available,
                'display_order'         => $product->display_order,
                'pricing_count'         => $product->pricings->count(),
                'min_price'             => $product->pricings->min('price'),
                'created_at'            => $product->created_at,
            ]),
            'filters'  => request()->only(['search', 'status', 'type']),
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        $productTypes       = ProductType::active()->ordered()->get(['id', 'name']);
        $billingCycles      = BillingCycle::ordered()->get(['id', 'name', 'slug']);
        $configurableGroups = ConfigurableOptionGroup::active()->ordered()->get(['id', 'name']);

        return Inertia::render('Admin/Products/Create', [
            'productTypes'       => $productTypes,
            'billingCycles'      => $billingCycles,
            'configurableGroups' => $configurableGroups,
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'slug'                        => 'nullable|string|max:255|unique:products,slug',
            'description'                 => 'nullable|string|max:2000',
            'product_type_id'             => 'required|exists:product_types,id',
            'module_name'                 => 'nullable|string|max:255',
            'status'                      => 'required|in:active,inactive,hidden',
            'is_publicly_available'       => 'boolean',
            'is_resellable_by_default'    => 'boolean',
            'auto_setup'                  => 'boolean',
            'requires_approval'           => 'boolean',
            'setup_fee'                   => 'nullable|numeric|min:0',
            'track_stock'                 => 'boolean',
            'stock_quantity'              => 'nullable|integer|min:0',
            'display_order'               => 'integer|min:0',
            'pricings'                    => 'required|array|min:1',
            'pricings.*.billing_cycle_id' => 'required|exists:billing_cycles,id',
            'pricings.*.price'            => 'required|numeric|min:0',
            'pricings.*.setup_fee'        => 'nullable|numeric|min:0',
            'configurable_groups'         => 'array',
            'configurable_groups.*'       => 'exists:configurable_option_groups,id',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pricings           = $validated['pricings'];
        $configurableGroups = $validated['configurable_groups'] ?? [];
        unset($validated['pricings'], $validated['configurable_groups']);

        $product = Product::create($validated);

        // Create pricings
        foreach ($pricings as $pricing) {
            ProductPricing::create([
                'product_id'       => $product->id,
                'billing_cycle_id' => $pricing['billing_cycle_id'],
                'price'            => $pricing['price'],
                'setup_fee'        => $pricing['setup_fee'] ?? 0,
                'currency_code'    => 'USD',
                'is_active'        => true,
            ]);
        }

        // Associate configurable option groups
        if (! empty($configurableGroups)) {
            $product->configurableOptionGroups()->sync($configurableGroups);
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): Response
    {
        $product->load([
            'productType',
            'pricings.billingCycle',
            'configurableOptionGroups.options.pricings.billingCycle',
        ]);

        return Inertia::render('Admin/Products/Show', [
            'product' => [
                'id'                         => $product->id,
                'name'                       => $product->name,
                'slug'                       => $product->slug,
                'description'                => $product->description,
                'product_type'               => $product->productType,
                'module_name'                => $product->module_name,
                'status'                     => $product->status,
                'is_publicly_available'      => $product->is_publicly_available,
                'is_resellable_by_default'   => $product->is_resellable_by_default,
                'auto_setup'                 => $product->auto_setup,
                'requires_approval'          => $product->requires_approval,
                'setup_fee'                  => $product->setup_fee,
                'track_stock'                => $product->track_stock,
                'stock_quantity'             => $product->stock_quantity,
                'display_order'              => $product->display_order,
                'created_at'                 => $product->created_at,
                'updated_at'                 => $product->updated_at,
                'pricings'                   => $product->pricings->map(fn($pricing) => [
                    'id'            => $pricing->id,
                    'billing_cycle' => $pricing->billingCycle,
                    'price'         => $pricing->price,
                    'setup_fee'     => $pricing->setup_fee,
                    'currency_code' => $pricing->currency_code,
                    'is_active'     => $pricing->is_active,
                ]),
                'configurable_option_groups' => $product->configurableOptionGroups->map(fn($group) => [
                    'id'            => $group->id,
                    'name'          => $group->name,
                    'slug'          => $group->slug,
                    'description'   => $group->description,
                    'is_active'     => $group->is_active,
                    'options_count' => $group->options->count(),
                    'options'       => $group->options->map(fn($option) => [
                        'id'          => $option->id,
                        'name'        => $option->name,
                        'option_type' => $option->option_type,
                        'is_required' => $option->is_required,
                        'is_active'   => $option->is_active,
                        'pricings'    => $option->pricings->map(fn($pricing) => [
                            'billing_cycle_name' => $pricing->billingCycle->name,
                            'price'              => $pricing->price,
                            'setup_fee'          => $pricing->setup_fee,
                        ]),
                    ]),
                ]),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product): Response
    {
        $product->load(['pricings', 'configurableOptionGroups']);

        $productTypes       = ProductType::active()->ordered()->get(['id', 'name']);
        $billingCycles      = BillingCycle::ordered()->get(['id', 'name', 'slug']);
        $configurableGroups = ConfigurableOptionGroup::active()->ordered()->get(['id', 'name']);

        // Obtener grupos configurables con sus opciones para recursos base dinámicos
        $availableResourceGroups = ConfigurableOptionGroup::with(['options' => function ($query) {
            $query->where('is_active', true)->orderBy('display_order');
        }])
            ->active()
            ->ordered()
            ->get()
            ->map(function ($group) {
                return [
                    'id'      => $group->id,
                    'name'    => $group->name,
                    'slug'    => Str::slug($group->name),
                    'options' => $group->options->map(function ($option) {
                        return [
                            'id'          => $option->id,
                            'name'        => $option->name,
                            'option_type' => $option->option_type,
                            'value'       => $option->value,
                        ];
                    }),
                ];
            });

        // Calcular precio automático usando el servicio
        $pricingCalculator = app(\App\Services\PricingCalculatorService::class);
       // $calculatedPrice   = 0;

        try {
            $calculation     = $pricingCalculator->calculateProductPrice($product->id, 1, []); // Ciclo mensual
            $calculatedPrice = $calculation['total'];

            // Debug: Log para verificar el cálculo
            \Illuminate\Support\Facades\Log::info('Precio calculado para producto ' . $product->id . ': ' . $calculatedPrice, [
                'product_id'  => $product->id,
                'calculation' => $calculation,
            ]);

            // Debug temporal: forzar un valor para probar
            if ($calculatedPrice == 0) {
                $calculatedPrice = 99.99; // Valor temporal para debug
                \Illuminate\Support\Facades\Log::info('Forzando precio temporal: ' . $calculatedPrice);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error calculando precio en admin: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'exception'  => $e,
            ]);
            // Debug temporal: forzar un valor en caso de error
            $calculatedPrice = 88.88;
        }

        return Inertia::render('Admin/Products/Edit', [
            'product'                 => [
                'id'                       => $product->id,
                'name'                     => $product->name,
                'slug'                     => $product->slug,
                'description'              => $product->description,
                'product_type_id'          => $product->product_type_id,
                'module_name'              => $product->module_name,
                'status'                   => $product->status,
                'is_publicly_available'    => $product->is_publicly_available,
                'is_resellable_by_default' => $product->is_resellable_by_default,
                'auto_setup'               => $product->auto_setup,
                'requires_approval'        => $product->requires_approval,
                'setup_fee'                => $product->setup_fee,
                'track_stock'              => $product->track_stock,
                'stock_quantity'           => $product->stock_quantity,
                'display_order'            => $product->display_order,
                // Recursos base dinámicos
                'base_resources'           => $product->base_resources ?? [],
                'pricings'                 => $product->pricings->map(fn($pricing) => [
                    'id'               => $pricing->id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'price'            => $pricing->price,
                    'setup_fee'        => $pricing->setup_fee,
                ]),
                'configurable_groups'      => $product->configurableOptionGroups->mapWithKeys(function ($group) {
                    return [$group->id => [
                        'display_order' => $group->pivot->display_order ?? 0,
                        'base_quantity' => $group->pivot->base_quantity ?? 0,
                    ]];
                })->toArray(),
            ],
            'productTypes'            => $productTypes,
            'billingCycles'           => $billingCycles,
            'configurableGroups'      => $configurableGroups,
            'availableResourceGroups' => $availableResourceGroups,
            'calculatedPrice'         => $calculatedPrice,
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                                => 'required|string|max:255',
            'slug'                                => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description'                         => 'nullable|string|max:2000',
            'product_type_id'                     => 'required|exists:product_types,id',
            'module_name'                         => 'nullable|string|max:255',
            'status'                              => 'required|in:active,inactive,hidden',
            'is_publicly_available'               => 'boolean',
            'is_resellable_by_default'            => 'boolean',
            'auto_setup'                          => 'boolean',
            'requires_approval'                   => 'boolean',
            'setup_fee'                           => 'nullable|numeric|min:0',
            'track_stock'                         => 'boolean',
            'stock_quantity'                      => 'nullable|integer|min:0',
            'display_order'                       => 'integer|min:0',
            'pricings'                            => 'required|array|min:1',
            'pricings.*.billing_cycle_id'         => 'required|exists:billing_cycles,id',
            'pricings.*.price'                    => 'required|numeric|min:0',
            'pricings.*.setup_fee'                => 'nullable|numeric|min:0',
            'configurable_groups'                 => 'array',
            'configurable_groups.*.display_order' => 'nullable|integer|min:0',
            'configurable_groups.*.base_quantity' => 'nullable|numeric|min:0',
            // Recursos base dinámicos
            'base_resources'                      => 'nullable|array',
            'base_resources.*'                    => 'nullable|numeric|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pricings           = $validated['pricings'];
        $configurableGroups = $validated['configurable_groups'] ?? [];
        unset($validated['pricings'], $validated['configurable_groups']);

        // Preparar datos para la tabla pivot con base_quantity
        $pivotData = [];
        foreach ($configurableGroups as $groupId => $groupData) {
            // Verificar que el grupo existe
            if (ConfigurableOptionGroup::where('id', $groupId)->exists()) {
                $pivotData[$groupId] = [
                    'display_order' => $groupData['display_order'] ?? 0,
                    'base_quantity' => $groupData['base_quantity'] ?? 0,
                ];
            }
        }

        // Debug: Log los datos que se van a actualizar
        Log::info('Updating product with data:', $validated);
        Log::info('Configurable groups data:', $configurableGroups);
        Log::info('Pivot data prepared:', $pivotData);

        $product->update($validated);

        // Update pricings - delete existing and recreate
        $product->pricings()->delete();
        foreach ($pricings as $pricing) {
            ProductPricing::create([
                'product_id'       => $product->id,
                'billing_cycle_id' => $pricing['billing_cycle_id'],
                'price'            => $pricing['price'],
                'setup_fee'        => $pricing['setup_fee'] ?? 0,
                'currency_code'    => 'USD',
                'is_active'        => true,
            ]);
        }

        // Update configurable option groups with pivot data
        $product->configurableOptionGroups()->sync($pivotData);

        // Recalcular precios automáticamente basado en recursos base
        $this->recalculateProductPrices($product);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Recalcular precios del producto basado en recursos base y precios de opciones
     */
    private function recalculateProductPrices(Product $product)
    {
        // Obtener todos los ciclos de facturación
        $billingCycles = BillingCycle::all();

        foreach ($billingCycles as $cycle) {
            $totalPrice = 0;

            // Calcular precio basado en recursos base del producto
            foreach ($product->configurableOptionGroups as $group) {
                $baseQuantity = $group->pivot->base_quantity ?? 0;

                if ($baseQuantity > 0) {
                    // Buscar el precio de esta opción para este ciclo
                    foreach ($group->options as $option) {
                        $optionPricing = $option->pricings()
                            ->where('billing_cycle_id', $cycle->id)
                            ->first();

                        if ($optionPricing) {
                            $totalPrice += $baseQuantity * $optionPricing->price;
                        }
                    }
                }
            }

            // Actualizar o crear el precio para este ciclo
            if ($totalPrice > 0) {
                ProductPricing::updateOrCreate(
                    [
                        'product_id'       => $product->id,
                        'billing_cycle_id' => $cycle->id,
                    ],
                    [
                        'price'         => $totalPrice,
                        'setup_fee'     => 0,
                        'currency_code' => 'USD',
                        'is_active'     => true,
                    ]
                );
            }
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /**
     * Calculate pricing for a product with options
     */
    public function calculatePricing(Request $request, Product $product)
    {
        $validated = $request->validate([
            'billing_cycle_id'    => 'required|exists:billing_cycles,id',
            'options'             => 'array',
            'options.*.option_id' => 'required|exists:configurable_options,id',
            'options.*.quantity'  => 'nullable|integer|min:1',
        ]);

        $basePricing = $product->pricings()
            ->where('billing_cycle_id', $validated['billing_cycle_id'])
            ->first();

        if (! $basePricing) {
            return response()->json(['error' => 'Precio base no encontrado'], 404);
        }

        $totalPrice    = $basePricing->price;
        $totalSetupFee = $basePricing->setup_fee;
        $optionDetails = [];

        foreach ($validated['options'] ?? [] as $optionData) {
            $option = ConfigurableOption::with('pricings')
                ->find($optionData['option_id']);

            if ($option) {
                $optionPricing = $option->pricings()
                    ->where('billing_cycle_id', $validated['billing_cycle_id'])
                    ->first();

                if ($optionPricing) {
                    $quantity       = $optionData['quantity'] ?? 1;
                    $optionPrice    = $optionPricing->price * $quantity;
                    $optionSetupFee = $optionPricing->setup_fee * $quantity;

                    $totalPrice += $optionPrice;
                    $totalSetupFee += $optionSetupFee;

                    $optionDetails[] = [
                        'option_name' => $option->name,
                        'quantity'    => $quantity,
                        'unit_price'  => $optionPricing->price,
                        'total_price' => $optionPrice,
                        'setup_fee'   => $optionSetupFee,
                    ];
                }
            }
        }

        return response()->json([
            'base_price'      => $basePricing->price,
            'base_setup_fee'  => $basePricing->setup_fee,
            'options'         => $optionDetails,
            'total_price'     => $totalPrice,
            'total_setup_fee' => $totalSetupFee,
            'currency_code'   => $basePricing->currency_code,
        ]);
    }
}
