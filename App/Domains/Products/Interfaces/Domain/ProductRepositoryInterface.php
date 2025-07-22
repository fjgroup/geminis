<?php

namespace App\Domains\Products\Interfaces\Domain;

use App\Domains\Products\Domain\Entities\Product;
use App\Domains\Products\Domain\ValueObjects\ProductStatus;

/**
 * Interface - ProductRepositoryInterface
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Inversión de dependencias (SOLID)
 * - Fácil testing con mocks
 * - Intercambiable implementación (MySQL, PostgreSQL, MongoDB, etc.)
 * - Dominio no depende de infraestructura
 * - Contratos claros y explícitos
 */
interface ProductRepositoryInterface
{
    /**
     * ✅ BENEFICIO: Trabajamos con entidades de dominio, no arrays
     */
    public function save(Product $product): void;

    /**
     * ✅ BENEFICIO: Retorna entidad de dominio o null
     */
    public function findById(string $id): ?Product;

    /**
     * Buscar por nombre
     */
    public function findByName(string $name): ?Product;

    /**
     * ✅ BENEFICIO: Verificación de existencia sin cargar entidad completa
     */
    public function existsByName(string $name): bool;

    /**
     * Buscar productos por estado
     */
    public function findByStatus(ProductStatus $status): array;

    /**
     * Buscar productos activos
     */
    public function findActiveProducts(): array;

    /**
     * Buscar productos por tipo
     */
    public function findByProductType(string $productTypeId): array;

    /**
     * ✅ BENEFICIO: Búsqueda con criterios complejos
     */
    public function findByCriteria(array $criteria, int $limit = 10, int $offset = 0): array;

    /**
     * Contar productos por estado
     */
    public function countByStatus(ProductStatus $status): int;

    /**
     * ✅ BENEFICIO: Operaciones de agregado
     */
    public function getTotalProductsCount(): int;

    /**
     * Eliminar producto
     */
    public function delete(Product $product): void;

    /**
     * ✅ BENEFICIO: Búsqueda de texto completo
     */
    public function searchByText(string $searchTerm, int $limit = 10): array;

    /**
     * Obtener productos más vendidos
     */
    public function findBestSelling(int $limit = 10): array;

    /**
     * ✅ BENEFICIO: Transacciones manejadas por el repositorio
     */
    public function saveMultiple(array $products): void;
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
 *         // ❌ Acoplado directamente a Eloquent
 *         $products = Product::where('status', 'active')
 *                           ->orderBy('created_at', 'desc')
 *                           ->paginate(10);
 *         
 *         // ❌ Lógica de presentación mezclada
 *         return view('products.index', compact('products'));
 *     }
 *     
 *     public function store(Request $request) 
 *     {
 *         // ❌ Validación y persistencia mezcladas
 *         $product = Product::create($request->all());
 *         
 *         // ❌ Sin abstracción, difícil de testear
 *         return redirect()->route('products.index');
 *     }
 * }
 * 
 * 🟢 HEXAGONAL (BENEFICIOS):
 * 
 * class AdminProductController extends Controller 
 * {
 *     public function __construct(
 *         private GetProductsUseCaseInterface $getProductsUseCase,
 *         private CreateProductUseCaseInterface $createProductUseCase
 *     ) {}
 *     
 *     public function index(): JsonResponse 
 *     {
 *         // ✅ Delegación a caso de uso
 *         $query = new GetProductsQuery(status: 'active', limit: 10);
 *         $products = $this->getProductsUseCase->execute($query);
 *         
 *         // ✅ Solo adaptación a HTTP
 *         return $this->successResponse($products);
 *     }
 *     
 *     public function store(StoreProductRequest $request): JsonResponse 
 *     {
 *         // ✅ Comando inmutable y type-safe
 *         $command = CreateProductCommand::fromArray($request->validated());
 *         $product = $this->createProductUseCase->execute($command);
 *         
 *         return $this->successResponse($product, 'Product created');
 *     }
 * }
 * 
 * // ✅ Implementación intercambiable
 * class EloquentProductRepository implements ProductRepositoryInterface 
 * {
 *     public function save(Product $product): void 
 *     {
 *         // Mapear entidad de dominio -> modelo Eloquent
 *         $eloquentModel = $this->mapToEloquent($product);
 *         $eloquentModel->save();
 *     }
 *     
 *     public function findById(string $id): ?Product 
 *     {
 *         $eloquentModel = ProductModel::find($id);
 *         return $eloquentModel ? $this->mapToDomain($eloquentModel) : null;
 *     }
 * }
 * 
 * // ✅ Testing fácil con mocks
 * class CreateProductUseCaseTest extends TestCase 
 * {
 *     public function test_creates_product_successfully() 
 *     {
 *         $mockRepo = $this->createMock(ProductRepositoryInterface::class);
 *         $mockEventBus = $this->createMock(EventBus::class);
 *         
 *         $useCase = new CreateProductUseCase($mockRepo, $mockEventBus);
 *         $command = new CreateProductCommand('Test Product', 'Description', 99.99, 'USD', 'type-1');
 *         
 *         $product = $useCase->execute($command);
 *         
 *         $this->assertEquals('Test Product', $product->getName());
 *         $this->assertTrue($product->getStatus()->isDraft());
 *     }
 * }
 * 
 * 🎯 BENEFICIOS CLAVE:
 * 
 * 1. TESTABILIDAD: Mocks fáciles, tests rápidos
 * 2. FLEXIBILIDAD: Cambiar DB sin afectar lógica
 * 3. ESCALABILIDAD: Agregar cache, sharding, etc.
 * 4. MANTENIBILIDAD: Contratos claros
 * 5. REUTILIZACIÓN: Misma lógica, diferentes persistencias
 */
