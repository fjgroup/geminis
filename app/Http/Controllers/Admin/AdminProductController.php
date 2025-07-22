<?php

/**
 * ⚠️ DEPRECATED - MARCADO PARA ELIMINACIÓN
 *
 * Este controlador ha sido refactorizado y reemplazado por:
 * - AdminProductControllerRefactored (manejo HTTP)
 * - ProductManagementService (lógica de negocio)
 * - PricingCalculatorService (cálculos de precios)
 *
 * TODO: Eliminar este archivo después de migrar completamente las rutas
 * Fecha de refactorización: 2025-01-22
 * Reemplazado por: AdminProductControllerRefactored + ProductManagementService
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductPricingRequest;  // Para el CRUD del producto principal
use App\Http\Requests\Admin\StoreProductRequest;         // Para el CRUD del producto principal
use App\Http\Requests\Admin\UpdateProductPricingRequest; // ¡IMPORTANTE! Añadir esta importación
use App\Http\Requests\Admin\UpdateProductRequest;        // ¡IMPORTANTE! Añadir esta importación
use App\Models\BillingCycle;
use App\Models\ConfigurableOptionGroup;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;        // Asegúrate que el namespace y nombre de clase son correctos
use App\Models\ProductType;           // Añadir
use App\Models\User;                  // Para cargar revendedores si es necesario
use Illuminate\Http\RedirectResponse; // Added for ProductType

use Illuminate\Support\Facades\Log; // Añadir importación para BillingCycle
use Illuminate\Support\Str;
use Inertia\Inertia; // Para el slug
use Inertia\Response;

// Añadir importación para Log

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::latest()
            ->with('owner') // Carga la relación 'owner' si la tienes definida en el modelo Product
            ->paginate(10)
            ->through(fn($product) => [
                'id'         => $product->id,
                'name'       => $product->name,
                'slug'       => $product->slug,
                'type'       => $product->type,
                'owner_name' => $product->owner_id ? ($product->owner ? $product->owner->name : 'Revendedor (ID: ' . $product->owner_id . ')') : 'Plataforma',
                'status'     => $product->status,
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Product::class);
        // Aquí podrías pasar datos adicionales si fueran necesarios (ej: tipos de producto, usuarios revendedores)
        // $productTypes = [['value' => 'shared_hosting', 'label' => 'Shared Hosting'], ...];
        // $resellers = User::where('role', 'reseller')->pluck('name', 'id');
        $productTypes = ProductType::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Admin/Products/Create', [
            'productTypes' => $productTypes->map(fn($pt) => ['value' => $pt->id, 'label' => $pt->name]),
            // 'resellers' => $resellers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        // La autorización y validación son manejadas por StoreProductRequest
        $validatedData = $request->validated();

        // Si el slug no viene en $validatedData (porque es nullable y no se envió), lo generamos.
        // Si se envía, se usará el valor validado.
        if (empty($validatedData['slug']) && ! empty($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        // Remove old 'type' field if product_type_id is present, to avoid confusion
        // The actual deprecation/removal of the 'type' column is a separate migration task.
        if (isset($validatedData['product_type_id'])) {
            unset($validatedData['type']);
        }
        Product::create($validatedData);
        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $this->authorize('view', $product);
        // Cargar relaciones si es necesario para la vista de detalle
        // $product->load('owner', 'pricings');
        return Inertia::render('Admin/Products/Show', ['product' => $product]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // ... en AdminProductController@edit ...
    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);
        // Cargar product.pricings con la relación billingCycle, productType
        $product->load('pricings.billingCycle', 'configurableOptionGroups', 'productType');

        // Obtener todos los ciclos de facturación
        $billingCyclesFromDB = BillingCycle::orderBy('name')->get(['id', 'name']);

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name', 'company_name']);
        // Obtener todos los grupos de opciones configurables
        $allOptionGroups = ConfigurableOptionGroup::orderBy('name')->get(['id', 'name']);
        $productTypes    = ProductType::orderBy('name')->get(['id', 'name']);

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
                    'slug'    => \Illuminate\Support\Str::slug($group->name),
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

        $allOptionGroupsData = $allOptionGroups->map(fn($group) => [
            'id'   => $group->id,
            'name' => $group->name,
        ])->toArray();

        // Calcular precio automático usando el servicio
        $pricingCalculator = app(\App\Services\PricingCalculatorService::class);
        $calculatedPrice   = 0;

        try {
            $calculation     = $pricingCalculator->calculateProductPrice($product->id, 1, []); // Ciclo mensual
            $calculatedPrice = $calculation['total'];

            // Debug: Log para verificar el cálculo
            \Illuminate\Support\Facades\Log::info('AdminProductController - Precio calculado para producto ' . $product->id . ': ' . $calculatedPrice, [
                'product_id'  => $product->id,
                'calculation' => $calculation,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AdminProductController - Error calculando precio: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'exception'  => $e,
            ]);
            $calculatedPrice = 0;
        }

        return Inertia::render('Admin/Products/Edit', [
            'product'                 => $product->toArray() + [
                // pricings ya está en $product->toArray() si la relación está cargada
                // 'productType' ya está cargado y se incluirá en toArray()
                'configurable_groups' => $product->configurableOptionGroups->mapWithKeys(function ($group) {
                    return [$group->id => [
                        'display_order' => $group->pivot->display_order ?? 0,
                        'base_quantity' => $group->pivot->base_quantity ?? 0,
                    ]];
                })->toArray(),
            ],
            'resellers'               => $resellers->map(fn($reseller) => [
                'id'    => $reseller->id,
                'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : ""),
            ])->toArray(),
            'all_option_groups'       => $allOptionGroupsData, // Usar la variable depurada
            'productTypes'            => $productTypes->map(fn($pt) => ['value' => $pt->id, 'label' => $pt->name]),
            'billingCycles'           => $billingCyclesFromDB->map(fn($cycle) => [
                'value' => $cycle->id,
                'label' => $cycle->name,
            ]), // Pasar los ciclos de facturación formateados para SelectInput
            'availableResourceGroups' => $availableResourceGroups,
            'calculatedPrice'         => $calculatedPrice,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {

        // La autorización y validación son manejadas por UpdateProductRequest
        $validatedData = $request->validated();
        // Excluir configurable_option_groups y el antiguo campo 'type' de $validatedData para el update del producto principal
        $productData = collect($validatedData)->except(['configurable_option_groups', 'type'])->toArray();

        // Si el nombre cambia, actualiza el slug
        // Si el slug fue enviado explícitamente y es diferente al generado por el nuevo nombre,
        // se podría dar prioridad al slug enviado (si esa es la lógica deseada y el form lo permite).
        // Por ahora, si el nombre cambia, el slug se regenera basado en el nuevo nombre.
        if (isset($productData['name']) && (! isset($productData['slug']) || empty($productData['slug']) || $productData['name'] !== $product->name)) {
            $productData['slug'] = Str::slug($productData['name']);
        }
        // The old 'type' field is now excluded by the except() method above.
        // The following block can be removed or kept as an additional safeguard, though it becomes redundant.
        // if (isset($productData['product_type_id'])) {
        //    unset($productData['type']); // This would attempt to unset 'type' again if it somehow passed the except filter.
        // }

        $product->update($productData);

        // Sincronizar grupos de opciones configurables
        if ($request->has('configurable_option_groups')) {

            $groupsToSync = [];
            foreach ($request->input('configurable_option_groups', []) as $groupId => $pivotData) {
                $groupsToSync[$groupId] = [
                    'display_order' => isset($pivotData['display_order']) ? (int) $pivotData['display_order'] : 0,
                    'base_quantity' => isset($pivotData['base_quantity']) ? (float) $pivotData['base_quantity'] : 0,
                ];
            }

            $product->configurableOptionGroups()->sync($groupsToSync);
        } else {
            $product->configurableOptionGroups()->detach(); // Si no se envía nada, desasociar todos
        }
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete(); // Asumiendo SoftDeletes si lo tienes en el modelo Product
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function storePricing(StoreProductPricingRequest $request, Product $product): RedirectResponse
    {
        // Autorizar la creación de un precio para este producto
        // Asumiendo que si puede crear el producto, puede añadirle precios, o usar ProductPricingPolicy directamente
        // La autorización y validación ahora son manejadas por StoreProductPricingRequest
        $validated = $request->validated();
        Log::info('Datos validados en storePricing:', $validated); // Log para depuración

        // Usar directamente los datos validados para la creación, incluyendo billing_cycle_id
        $product->pricings()->create($validated);
        Log::info('Datos usados para crear pricing:', $validated); // Log para depuración
        return redirect()->back()->with('success', 'Precio añadido correctamente.');
    }

    public function updatePricing(UpdateProductPricingRequest $request, Product $product, ProductPricing $pricing): RedirectResponse
    {
        // La autorización y validación ahora son manejadas por UpdateProductPricingRequest
        // $this->authorize('update', $pricing); // Ya no es necesario aquí
        $validated = $request->validated();
        Log::info('Datos validados en updatePricing:', $validated); // Log para depuración

        // Usar directamente los datos validados para la actualización
        $pricing->update($validated);
        Log::info('Datos usados para actualizar pricing:', $validated); // Log para depuración
        return redirect()->back()->with('success', 'Precio actualizado correctamente.');
    }

    public function destroyPricing(Product $product, ProductPricing $pricing): RedirectResponse
    {
        $this->authorize('delete', $pricing); // Usar la instancia de ProductPricing
        $pricing->delete();
        return redirect()->back()->with('success', 'Precio eliminado correctamente.');
    }
}
