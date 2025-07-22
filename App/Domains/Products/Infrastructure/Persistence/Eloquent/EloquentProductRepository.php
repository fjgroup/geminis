<?php

namespace App\Domains\Products\Infrastructure\Persistence\Eloquent;

use App\Domains\Products\Domain\Entities\Product;
use App\Domains\Products\Domain\ValueObjects\ProductPrice;
use App\Domains\Products\Domain\ValueObjects\ProductStatus;
use App\Domains\Products\Infrastructure\Persistence\Models\Product as ProductModel;
use App\Domains\Products\Interfaces\Domain\ProductRepositoryInterface;
use App\Domains\Shared\Domain\ValueObjects\Money;

/**
 * Implementación Eloquent del ProductRepository
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Adaptador entre dominio e infraestructura
 * - Mapeo explícito entidad <-> modelo
 * - Intercambiable sin afectar dominio
 * - Transacciones manejadas correctamente
 * - Optimizaciones de DB aisladas
 */
final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        $eloquentModel = $this->findEloquentModel($product->getId()) ?? new ProductModel();
        
        // ✅ BENEFICIO: Mapeo explícito dominio -> infraestructura
        $eloquentModel->fill([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice()->getAmount(),
            'currency' => $product->getPrice()->getCurrency(),
            'status' => $product->getStatus()->toString(),
            'product_type_id' => $product->getProductTypeId(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $eloquentModel->save();
    }

    public function findById(string $id): ?Product
    {
        $eloquentModel = ProductModel::find($id);
        
        return $eloquentModel ? $this->mapToDomain($eloquentModel) : null;
    }

    public function findByName(string $name): ?Product
    {
        $eloquentModel = ProductModel::where('name', $name)->first();
        
        return $eloquentModel ? $this->mapToDomain($eloquentModel) : null;
    }

    public function existsByName(string $name): bool
    {
        return ProductModel::where('name', $name)->exists();
    }

    public function findByStatus(ProductStatus $status): array
    {
        $eloquentModels = ProductModel::where('status', $status->toString())->get();
        
        return $eloquentModels->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    public function findActiveProducts(): array
    {
        return $this->findByStatus(ProductStatus::active());
    }

    public function findByProductType(string $productTypeId): array
    {
        $eloquentModels = ProductModel::where('product_type_id', $productTypeId)->get();
        
        return $eloquentModels->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    public function findByCriteria(array $criteria, int $limit = 10, int $offset = 0): array
    {
        $query = ProductModel::query();

        // ✅ BENEFICIO: Construcción dinámica de queries
        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['product_type_id'])) {
            $query->where('product_type_id', $criteria['product_type_id']);
        }

        if (isset($criteria['min_price'])) {
            $query->where('price', '>=', $criteria['min_price']);
        }

        if (isset($criteria['max_price'])) {
            $query->where('price', '<=', $criteria['max_price']);
        }

        if (isset($criteria['search'])) {
            $query->where(function($q) use ($criteria) {
                $q->where('name', 'like', '%' . $criteria['search'] . '%')
                  ->orWhere('description', 'like', '%' . $criteria['search'] . '%');
            });
        }

        $eloquentModels = $query->limit($limit)->offset($offset)->get();
        
        return $eloquentModels->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    public function countByStatus(ProductStatus $status): int
    {
        return ProductModel::where('status', $status->toString())->count();
    }

    public function getTotalProductsCount(): int
    {
        return ProductModel::count();
    }

    public function delete(Product $product): void
    {
        ProductModel::where('id', $product->getId())->delete();
    }

    public function searchByText(string $searchTerm, int $limit = 10): array
    {
        $eloquentModels = ProductModel::where('name', 'like', '%' . $searchTerm . '%')
            ->orWhere('description', 'like', '%' . $searchTerm . '%')
            ->limit($limit)
            ->get();
        
        return $eloquentModels->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    public function findBestSelling(int $limit = 10): array
    {
        // ✅ BENEFICIO: Queries complejas aisladas en repositorio
        $eloquentModels = ProductModel::select('products.*')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByRaw('COUNT(order_items.id) DESC')
            ->limit($limit)
            ->get();
        
        return $eloquentModels->map(fn($model) => $this->mapToDomain($model))->toArray();
    }

    public function saveMultiple(array $products): void
    {
        \DB::transaction(function() use ($products) {
            foreach ($products as $product) {
                $this->save($product);
            }
        });
    }

    /**
     * ✅ BENEFICIO: Mapeo explícito infraestructura -> dominio
     */
    private function mapToDomain(ProductModel $eloquentModel): Product
    {
        // Usar reflection para acceder al constructor privado
        $reflection = new \ReflectionClass(Product::class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);

        $product = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke(
            $product,
            $eloquentModel->id,
            $eloquentModel->name,
            $eloquentModel->description ?? '',
            ProductPrice::fromAmount($eloquentModel->price, $eloquentModel->currency ?? 'USD'),
            ProductStatus::fromString($eloquentModel->status),
            $eloquentModel->product_type_id,
            new \DateTimeImmutable($eloquentModel->created_at)
        );

        return $product;
    }

    /**
     * Buscar modelo Eloquent existente
     */
    private function findEloquentModel(string $id): ?ProductModel
    {
        return ProductModel::find($id);
    }
}

/**
 * ✅ COMPARACIÓN: MVC Tradicional vs Hexagonal
 * 
 * 🔴 MVC TRADICIONAL (PROBLEMÁTICO):
 * 
 * class ProductController extends Controller 
 * {
 *     public function index() 
 *     {
 *         // ❌ Lógica de DB mezclada con controlador
 *         $products = Product::where('status', 'active')
 *                           ->when(request('search'), function($q) {
 *                               $q->where('name', 'like', '%' . request('search') . '%');
 *                           })
 *                           ->orderBy('created_at', 'desc')
 *                           ->paginate(10);
 *         
 *         return view('products.index', compact('products'));
 *     }
 *     
 *     public function store(Request $request) 
 *     {
 *         // ❌ Sin validaciones de dominio
 *         $product = Product::create([
 *             'name' => $request->name,
 *             'price' => $request->price,
 *             'status' => 'draft' // ❌ String mágico
 *         ]);
 *         
 *         return redirect()->route('products.index');
 *     }
 * }
 * 
 * 🟢 HEXAGONAL (BENEFICIOS):
 * 
 * // ✅ Controlador limpio
 * class AdminProductController extends Controller 
 * {
 *     public function index(GetProductsRequest $request): JsonResponse 
 *     {
 *         $query = new GetProductsQuery(
 *             status: $request->input('status'),
 *             search: $request->input('search'),
 *             limit: $request->input('limit', 10)
 *         );
 *         
 *         $products = $this->getProductsUseCase->execute($query);
 *         
 *         return $this->successResponse($products);
 *     }
 * }
 * 
 * // ✅ Use Case reutilizable
 * class GetProductsUseCase 
 * {
 *     public function execute(GetProductsQuery $query): array 
 *     {
 *         $criteria = [];
 *         
 *         if ($query->status) {
 *             $criteria['status'] = $query->status;
 *         }
 *         
 *         if ($query->search) {
 *             $criteria['search'] = $query->search;
 *         }
 *         
 *         return $this->productRepository->findByCriteria($criteria, $query->limit);
 *     }
 * }
 * 
 * 🎯 BENEFICIOS CLAVE:
 * 
 * 1. SEPARACIÓN: Lógica DB separada de lógica de negocio
 * 2. TESTABILIDAD: Mock del repositorio para tests rápidos
 * 3. FLEXIBILIDAD: Cambiar de MySQL a MongoDB sin afectar dominio
 * 4. OPTIMIZACIÓN: Queries complejas aisladas en repositorio
 * 5. REUTILIZACIÓN: Mismo repositorio desde web, API, CLI
 */
