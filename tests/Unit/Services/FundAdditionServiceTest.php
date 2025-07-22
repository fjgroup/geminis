<?php

namespace Tests\Unit\Services;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FundAdditionService;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FundAdditionServiceTest extends TestCase
{
    use RefreshDatabase;

    private FundAdditionService $service;
    private PayPalService $mockPayPalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPayPalService = Mockery::mock(PayPalService::class);
        $this->service = new FundAdditionService($this->mockPayPalService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_gets_form_data_successfully()
    {
        $client = User::factory()->create([
            'role' => 'client',
            'balance' => 100.50,
            'currency_code' => 'USD'
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'is_active' => true,
            'name' => 'Bank Transfer'
        ]);

        $result = $this->service->getFormData($client);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('paymentMethods', $result['data']);
        $this->assertEquals('USD', $result['data']['currencyCode']);
        $this->assertEquals(100.50, $result['data']['currentBalance']);
    }

    /** @test */
    public function it_processes_manual_fund_addition_successfully()
    {
        $client = User::factory()->create(['role' => 'client']);
        $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

        $data = [
            'payment_method_id' => $paymentMethod->id,
            'amount' => 100.00,
            'reference_number' => 'REF123456',
            'payment_date' => now()->format('Y-m-d')
        ];

        $result = $this->service->processManualFundAddition($client, $data);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Transaction::class, $result['data']);

        // Verificar que se creó la transacción
        $this->assertDatabaseHas('transactions', [
            'client_id' => $client->id,
            'amount' => 100.00,
            'status' => 'pending',
            'type' => 'credit_added'
        ]);
    }

