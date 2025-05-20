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

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str; // Para el slug

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
        return Inertia::render('Admin/Products/Create'/*, [
            // 'productTypes' => $productTypes,
            // 'resellers' => $resellers,
        ]*/);
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
        // Considerar verificar unicidad del slug si se genera aquí y no se valida como unique en el FormRequest
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
        $product->load('pricings', 'configurableOptionGroups');

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name', 'company_name']);
        // Incluir product_id para saber si un grupo es global o específico de un producto
        $allOptionGroups = ConfigurableOptionGroup::orderBy('name')->get(['id', 'name', 'product_id']);

        $allOptionGroupsData = $allOptionGroups->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'owner_product_id' => $group->product_id, // Este es el product_id de la tabla configurable_option_groups
        ])->toArray();

        // Añade esta línea para depurar:
        // dd($allOptionGroupsData, $allOptionGroups->count());

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->toArray() + [
                'pricings' => $product->pricings ? $product->pricings->toArray() : [],
                'associated_option_groups' => $product->configurableOptionGroups->mapWithKeys(function ($group) {
                    return [$group->id => ['display_order' => $group->pivot->display_order ?? 0]];
                })->toArray(),
            ],
            'resellers' => $resellers->map(fn ($reseller) => [
                'id' => $reseller->id,
                'label' => $reseller->name . ($reseller->company_name ? " ({$reseller->company_name})" : "")
            ])->toArray(),
            'all_option_groups' => $allOptionGroupsData, // Usar la variable depurada
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        // dd($request->all()); // Descomenta esta línea para ver todo lo que llega

        // La autorización y validación son manejadas por UpdateProductRequest
        $validatedData = $request->validated();
        // Excluir configurable_option_groups de $validatedData para el update del producto principal
        $productData = collect($validatedData)->except('configurable_option_groups')->toArray();

        // Si el nombre cambia, actualiza el slug
        // Si el slug fue enviado explícitamente y es diferente al generado por el nuevo nombre,
        // se podría dar prioridad al slug enviado (si esa es la lógica deseada y el form lo permite).
        // Por ahora, si el nombre cambia, el slug se regenera basado en el nuevo nombre.
        if (isset($productData['name']) && $productData['name'] !== $product->name) {
            $productData['slug'] = Str::slug($productData['name']);
        }
        elseif (isset($productData['name']) && empty($productData['slug'])) { // Si el slug no vino del form o vino vacío pero el nombre sí
            $productData['slug'] = Str::slug($productData['name']);
        }

        $product->update($productData);

        // Sincronizar grupos de opciones configurables
        if ($request->has('configurable_option_groups')) {
            // DEBUG: Ver qué datos llegan para los grupos
             dd($request->input('configurable_option_groups'));

            $groupsToSync = [];
            foreach ($request->input('configurable_option_groups', []) as $groupId => $pivotData) {
                $groupsToSync[$groupId] = ['display_order' => isset($pivotData['display_order']) ? (int)$pivotData['display_order'] : 0];
            }
            // DEBUG: Ver qué datos se van a sincronizar
            // dd($groupsToSync);

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
        $product->pricings()->create($validated);
        return redirect()->back()->with('success', 'Precio añadido correctamente.');
    }

    public function updatePricing(UpdateProductPricingRequest $request, Product $product, ProductPricing $pricing): RedirectResponse
    {
        // La autorización y validación ahora son manejadas por UpdateProductPricingRequest
        // $this->authorize('update', $pricing); // Ya no es necesario aquí
        $validated = $request->validated();
        $pricing->update($validated);
        return redirect()->back()->with('success', 'Precio actualizado correctamente.');
    }

    public function destroyPricing(Product $product, ProductPricing $pricing): RedirectResponse
    {
        $this->authorize('delete', $pricing); // Usar la instancia de ProductPricing
        $pricing->delete();
        return redirect()->back()->with('success', 'Precio eliminado correctamente.');
    }
}
