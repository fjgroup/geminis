<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest; // Para el CRUD del producto principal
use App\Http\Requests\Admin\UpdateProductRequest; // Para el CRUD del producto principal
use App\Http\Requests\Admin\StoreProductPricingRequest; // ¡IMPORTANTE! Añadir esta importación
use App\Http\Requests\Admin\UpdateProductPricingRequest; // ¡IMPORTANTE! Añadir esta importación
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Product;
use App\Models\ProductPricing; // Asegúrate que el namespace y nombre de clase son correctos
use App\Models\ConfigurableOptionGroup; // Añadir
use App\Models\User; // Para cargar revendedores si es necesario
use App\Models\ProductType; // Added for ProductType

use App\Models\BillingCycle; // Añadir importación para BillingCycle
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str; // Para el slug
use Illuminate\Support\Facades\Log; // Añadir importación para Log

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
        ->through(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'type' => $product->type,
            'owner_name' => $product->owner_id ? ($product->owner ? $product->owner->name : 'Revendedor (ID: '.$product->owner_id.')') : 'Plataforma',
            'status' => $product->status,
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
        if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
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
        // Incluir product_id para saber si un grupo es global o específico de un producto
        $allOptionGroups = ConfigurableOptionGroup::orderBy('name')->get(['id', 'name', 'product_id']);
        $productTypes = ProductType::orderBy('name')->get(['id', 'name']);

        $allOptionGroupsData = $allOptionGroups->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'owner_product_id' => $group->product_id, // Este es el product_id de la tabla configurable_option_groups
        ])->toArray();

        // Añade esta línea para depurar:

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->toArray() + [
                // pricings ya está en $product->toArray() si la relación está cargada
                // 'productType' ya está cargado y se incluirá en toArray()
                'associated_option_groups' => $product->configurableOptionGroups->mapWithKeys(function ($group) {
                    return [$group->id => ['display_order' => $group->pivot->display_order ?? 0]];
                })->toArray(),
            ],
            'resellers' => $resellers->map(fn ($reseller) => [
                'id' => $reseller->id,
                'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : "")
            ])->toArray(),
            'all_option_groups' => $allOptionGroupsData, // Usar la variable depurada
            'productTypes' => $productTypes->map(fn($pt) => ['value' => $pt->id, 'label' => $pt->name]),
            'billingCycles' => $billingCyclesFromDB->map(fn($cycle) => [
                'value' => $cycle->id,
                'label' => $cycle->name
            ]), // Pasar los ciclos de facturación formateados para SelectInput
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
        if (isset($productData['name']) && (!isset($productData['slug']) || empty($productData['slug']) || $productData['name'] !== $product->name) ) {
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
                $groupsToSync[$groupId] = ['display_order' => isset($pivotData['display_order']) ? (int)$pivotData['display_order'] : 0];
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
