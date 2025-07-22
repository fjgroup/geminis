<?php

namespace Tests\Unit\Services;

use App\Models\ClientService;
use App\Models\Product;
use App\Models\User;
use App\Services\ClientServiceManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Class ClientServiceManagementServiceTest
 * 
 * Tests unitarios para ClientServiceManagementService
 * Valida la gestión de servicios de clientes
 */
class ClientServiceManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClientServiceManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ClientServiceManagementService::class);
    }

    /** @test */
    public function it_can_create_client_service_successfully()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        
        $data = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'domain_name' => 'test.example.com',
            'status' => 'pending',
            'notes' => 'Test service'
        ];

        // Act
        $result = $this->service->createClientService($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Servicio de cliente creado exitosamente', $result['message']);
        $this->assertInstanceOf(ClientService::class, $result['data']);
        $this->assertDatabaseHas('client_services', [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'domain_name' => 'test.example.com'
        ]);
    }

    /** @test */
    public function it_fails_to_create_service_with_invalid_client()
    {
        // Arrange
        $product = Product::factory()->create(['status' => 'active']);
        
        $data = [
            'client_id' => 999, // Cliente inexistente
            'product_id' => $product->id,
            'domain_name' => 'test.example.com',
            'status' => 'pending'
        ];

        // Act
        $result = $this->service->createClientService($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('Cliente no encontrado', $result['message']);
    }

    /** @test */
    public function it_fails_to_create_service_with_invalid_product()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        
        $data = [
            'client_id' => $client->id,
            'product_id' => 999, // Producto inexistente
            'domain_name' => 'test.example.com',
            'status' => 'pending'
        ];

        // Act
        $result = $this->service->createClientService($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('Producto no encontrado', $result['message']);
    }

    /** @test */
    public function it_can_update_client_service_successfully()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'status' => 'active',
            'notes' => 'Updated notes'
        ];

        // Act
        $result = $this->service->updateClientService($clientService, $updateData);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Servicio de cliente actualizado exitosamente', $result['message']);
        $this->assertDatabaseHas('client_services', [
            'id' => $clientService->id,
            'status' => 'active',
            'notes' => 'Updated notes'
        ]);
    }

    /** @test */
    public function it_can_delete_client_service_successfully()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);

        // Act
        $result = $this->service->deleteClientService($clientService);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Servicio de cliente eliminado exitosamente', $result['message']);
        $this->assertDatabaseMissing('client_services', [
            'id' => $clientService->id
        ]);
    }

    /** @test */
    public function it_prevents_deletion_of_active_service()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'status' => 'active'
        ]);

        // Act
        $result = $this->service->deleteClientService($clientService);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('No se puede eliminar un servicio activo', $result['message']);
        $this->assertDatabaseHas('client_services', [
            'id' => $clientService->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_get_client_services_with_filters()
    {
        // Arrange
        $client1 = User::factory()->create(['role' => 'client', 'name' => 'John Doe']);
        $client2 = User::factory()->create(['role' => 'client', 'name' => 'Jane Smith']);
        $product = Product::factory()->create(['status' => 'active']);
        
        ClientService::factory()->create([
            'client_id' => $client1->id,
            'product_id' => $product->id,
            'status' => 'active'
        ]);
        
        ClientService::factory()->create([
            'client_id' => $client2->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);

        $filters = [
            'status' => 'active'
        ];

        // Act
        $result = $this->service->getClientServices($filters, 10);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']->items());
        $this->assertEquals('active', $result['data']->items()[0]->status);
    }

    /** @test */
    public function it_can_retry_provisioning_successfully()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'status' => 'provisioning_failed'
        ]);

        // Act
        $result = $this->service->retryProvisioning($clientService);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Reintento de aprovisionamiento iniciado', $result['message']);
        $this->assertDatabaseHas('client_services', [
            'id' => $clientService->id,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_fails_to_retry_provisioning_for_active_service()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'status' => 'active'
        ]);

        // Act
        $result = $this->service->retryProvisioning($clientService);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('Solo se puede reintentar el aprovisionamiento', $result['message']);
    }

    /** @test */
    public function it_can_get_form_data_successfully()
    {
        // Arrange
        $clients = User::factory()->count(3)->create(['role' => 'client']);
        $products = Product::factory()->count(2)->create(['status' => 'active']);

        // Act
        $result = $this->service->getFormData();

        // Assert
        $this->assertArrayHasKey('clients', $result);
        $this->assertArrayHasKey('products', $result);
        $this->assertCount(3, $result['clients']);
        $this->assertCount(2, $result['products']);
    }

    /** @test */
    public function it_logs_errors_appropriately()
    {
        // Arrange
        Log::shouldReceive('error')->once();
        
        $data = [
            'client_id' => null, // Datos inválidos para forzar error
            'product_id' => null,
            'domain_name' => '',
        ];

        // Act
        $result = $this->service->createClientService($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Error interno del servidor', $result['message']);
    }

    /** @test */
    public function it_validates_domain_name_format()
    {
        // Arrange
        $client = User::factory()->create(['role' => 'client']);
        $product = Product::factory()->create(['status' => 'active']);
        
        $data = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'domain_name' => 'invalid-domain', // Dominio inválido
            'status' => 'pending'
        ];

        // Act
        $result = $this->service->createClientService($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('formato de dominio', $result['message']);
    }
}
