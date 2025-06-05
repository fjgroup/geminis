<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductType;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_product_type_id(): void
    {
        // 1. Create and authenticate admin user
        $adminUser = User::factory()->create(['role' => 'admin']); // Ensure User model has 'role' attribute or adjust
        $this->actingAs($adminUser);

        // 2. Create a ProductType
        $productType = ProductType::factory()->create(['name' => 'Test Product Type Special']); // Use a unique name

        // 3. Prepare product data
        $productData = [
            'name' => 'Test Product Awesome Unique', // Use a unique name
            'description' => 'This is a test product.',
            'product_type_id' => $productType->id,
            // 'type' should not be sent, or StoreProductRequest should handle its absence if nullable
            'status' => 'active',
            'is_publicly_available' => true,
            'is_resellable_by_default' => false,
            // Ensure all other required fields from StoreProductRequest are included
            // For example, if 'slug' is not auto-generated on null, it might be needed.
            // Based on StoreProductRequest, 'slug' is nullable.
            // 'type' is now nullable in StoreProductRequest.
        ];

        // 4. Make POST request
        $response = $this->post(route('admin.products.store'), $productData);

        // 5. Assert redirect and success message
        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        // 6. Assert product exists in database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product Awesome Unique',
            'product_type_id' => $productType->id,
            'status' => 'active',
            'description' => 'This is a test product.',
            'is_publicly_available' => true,
            'is_resellable_by_default' => false,
        ]);

        // Optional: Assert that the old 'type' field is null for the created product if it still exists
        $createdProduct = Product::where('name', 'Test Product Awesome Unique')->first();
        $this->assertNotNull($createdProduct);
        // If the 'type' column still exists and is part of $fillable,
        // and was not part of $productData, its value would depend on DB default or model mutators.
        // Given StoreProductRequest makes 'type' nullable, and controller unsets it if product_type_id is present,
        // it should ideally be null or not set if not provided.
        // $this->assertNull($createdProduct->type); // This assertion depends on the final state of 'type' column.
    }
}