    /** @test */
    public function it_validates_manual_fund_addition_data()
    {
        $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

        // Datos válidos
        $validData = [
            'amount' => 100.00,
            'payment_method_id' => $paymentMethod->id,
            'reference_number' => 'REF123456',
            'payment_date' => now()->format('Y-m-d')
        ];

        $result = $this->service->validateManualFundAddition($validData);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        // Datos inválidos
        $invalidData = [
            'amount' => -10,
            'payment_method_id' => 99999,
            'reference_number' => '',
            'payment_date' => 'invalid-date'
        ];

        $result = $this->service->validateManualFundAddition($invalidData);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @test */
    public function it_initiates_paypal_payment_successfully()
    {
        $client = User::factory()->create([
            'role' => 'client',
            'currency_code' => 'USD'
        ]);

        $this->mockPayPalService
            ->shouldReceive('createFundAdditionOrder')
            ->once()
            ->andReturn([
                'approval_link' => 'https://paypal.com/approve/123',
                'order_id' => 'PAYPAL123'
            ]);

        $result = $this->service->initiatePayPalPayment($client, 50.00);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('approval_link', $result['data']);
        $this->assertArrayHasKey('session_data', $result['data']);
    }

    /** @test */
    public function it_rejects_paypal_payment_below_minimum()
    {
        $client = User::factory()->create(['role' => 'client']);

        $result = $this->service->initiatePayPalPayment($client, 25.00);

        $this->assertFalse($result['success']);
        $this->assertStringContains('monto mínimo es de $30.00', $result['message']);
    }

    /** @test */
    public function it_handles_paypal_success_payment()
    {
        $client = User::factory()->create([
            'role' => 'client',
            'balance' => 100.00
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'slug' => 'paypal',
            'is_active' => true
        ]);

        $sessionData = [
            'paypal_fund_order_id' => 'PAYPAL123',
            'paypal_fund_amount' => 50.00,
            'paypal_fund_currency' => 'USD'
        ];

        $this->mockPayPalService
            ->shouldReceive('captureOrder')
            ->once()
            ->with('PAYPAL123')
            ->andReturn([
                'status' => 'COMPLETED',
                'paypal_capture_id' => 'CAPTURE123',
                'paypal_fee' => 2.50
            ]);

        $result = $this->service->handlePayPalSuccess($client, $sessionData);

        $this->assertTrue($result['success']);

        // Verificar que se actualizó el balance
        $client->refresh();
        $this->assertEquals(150.00, $client->balance);

        // Verificar que se creó la transacción
        $this->assertDatabaseHas('transactions', [
            'client_id' => $client->id,
            'amount' => 50.00,
            'status' => 'completed',
            'gateway_slug' => 'paypal'
        ]);
    }

    /** @test */
    public function it_handles_paypal_success_with_invalid_session()
    {
        $client = User::factory()->create(['role' => 'client']);

        $invalidSessionData = [
            'paypal_fund_order_id' => null,
            'paypal_fund_amount' => null,
            'paypal_fund_currency' => null
        ];

        $result = $this->service->handlePayPalSuccess($client, $invalidSessionData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Sesión inválida', $result['message']);
    }

    /** @test */
    public function it_handles_paypal_capture_failure()
    {
        $client = User::factory()->create(['role' => 'client']);

        $sessionData = [
            'paypal_fund_order_id' => 'PAYPAL123',
            'paypal_fund_amount' => 50.00,
            'paypal_fund_currency' => 'USD'
        ];

        $this->mockPayPalService
            ->shouldReceive('captureOrder')
            ->once()
            ->andReturn([
                'status' => 'FAILED'
            ]);

        $result = $this->service->handlePayPalSuccess($client, $sessionData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Falló la captura del pago', $result['message']);
    }

    /** @test */
    public function it_handles_paypal_cancellation()
    {
        $client = User::factory()->create(['role' => 'client']);

        $result = $this->service->handlePayPalCancel($client);

        $this->assertTrue($result['success']);
        $this->assertStringContains('cancelada', $result['message']);
    }

    /** @test */
    public function it_gets_fund_addition_history()
    {
        $client = User::factory()->create(['role' => 'client']);
        $paymentMethod = PaymentMethod::factory()->create();

        // Crear algunas transacciones de adición de fondos
        Transaction::factory()->count(3)->create([
            'client_id' => $client->id,
            'type' => 'credit_added',
            'payment_method_id' => $paymentMethod->id
        ]);

        // Crear una transacción de otro tipo (no debería aparecer)
        Transaction::factory()->create([
            'client_id' => $client->id,
            'type' => 'payment'
        ]);

        $history = $this->service->getFundAdditionHistory($client, 10);

        $this->assertCount(3, $history);
    }

    /** @test */
    public function it_gets_fund_addition_stats()
    {
        $client = User::factory()->create(['role' => 'client']);

        // Crear transacciones completadas
        Transaction::factory()->count(2)->create([
            'client_id' => $client->id,
            'type' => 'credit_added',
            'status' => 'completed',
            'amount' => 100.00
        ]);

        // Crear transacción pendiente
        Transaction::factory()->create([
            'client_id' => $client->id,
            'type' => 'credit_added',
            'status' => 'pending',
            'amount' => 50.00
        ]);

        $result = $this->service->getFundAdditionStats($client);

        $this->assertTrue($result['success']);
        $this->assertEquals(200.00, $result['data']['total_added']);
        $this->assertEquals(1, $result['data']['pending_additions']);
    }

    /** @test */
    public function it_handles_paypal_service_exception()
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->mockPayPalService
            ->shouldReceive('createFundAdditionOrder')
            ->once()
            ->andThrow(new \Exception('PayPal service error'));

        $result = $this->service->initiatePayPalPayment($client, 50.00);

        $this->assertFalse($result['success']);
        $this->assertStringContains('error inesperado', $result['message']);
    }

    /** @test */
    public function it_handles_database_error_in_manual_addition()
    {
        $client = User::factory()->create(['role' => 'client']);

        $data = [
            'payment_method_id' => 99999, // ID inválido
            'amount' => 100.00,
            'reference_number' => 'REF123456',
            'payment_date' => now()->format('Y-m-d')
        ];

        $result = $this->service->processManualFundAddition($client, $data);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
}
