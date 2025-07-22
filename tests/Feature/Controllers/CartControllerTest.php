<?php

namespace Tests\Feature\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class CartControllerTest
 * 
 * Tests de integración para CartController unificado
 * Valida el comportamiento HTTP del carrito
 */
class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function anonymous_user_can_view_cart()
    {
        // Act
        $response = $this->get(route('cart.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Cart/Index')
                 ->has('cart')
                 ->where('cart.count', 0)
        );
    }

    /** @test */
    public function authenticated_user_can_view_cart()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);

        // Act
        $response = $this->actingAs($user)->get(route('cart.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Cart/Index')
                 ->has('cart')
        );
    }

    /** @test */
    public function anonymous_user_can_add_product_to_cart()
    {
        // Arrange
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 2,
            'configurable_options' => []
        ];

        // Act
        $response = $this->postJson(route('cart.add'), $data);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Producto agregado al carrito exitosamente'
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'cart_count',
                'cart_total'
            ]
        ]);
    }

    /** @test */
    public function authenticated_user_can_add_product_to_cart()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ];

        // Act
        $response = $this->actingAs($user)->postJson(route('cart.add'), $data);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_adding_to_cart()
    {
        // Arrange
        $data = [
            // Faltan campos requeridos
        ];

        // Act
        $response = $this->postJson(route('cart.add'), $data);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    /** @test */
    public function it_prevents_adding_inactive_product_to_cart()
    {
        // Arrange
        $product = Product::factory()->create([
            'status' => 'inactive'
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ];

        // Act
        $response = $this->postJson(route('cart.add'), $data);

        // Assert
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function it_prevents_adding_more_than_available_stock()
    {
        // Arrange
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 2
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 5, // Más que el stock disponible
            'configurable_options' => []
        ];

        // Act
        $response = $this->postJson(route('cart.add'), $data);

        // Assert
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Producto no disponible en la cantidad solicitada'
        ]);
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        // Primero agregar producto al carrito
        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ]);

        $updateData = [
            'product_id' => $product->id,
            'quantity' => 3
        ];

        // Act
        $response = $this->actingAs($user)->postJson(route('cart.update'), $updateData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cantidad actualizada exitosamente'
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        // Primero agregar producto al carrito
        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ]);

        $removeData = [
            'product_id' => $product->id
        ];

        // Act
        $response = $this->actingAs($user)->postJson(route('cart.remove'), $removeData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Producto removido del carrito'
        ]);
    }

    /** @test */
    public function user_can_clear_entire_cart()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        // Primero agregar producto al carrito
        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ]);

        // Act
        $response = $this->actingAs($user)->postJson(route('cart.clear'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Carrito limpiado exitosamente'
        ]);
    }

    /** @test */
    public function user_can_get_cart_summary()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);

        // Act
        $response = $this->actingAs($user)->getJson(route('cart.summary'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'count',
                'total',
                'items',
                'is_valid'
            ]
        ]);
    }

    /** @test */
    public function cart_validates_integrity()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'client']);

        // Act
        $response = $this->actingAs($user)->getJson(route('cart.validate'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'is_valid',
                'errors'
            ]
        ]);
    }

    /** @test */
    public function cart_respects_rate_limiting()
    {
        // Arrange
        $product = Product::factory()->create([
            'status' => 'active',
            'stock_quantity' => 10
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 1,
            'configurable_options' => []
        ];

        // Act - Hacer muchas requests rápidamente
        for ($i = 0; $i < 25; $i++) {
            $response = $this->postJson(route('cart.add'), $data);
        }

        // Assert - La última request debería ser rate limited
        $response->assertStatus(429);
    }
}
