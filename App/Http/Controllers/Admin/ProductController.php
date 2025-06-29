<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ConfigurableOptionGroup;
use Illuminate\Http\Request;
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
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->productType?->name,
                'status' => $product->status,
                'is_publicly_available' => $product->is_publicly_available,
                'display_order' => $product->display_order,
                'pricing_count' => $product->pricings->count(),
                'min_price' => $product->pricings->min('price'),
                'created_at' => $product->created_at,
            ]),
            'filters' => request()->only(['search', 'status', 'type']),
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        $productTypes = ProductType::active()->ordered()->get(['id', 'name']);
        $billingCycles = BillingCycle::ordered()->get(['id', 'name', 'slug']);
        $configurableGroups = ConfigurableOptionGroup::active()->ordered()->get(['id', 'name']);

        return Inertia::render('Admin/Products/Create', [
            'productTypes' => $productTypes,
            'billingCycles' => $billingCycles,
            'configurableGroups' => $configurableGroups,
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string|max:2000',
            'product_type_id' => 'required|exists:product_types,id',
            'module_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,hidden',
            'is_publicly_available' => 'boolean',
            'is_resellable_by_default' => 'boolean',
            'auto_setup' => 'boolean',
            'requires_approval' => 'boolean',
            'setup_fee' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'display_order' => 'integer|min:0',
            'pricings' => 'required|array|min:1',
            'pricings.*.billing_cycle_id' => 'required|exists:billing_cycles,id',
            'pricings.*.price' => 'required|numeric|min:0',
            'pricings.*.setup_fee' => 'nullable|numeric|min:0',
            'configurable_groups' => 'array',
            'configurable_groups.*' => 'exists:configurable_option_groups,id',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pricings = $validated['pricings'];
        $configurableGroups = $validated['configurable_groups'] ?? [];
        unset($validated['pricings'], $validated['configurable_groups']);

        $product = Product::create($validated);

        // Create pricings
        foreach ($pricings as $pricing) {
            ProductPricing::create([
                'product_id' => $product->id,
                'billing_cycle_id' => $pricing['billing_cycle_id'],
                'price' => $pricing['price'],
                'setup_fee' => $pricing['setup_fee'] ?? 0,
                'currency_code' => 'USD',
                'is_active' => true,
            ]);
        }

        // Associate configurable option groups
        if (!empty($configurableGroups)) {
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
            'configurableOptionGroups.options.pricings.billingCycle'
        ]);

        return Inertia::render('Admin/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->productType,
                'module_name' => $product->module_name,
                'status' => $product->status,
                'is_publicly_available' => $product->is_publicly_available,
                'is_resellable_by_default' => $product->is_resellable_by_default,
                'auto_setup' => $product->auto_setup,
                'requires_approval' => $product->requires_approval,
                'setup_fee' => $product->setup_fee,
                'track_stock' => $product->track_stock,
                'stock_quantity' => $product->stock_quantity,
                'display_order' => $product->display_order,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'pricings' => $product->pricings->map(fn($pricing) => [
                    'id' => $pricing->id,
                    'billing_cycle' => $pricing->billingCycle,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                    'currency_code' => $pricing->currency_code,
                    'is_active' => $pricing->is_active,
                ]),
                'configurable_option_groups' => $product->configurableOptionGroups->map(fn($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'slug' => $group->slug,
                    'description' => $group->description,
                    'is_active' => $group->is_active,
                    'options_count' => $group->options->count(),
                    'options' => $group->options->map(fn($option) => [
                        'id' => $option->id,
                        'name' => $option->name,
                        'option_type' => $option->option_type,
                        'is_required' => $option->is_required,
                        'is_active' => $option->is_active,
                        'pricings' => $option->pricings->map(fn($pricing) => [
                            'billing_cycle_name' => $pricing->billingCycle->name,
                            'price' => $pricing->price,
                            'setup_fee' => $pricing->setup_fee,
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
        
        $productTypes = ProductType::active()->ordered()->get(['id', 'name']);
        $billingCycles = BillingCycle::ordered()->get(['id', 'name', 'slug']);
        $configurableGroups = ConfigurableOptionGroup::active()->ordered()->get(['id', 'name']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type_id' => $product->product_type_id,
                'module_name' => $product->module_name,
                'status' => $product->status,
                'is_publicly_available' => $product->is_publicly_available,
                'is_resellable_by_default' => $product->is_resellable_by_default,
                'auto_setup' => $product->auto_setup,
                'requires_approval' => $product->requires_approval,
                'setup_fee' => $product->setup_fee,
                'track_stock' => $product->track_stock,
                'stock_quantity' => $product->stock_quantity,
                'display_order' => $product->display_order,
                'pricings' => $product->pricings->map(fn($pricing) => [
                    'id' => $pricing->id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                ]),
                'configurable_groups' => $product->configurableOptionGroups->pluck('id')->toArray(),
            ],
            'productTypes' => $productTypes,
            'billingCycles' => $billingCycles,
            'configurableGroups' => $configurableGroups,
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string|max:2000',
            'product_type_id' => 'required|exists:product_types,id',
            'module_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,hidden',
            'is_publicly_available' => 'boolean',
            'is_resellable_by_default' => 'boolean',
            'auto_setup' => 'boolean',
            'requires_approval' => 'boolean',
            'setup_fee' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'display_order' => 'integer|min:0',
            'pricings' => 'required|array|min:1',
            'pricings.*.billing_cycle_id' => 'required|exists:billing_cycles,id',
            'pricings.*.price' => 'required|numeric|min:0',
            'pricings.*.setup_fee' => 'nullable|numeric|min:0',
            'configurable_groups' => 'array',
            'configurable_groups.*' => 'exists:configurable_option_groups,id',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pricings = $validated['pricings'];
        $configurableGroups = $validated['configurable_groups'] ?? [];
        unset($validated['pricings'], $validated['configurable_groups']);

        $product->update($validated);

        // Update pricings - delete existing and recreate
        $product->pricings()->delete();
        foreach ($pricings as $pricing) {
            ProductPricing::create([
                'product_id' => $product->id,
                'billing_cycle_id' => $pricing['billing_cycle_id'],
                'price' => $pricing['price'],
                'setup_fee' => $pricing['setup_fee'] ?? 0,
                'currency_code' => 'USD',
                'is_active' => true,
            ]);
        }

        // Update configurable option groups
        $product->configurableOptionGroups()->sync($configurableGroups);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Producto actualizado exitosamente.');
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
            'billing_cycle_id' => 'required|exists:billing_cycles,id',
            'options' => 'array',
            'options.*.option_id' => 'required|exists:configurable_options,id',
            'options.*.quantity' => 'nullable|integer|min:1',
        ]);

        $basePricing = $product->pricings()
            ->where('billing_cycle_id', $validated['billing_cycle_id'])
            ->first();

        if (!$basePricing) {
            return response()->json(['error' => 'Precio base no encontrado'], 404);
        }

        $totalPrice = $basePricing->price;
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
                    $quantity = $optionData['quantity'] ?? 1;
                    $optionPrice = $optionPricing->price * $quantity;
                    $optionSetupFee = $optionPricing->setup_fee * $quantity;

                    $totalPrice += $optionPrice;
                    $totalSetupFee += $optionSetupFee;

                    $optionDetails[] = [
                        'option_name' => $option->name,
                        'quantity' => $quantity,
                        'unit_price' => $optionPricing->price,
                        'total_price' => $optionPrice,
                        'setup_fee' => $optionSetupFee,
                    ];
                }
            }
        }

        return response()->json([
            'base_price' => $basePricing->price,
            'base_setup_fee' => $basePricing->setup_fee,
            'options' => $optionDetails,
            'total_price' => $totalPrice,
            'total_setup_fee' => $totalSetupFee,
            'currency_code' => $basePricing->currency_code,
        ]);
    }
}
