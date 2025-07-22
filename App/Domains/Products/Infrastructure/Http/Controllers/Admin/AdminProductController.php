<?php

namespace App\Domains\Products\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Services\ProductManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class AdminProductController
 *
 * Controlador de productos para administradores en arquitectura hexagonal
 * Solo maneja HTTP requests/responses, delega lógica de negocio a ProductManagementService
 * Ubicado en Infrastructure layer como Input Adapter
 */
class AdminProductController extends Controller
{
    public function __construct(
        private ProductManagementService $productManagementService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('viewAny', Product::class);

        $filters = $request->only(['search', 'status', 'product_type_id', 'owner_id']);
        $result = $this->productManagementService->getProducts($filters, 15);

        if (!$result['success']) {
            Log::error('Error obteniendo productos', [
                'filters' => $filters,
                'error' => $result['message']
            ]);

            // En caso de error, mostrar página vacía
            $result['data'] = collect()->paginate(15);
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $result['data'],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('create', Product::class);

        $formData = $this->productManagementService->getFormData();

        return Inertia::render('Admin/Products/Create', $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $result = $this->productManagementService->createProduct($request->validated());

        if ($result['success']) {
            return redirect()->route('admin.products.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'productType',
            'pricings.billingCycle',
            'configurableOptionGroups.options',
            'owner',
            'clientServices' => function ($query) {
                $query->with('client')->latest()->limit(10);
            }
        ]);

        return Inertia::render('Admin/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'status' => $product->status,
                'product_type' => $product->productType,
                'pricings' => $product->pricings,
                'configurable_groups' => $product->configurableOptionGroups,
                'owner' => $product->owner,
                'recent_services' => $product->clientServices,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('update', $product);

        $product->load([
            'productType',
            'pricings.billingCycle',
            'configurableOptionGroups',
            'owner'
        ]);

        $formData = $this->productManagementService->getFormData();

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'status' => $product->status,
                'product_type_id' => $product->product_type_id,
                'owner_id' => $product->owner_id,
                'pricings' => $product->pricings->map(function ($pricing) {
                    return [
                        'id' => $pricing->id,
                        'billing_cycle_id' => $pricing->billing_cycle_id,
                        'price' => $pricing->price,
                        'setup_fee' => $pricing->setup_fee,
                        'currency_code' => $pricing->currency_code,
                        'billing_cycle' => $pricing->billingCycle,
                    ];
                }),
                'configurable_groups' => $product->configurableOptionGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'display_order' => $group->pivot->display_order ?? 0,
                        'base_quantity' => $group->pivot->base_quantity ?? 0,
                    ];
                }),
            ],
            ...$formData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $result = $this->productManagementService->updateProduct($product, $request->validated());

        if ($result['success']) {
            return redirect()->route('admin.products.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // TODO: Implementar autorización
        // $this->authorize('delete', $product);

        $result = $this->productManagementService->deleteProduct($product);

        if ($result['success']) {
            return redirect()->route('admin.products.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Get pricing options for a product (AJAX)
     */
    public function getPricingOptions(Product $product): JsonResponse
    {
        try {
            $product->load('pricings.billingCycle');

            $pricings = $product->pricings->map(function ($pricing) {
                return [
                    'id' => $pricing->id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'billing_cycle_name' => $pricing->billingCycle->name,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                    'currency_code' => $pricing->currency_code,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $pricings
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo opciones de pricing', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo opciones de pricing'
            ], 500);
        }
    }

    /**
     * Recalculate product prices (AJAX)
     */
    public function recalculatePrices(Product $product): JsonResponse
    {
        try {
            $result = $this->productManagementService->updateProduct($product, []);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Precios recalculados exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error recalculando precios', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error recalculando precios'
            ], 500);
        }
    }

    /**
     * Get product statistics (AJAX)
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('status', 'active')->count(),
                'inactive_products' => Product::where('status', 'inactive')->count(),
                'draft_products' => Product::where('status', 'draft')->count(),
                'products_with_services' => Product::whereHas('clientServices')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de productos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas'
            ], 500);
        }
    }

    /**
     * Search products for autocomplete (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        try {
            $products = Product::where('status', 'active')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('slug', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'slug'])
                ->map(function ($product) {
                    return [
                        'value' => $product->id,
                        'label' => $product->name . " ({$product->slug})"
                    ];
                });

            return response()->json($products);

        } catch (\Exception $e) {
            Log::error('Error buscando productos', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);

            return response()->json([]);
        }
    }
}
