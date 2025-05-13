<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;
use App\Http\Requests\Admin\StoreProductRequest; // Para el CRUD del producto principal
use App\Models\User; // Para cargar revendedores si es necesario
use App\Http\Requests\Admin\UpdateProductRequest; // Para el CRUD del producto principal
use App\Http\Requests\Admin\StoreProductPricingRequest; // ¡IMPORTANTE! Añadir esta importación
use App\Http\Requests\Admin\UpdateProductPricingRequest; // ¡IMPORTANTE! Añadir esta importación
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str; // Para el slug
use App\Models\ProductPricing; // Asegúrate que el namespace y nombre de clase son correctos

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
    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);
        $resellers = User::where('role', 'reseller')
            ->select('id', 'name', 'company_name') 
            ->orderBy('name')
            ->get();

            $product->load('pricings'); // ¡IMPORTANTE! Cargar los precios asociados al producto
        
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'resellers' => $resellers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
{
        // La autorización y validación son manejadas por UpdateProductRequest
        $validatedData = $request->validated();

        $updatePayload = $validatedData;

        // Si el nombre cambia, actualiza el slug
        // Si el slug fue enviado explícitamente y es diferente al generado por el nuevo nombre,
        // se podría dar prioridad al slug enviado (si esa es la lógica deseada y el form lo permite).
        // Por ahora, si el nombre cambia, el slug se regenera basado en el nuevo nombre.
        if (isset($validatedData['name']) && $validatedData['name'] !== $product->name) {
            $updatePayload['slug'] = Str::slug($validatedData['name']);
        } elseif (isset($validatedData['name']) && !isset($updatePayload['slug'])) { // Si el slug no vino del form pero el nombre sí
            $updatePayload['slug'] = Str::slug($validatedData['name']);
        }
        $product->update($updatePayload);
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