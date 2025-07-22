<?php

namespace Tests\Unit\Services;

use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Models\User;
use App\Services\ClientServiceBusinessService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceBusinessServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClientServiceBusinessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClientServiceBusinessService();
    }

    /** @test */
    public function it_extends_service_renewal_successfully()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => Carbon::now()->addDays(5),
            'status' => 'active'
        ]);

        $originalDueDate = $clientService->next_due_date;
        
        $result = $this->service->extendServiceRenewal($clientService, $billingCycle);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(30, $result['data']['days_extended']);
        
        // Verificar que la fecha se actualizó en la base de datos
        $clientService->refresh();
        $expectedDate = Carbon::parse($originalDueDate)->addDays(30);
        $this->assertEquals($expectedDate->format('Y-m-d'), $clientService->next_due_date->format('Y-m-d'));
    }

    /** @test */
    public function it_handles_extend_renewal_error()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        // Crear servicio con ID inválido para forzar error
        $clientService = new ClientService();
        $clientService->id = 99999; // ID que no existe
        $clientService->next_due_date = Carbon::now();
        
        $result = $this->service->extendServiceRenewal($clientService, $billingCycle);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_checks_if_service_can_be_renewed()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => Carbon::now()->addDays(5),
            'status' => 'active'
        ]);

        $result = $this->service->canServiceBeRenewed($clientService);
        
        $this->assertTrue($result['can_renew']);
        $this->assertEmpty($result['reasons']);
    }

    /** @test */
    public function it_prevents_renewal_for_inactive_service()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => Carbon::now()->addDays(5),
            'status' => 'cancelled'
        ]);

        $result = $this->service->canServiceBeRenewed($clientService);
        
        $this->assertFalse($result['can_renew']);
        $this->assertContains('El servicio debe estar activo o suspendido para ser renovado', $result['reasons']);
    }

    /** @test */
    public function it_prevents_renewal_for_service_without_due_date()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => null,
            'status' => 'active'
        ]);

        $result = $this->service->canServiceBeRenewed($clientService);
        
        $this->assertFalse($result['can_renew']);
        $this->assertContains('El servicio no tiene fecha de vencimiento configurada', $result['reasons']);
    }

    /** @test */
    public function it_prevents_renewal_for_overdue_service()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => Carbon::now()->subDays(35), // Vencido hace 35 días
            'status' => 'active'
        ]);

        $result = $this->service->canServiceBeRenewed($clientService);
        
        $this->assertFalse($result['can_renew']);
        $this->assertContains('El servicio está vencido por más de 30 días', $result['reasons']);
    }

    /** @test */
    public function it_calculates_next_due_date()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $currentDate = Carbon::now();
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => $currentDate,
            'status' => 'active'
        ]);

        $result = $this->service->calculateNextDueDate($clientService);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(30, $result['data']['days_to_add']);
        
        $expectedDate = $currentDate->copy()->addDays(30);
        $this->assertEquals($expectedDate->format('Y-m-d'), $result['data']['next_due_date']->format('Y-m-d'));
    }

    /** @test */
    public function it_gets_services_near_expiration()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        // Crear servicio que vence en 5 días
        ClientService::factory()->create([
            'client_id' => $client->id,
            'next_due_date' => Carbon::now()->addDays(5),
            'status' => 'active'
        ]);

        // Crear servicio que vence en 10 días (fuera del rango)
        ClientService::factory()->create([
            'client_id' => $client->id,
            'next_due_date' => Carbon::now()->addDays(10),
            'status' => 'active'
        ]);

        $result = $this->service->getServicesNearExpiration(7);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
    }

    /** @test */
    public function it_gets_service_stats()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        // Crear servicios con diferentes estados
        ClientService::factory()->create(['client_id' => $client->id, 'status' => 'active']);
        ClientService::factory()->create(['client_id' => $client->id, 'status' => 'active']);
        ClientService::factory()->create(['client_id' => $client->id, 'status' => 'suspended']);
        ClientService::factory()->create(['client_id' => $client->id, 'status' => 'cancelled']);

        $result = $this->service->getServiceStats($client->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(4, $result['data']['total']);
        $this->assertEquals(2, $result['data']['active']);
        $this->assertEquals(1, $result['data']['suspended']);
        $this->assertEquals(1, $result['data']['cancelled']);
    }

    /** @test */
    public function it_gets_global_service_stats()
    {
        $client1 = User::factory()->create(['role' => 'client']);
        $client2 = User::factory()->create(['role' => 'client']);
        
        // Crear servicios para diferentes clientes
        ClientService::factory()->create(['client_id' => $client1->id, 'status' => 'active']);
        ClientService::factory()->create(['client_id' => $client2->id, 'status' => 'active']);

        $result = $this->service->getServiceStats(); // Sin client_id
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['data']['total']);
        $this->assertEquals(2, $result['data']['active']);
    }

    /** @test */
    public function it_handles_calculate_next_due_date_without_billing_cycle()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => null,
            'next_due_date' => Carbon::now(),
            'status' => 'active'
        ]);

        $result = $this->service->calculateNextDueDate($clientService);
        
        $this->assertFalse($result['success']);
        $this->assertStringContains('No se encontró ciclo de facturación', $result['message']);
    }

    /** @test */
    public function it_handles_service_without_current_due_date()
    {
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['days' => 30]);
        
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'billing_cycle_id' => $billingCycle->id,
            'next_due_date' => null,
            'status' => 'active'
        ]);

        $result = $this->service->calculateNextDueDate($clientService);
        
        $this->assertTrue($result['success']);
        // Debería usar la fecha actual como base
        $this->assertInstanceOf(Carbon::class, $result['data']['next_due_date']);
    }
}
