<?php

namespace App\Domains\Products\Application\UseCases;

use App\Domains\Products\Application\Commands\CreateProductCommand;
use App\Domains\Products\Domain\Entities\Product;
use App\Domains\Products\Domain\ValueObjects\ProductPrice;
use App\Domains\Products\Interfaces\Domain\ProductRepositoryInterface;
use App\Domains\Products\Interfaces\Application\CreateProductUseCaseInterface;
use App\Domains\Shared\Domain\ValueObjects\Money;
use App\Domains\Shared\Application\Services\EventBus;
use Ramsey\Uuid\Uuid;

/**
 * Use Case - CreateProductUseCase
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Lógica de aplicación separada de HTTP
 * - Reutilizable desde cualquier entrada (Web, API, CLI, Queue)
 * - Testeable unitariamente sin framework
 * - Principio de Responsabilidad Única
 * - Inversión de dependencias (depende de interfaces)
 * - Transacciones y eventos manejados correctamente
 */
final readonly class CreateProductUseCase implements CreateProductUseCaseInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EventBus $eventBus
    ) {}

    /**
     * ✅ BENEFICIO: Lógica de aplicación pura, sin dependencias de HTTP
     */
    public function execute(CreateProductCommand $command): Product
    {
        // 1. ✅ Validaciones de aplicación (diferentes a las de dominio)
        $this->validateCommand($command);

        // 2. ✅ Verificar reglas de negocio de aplicación
        $this->ensureProductNameIsUnique($command->name);

        // 3. ✅ Crear entidad de dominio pura
        $product = Product::create(
            id: Uuid::uuid4()->toString(),
            name: $command->name,
            description: $command->description,
            price: ProductPrice::fromAmount($command->price, $command->currency),
            productTypeId: $command->productTypeId
        );

        // 4. ✅ Persistir usando repositorio (inversión de dependencias)
        $this->productRepository->save($product);

        // 5. ✅ Publicar eventos de dominio
        $this->publishDomainEvents($product);

        return $product;
    }

    /**
     * ✅ BENEFICIO: Validaciones de aplicación centralizadas
     */
    private function validateCommand(CreateProductCommand $command): void
    {
        if (empty(trim($command->name))) {
            throw new \InvalidArgumentException('Product name is required');
        }

        if ($command->price < 0) {
            throw new \InvalidArgumentException('Product price cannot be negative');
        }

        if (empty($command->productTypeId)) {
            throw new \InvalidArgumentException('Product type is required');
        }

        // Validar que la moneda sea válida
        if (!in_array($command->currency, ['USD', 'EUR', 'GBP'], true)) {
            throw new \InvalidArgumentException('Invalid currency');
        }
    }

    /**
     * ✅ BENEFICIO: Reglas de negocio de aplicación
     */
    private function ensureProductNameIsUnique(string $name): void
    {
        if ($this->productRepository->existsByName($name)) {
            throw new \DomainException('A product with this name already exists');
        }
    }

    /**
     * ✅ BENEFICIO: Eventos de dominio para comunicación desacoplada
     */
    private function publishDomainEvents(Product $product): void
    {
        $events = $product->getDomainEvents();
        
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        $product->clearDomainEvents();
    }
}

/**
 * ✅ COMPARACIÓN: MVC Tradicional vs Hexagonal
 * 
 * 🔴 MVC TRADICIONAL (PROBLEMÁTICO):
 * 
 * class ProductController extends Controller 
 * {
 *     public function store(Request $request) 
 *     {
 *         // ❌ Validación mezclada con lógica de negocio
 *         $request->validate([...]);
 *         
 *         // ❌ Lógica de negocio en el controlador
 *         if (Product::where('name', $request->name)->exists()) {
 *             return back()->withErrors('Name exists');
 *         }
 *         
 *         // ❌ Creación directa con Eloquent (acoplado a framework)
 *         $product = Product::create([
 *             'name' => $request->name,
 *             'price' => $request->price,
 *             // ❌ Sin validaciones de dominio
 *         ]);
 *         
 *         // ❌ Sin eventos, sin transacciones, sin separación de responsabilidades
 *         return redirect()->route('products.index');
 *     }
 * }
 * 
 * 🟢 HEXAGONAL (BENEFICIOS):
 * 
 * class AdminProductController extends Controller 
 * {
 *     public function store(StoreProductRequest $request): JsonResponse 
 *     {
 *         // ✅ Solo adaptación HTTP -> Comando
 *         $command = new CreateProductCommand(
 *             name: $request->input('name'),
 *             description: $request->input('description'),
 *             price: $request->input('price'),
 *             currency: $request->input('currency', 'USD'),
 *             productTypeId: $request->input('product_type_id')
 *         );
 *         
 *         // ✅ Delegación a caso de uso (reutilizable)
 *         $product = $this->createProductUseCase->execute($command);
 *         
 *         // ✅ Solo adaptación resultado -> HTTP
 *         return $this->successResponse($product, 'Product created successfully');
 *     }
 * }
 * 
 * 🎯 BENEFICIOS CLAVE:
 * 
 * 1. REUTILIZACIÓN: El mismo UseCase funciona desde:
 *    - Web Controller
 *    - API Controller  
 *    - CLI Command
 *    - Queue Job
 *    - GraphQL Resolver
 * 
 * 2. TESTABILIDAD: Test unitario sin framework:
 *    $useCase = new CreateProductUseCase($mockRepo, $mockEventBus);
 *    $product = $useCase->execute($command);
 *    $this->assertEquals('Test Product', $product->getName());
 * 
 * 3. ESCALABILIDAD: Fácil agregar:
 *    - Nuevos canales de entrada
 *    - Nuevas validaciones
 *    - Nuevos eventos
 *    - Nuevas reglas de negocio
 * 
 * 4. MANTENIBILIDAD: Cambios aislados:
 *    - Cambiar UI no afecta lógica
 *    - Cambiar DB no afecta lógica
 *    - Cambiar validaciones no afecta persistencia
 */
