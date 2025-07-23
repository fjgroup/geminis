<?php
namespace App\Domains\Products\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;        // Added Log
use Illuminate\Support\Facades\Log; // Added Rule
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', ProductType::class);
        $productTypes = ProductType::orderBy('name')->paginate(10);
        return Inertia::render('Admin/ProductTypes/Index', ['productTypes' => $productTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', ProductType::class);
        return Inertia::render('Admin/ProductTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // $this->authorize('create', ProductType::class); // TODO: Implement Policy

        $validatedData = $request->validate([
            'name'                     => 'required|string|max:255',
            'slug'                     => 'required|string|alpha_dash|max:255|unique:product_types,slug',
            'requires_domain'          => 'required|boolean',
            'creates_service_instance' => 'required|boolean',
            'description'              => 'nullable|string|max:5000',
        ]);

        // Ensure boolean values are correctly cast from request if not already
        $validatedData['requires_domain']          = $request->boolean('requires_domain');
        $validatedData['creates_service_instance'] = $request->boolean('creates_service_instance');

        ProductType::create($validatedData);

        return redirect()->route('admin.product-types.index')->with('success', 'Tipo de producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductType $productType): InertiaResponse
    {
        // $this->authorize('view', $productType); // TODO: Implement Policy
        return Inertia::render('Admin/ProductTypes/Edit', ['productType' => $productType]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductType $productType): InertiaResponse
    {
        // $this->authorize('update', $productType); // Or 'view' if separate permissions
        return Inertia::render('Admin/ProductTypes/Edit', ['productType' => $productType]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        // $this->authorize('update', $productType); // TODO: Implement Policy

        $validatedData = $request->validate([
            'name'                     => 'required|string|max:255',
            'slug'                     => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('product_types')->ignore($productType->id)],
            'requires_domain'          => 'required|boolean',
            'creates_service_instance' => 'required|boolean',
            'description'              => 'nullable|string|max:5000',
        ]);

        // Ensure boolean values are correctly cast from request
        $validatedData['requires_domain']          = $request->boolean('requires_domain');
        $validatedData['creates_service_instance'] = $request->boolean('creates_service_instance');

        $productType->update($validatedData);

        return redirect()->route('admin.product-types.index')->with('success', 'Tipo de producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $productType): RedirectResponse
    {
        // $this->authorize('delete', $productType); // TODO: Implement Policy

        if ($productType->products()->exists()) {
            Log::warning("Attempted to delete ProductType ID: {$productType->id} which has associated products.");
            return redirect()->route('admin.product-types.index')->with('error', 'No se puede eliminar el tipo de producto porque tiene productos asociados.');
        }

        $productType->delete();
        Log::info("Deleted ProductType ID: {$productType->id}");
        return redirect()->route('admin.product-types.index')->with('success', 'Tipo de producto eliminado exitosamente.');
    }
}
