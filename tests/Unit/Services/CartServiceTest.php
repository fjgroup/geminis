<?php

namespace Tests\Unit\Services;

use App\Contracts\CartRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Class CartServiceTest
 * 
 * Tests unitarios para CartService
 * Valida la lógica de negocio del carrito unificado
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;
    private $cartRepositoryMock;
    private $productRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepositoryMock = Mockery::mock(CartRepositoryInterface::class);
        $this->productRepositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        
        $this->cartService = new CartService(
            $this->cartRepositoryMock,
            $this->productRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_add_item_to_cart_successfully()
    {
        // Arrange
        $product = Product::factory()->make(['id' => 1, 'status' => 'active']);
        $quantity = 2;
        $configurableOptions = ['option1' => 'value1'];

        $this->productRepositoryMock
            ->shouldReceive('checkAvailability')
            ->with($product, $quantity)
            ->once()
            ->andReturn(true);

        $this->productRepositoryMock
            ->shouldReceive('validateConfigurableOptions')
            ->with($product, $configurableOptions)
            ->once()
            ->andReturn([]);

        $this->productRepositoryMock
            ->shouldReceive('calculateProductPrice')
            ->with($product, $configurableOptions, null)
            ->once()
            ->andReturn(10.00);

        $this->cartRepositoryMock
            ->shouldReceive('addItem')
            ->with($product, $quantity, Mockery::type('array'))
            ->once()
            ->andReturn(true);

        $this->cartRepositoryMock
            ->shouldReceive('getCartCount')
            ->once()
            ->andReturn(2);

        $this->cartRepositoryMock
            ->shouldReceive('getCartTotal')
            ->once()
            ->andReturn(20.00);

        // Act
        $result = $this->cartService->addItem($product, $quantity, $configurableOptions);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Producto agregado al carrito exitosamente', $result['message']);
        $this->assertEquals(2, $result['data']['cart_count']);
        $this->assertEquals(20.00, $result['data']['cart_total']);
    }

    /** @test */
    public function it_fails_to_add_item_when_product_not_available()
    {
        // Arrange
        $product = Product::factory()->make(['id' => 1, 'status' => 'active']);
        $quantity = 5;

        $this->productRepositoryMock
            ->shouldReceive('checkAvailability')
            ->with($product, $quantity)
            ->once()
            ->andReturn(false);

        // Act
        $result = $this->cartService->addItem($product, $quantity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Producto no disponible en la cantidad solicitada', $result['message']);
    }

    /** @test */
    public function it_fails_to_add_item_with_invalid_configurable_options()
    {
        // Arrange
        $product = Product::factory()->make(['id' => 1, 'status' => 'active']);
        $quantity = 1;
        $configurableOptions = ['invalid_option' => 'value'];

        $this->productRepositoryMock
            ->shouldReceive('checkAvailability')
            ->with($product, $quantity)
            ->once()
            ->andReturn(true);

        $this->productRepositoryMock
            ->shouldReceive('validateConfigurableOptions')
            ->with($product, $configurableOptions)
            ->once()
            ->andReturn(['Opción inválida']);

        // Act
        $result = $this->cartService->addItem($product, $quantity, $configurableOptions);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Opciones configurables inválidas', $result['message']);
        $this->assertEquals(['Opción inválida'], $result['errors']);
    }

    /** @test */
    public function it_can_update_quantity_successfully()
    {
        // Arrange
        $productId = 1;
        $quantity = 3;
        $product = Product::factory()->make(['id' => $productId]);

        $this->productRepositoryMock
            ->shouldReceive('findWithRelations')
            ->with($productId)
            ->once()
            ->andReturn($product);

        $this->productRepositoryMock
            ->shouldReceive('checkAvailability')
            ->with($product, $quantity)
            ->once()
            ->andReturn(true);

        $this->cartRepositoryMock
            ->shouldReceive('updateQuantity')
            ->with($productId, $quantity)
            ->once()
            ->andReturn(true);

        $this->cartRepositoryMock
            ->shouldReceive('getCartCount')
            ->once()
            ->andReturn(3);

        $this->cartRepositoryMock
            ->shouldReceive('getCartTotal')
            ->once()
            ->andReturn(30.00);

        // Act
        $result = $this->cartService->updateQuantity($productId, $quantity);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Cantidad actualizada exitosamente', $result['message']);
    }

    /** @test */
    public function it_removes_item_when_quantity_is_zero()
    {
        // Arrange
        $productId = 1;
        $quantity = 0;

        $this->cartRepositoryMock
            ->shouldReceive('removeItem')
            ->with($productId)
            ->once()
            ->andReturn(true);

        $this->cartRepositoryMock
            ->shouldReceive('getCartCount')
            ->once()
            ->andReturn(0);

        $this->cartRepositoryMock
            ->shouldReceive('getCartTotal')
            ->once()
            ->andReturn(0.00);

        // Act
        $result = $this->cartService->updateQuantity($productId, $quantity);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Producto removido del carrito', $result['message']);
    }

    /** @test */
    public function it_can_remove_item_successfully()
    {
        // Arrange
        $productId = 1;

        $this->cartRepositoryMock
            ->shouldReceive('removeItem')
            ->with($productId)
            ->once()
            ->andReturn(true);

        $this->cartRepositoryMock
            ->shouldReceive('getCartCount')
            ->once()
            ->andReturn(0);

        $this->cartRepositoryMock
            ->shouldReceive('getCartTotal')
            ->once()
            ->andReturn(0.00);

        // Act
        $result = $this->cartService->removeItem($productId);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Producto removido del carrito', $result['message']);
    }

    /** @test */
    public function it_can_get_cart_details_successfully()
    {
        // Arrange
        $cartData = [
            1 => ['product' => 'Product 1', 'quantity' => 2, 'price' => 20.00]
        ];

        $this->cartRepositoryMock
            ->shouldReceive('getCart')
            ->once()
            ->andReturn($cartData);

        $this->cartRepositoryMock
            ->shouldReceive('getCartTotal')
            ->once()
            ->andReturn(20.00);

        $this->cartRepositoryMock
            ->shouldReceive('getCartCount')
            ->once()
            ->andReturn(2);

        $this->cartRepositoryMock
            ->shouldReceive('getAppliedDiscount')
            ->once()
            ->andReturn(null);

        $this->cartRepositoryMock
            ->shouldReceive('validateCartIntegrity')
            ->once()
            ->andReturn([]);

        // Act
        $result = $this->cartService->getCartDetails();

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals($cartData, $result['data']['items']);
        $this->assertEquals(20.00, $result['data']['total']);
        $this->assertEquals(2, $result['data']['count']);
        $this->assertTrue($result['data']['is_valid']);
    }

    /** @test */
    public function it_can_clear_cart_successfully()
    {
        // Arrange
        $this->cartRepositoryMock
            ->shouldReceive('clearCart')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->cartService->clearCart();

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Carrito limpiado exitosamente', $result['message']);
    }

    /** @test */
    public function it_validates_add_item_input()
    {
        // Arrange
        $product = Product::factory()->make(['id' => 1, 'status' => 'inactive']);
        $quantity = 0;

        // Act
        $result = $this->cartService->addItem($product, $quantity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Error interno del servidor', $result['message']);
    }
}
