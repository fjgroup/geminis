<?php

namespace Tests\Unit\Domains\BillingAndPayments\ValueObjects;

use App\Domains\BillingAndPayments\Domain\ValueObjects\TransactionStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test para TransactionStatus Value Object
 * 
 * Valida que el Value Object cumple con los principios de DDD
 * y mantiene las validaciones y transiciones de estado correctas
 */
class TransactionStatusTest extends TestCase
{
    public function test_can_create_with_valid_status(): void
    {
        $status = new TransactionStatus(TransactionStatus::PENDING);
        
        $this->assertEquals(TransactionStatus::PENDING, $status->getValue());
        $this->assertEquals('Pendiente', $status->getLabel());
    }

    public function test_throws_exception_with_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Estado de transacción inválido: invalid');
        
        new TransactionStatus('invalid');
    }

    public function test_can_create_pending_status(): void
    {
        $status = TransactionStatus::pending();
        
        $this->assertTrue($status->isPending());
        $this->assertEquals(TransactionStatus::PENDING, $status->getValue());
    }

    public function test_can_create_completed_status(): void
    {
        $status = TransactionStatus::completed();
        
        $this->assertTrue($status->isCompleted());
        $this->assertEquals(TransactionStatus::COMPLETED, $status->getValue());
    }

    public function test_can_create_failed_status(): void
    {
        $status = TransactionStatus::failed();
        
        $this->assertTrue($status->isFailed());
        $this->assertEquals(TransactionStatus::FAILED, $status->getValue());
    }

    public function test_can_create_cancelled_status(): void
    {
        $status = TransactionStatus::cancelled();
        
        $this->assertTrue($status->isCancelled());
        $this->assertEquals(TransactionStatus::CANCELLED, $status->getValue());
    }

    public function test_can_create_refunded_status(): void
    {
        $status = TransactionStatus::refunded();
        
        $this->assertTrue($status->isRefunded());
        $this->assertEquals(TransactionStatus::REFUNDED, $status->getValue());
    }

    public function test_can_create_processing_status(): void
    {
        $status = TransactionStatus::processing();
        
        $this->assertTrue($status->isProcessing());
        $this->assertEquals(TransactionStatus::PROCESSING, $status->getValue());
    }

    public function test_identifies_final_statuses_correctly(): void
    {
        $this->assertTrue(TransactionStatus::completed()->isFinal());
        $this->assertTrue(TransactionStatus::failed()->isFinal());
        $this->assertTrue(TransactionStatus::cancelled()->isFinal());
        $this->assertTrue(TransactionStatus::refunded()->isFinal());
        
        $this->assertFalse(TransactionStatus::pending()->isFinal());
        $this->assertFalse(TransactionStatus::processing()->isFinal());
    }

    public function test_pending_can_change_to_valid_statuses(): void
    {
        $pending = TransactionStatus::pending();
        
        $this->assertTrue($pending->canChangeTo(TransactionStatus::completed()));
        $this->assertTrue($pending->canChangeTo(TransactionStatus::failed()));
        $this->assertTrue($pending->canChangeTo(TransactionStatus::cancelled()));
        $this->assertTrue($pending->canChangeTo(TransactionStatus::processing()));
        
        $this->assertFalse($pending->canChangeTo(TransactionStatus::refunded()));
    }

    public function test_processing_can_change_to_valid_statuses(): void
    {
        $processing = TransactionStatus::processing();
        
        $this->assertTrue($processing->canChangeTo(TransactionStatus::completed()));
        $this->assertTrue($processing->canChangeTo(TransactionStatus::failed()));
        $this->assertTrue($processing->canChangeTo(TransactionStatus::cancelled()));
        
        $this->assertFalse($processing->canChangeTo(TransactionStatus::pending()));
        $this->assertFalse($processing->canChangeTo(TransactionStatus::refunded()));
    }

    public function test_final_statuses_cannot_change(): void
    {
        $completed = TransactionStatus::completed();
        $failed = TransactionStatus::failed();
        $cancelled = TransactionStatus::cancelled();
        $refunded = TransactionStatus::refunded();
        
        $this->assertFalse($completed->canChangeTo(TransactionStatus::pending()));
        $this->assertFalse($failed->canChangeTo(TransactionStatus::completed()));
        $this->assertFalse($cancelled->canChangeTo(TransactionStatus::processing()));
        $this->assertFalse($refunded->canChangeTo(TransactionStatus::pending()));
    }

    public function test_can_compare_statuses(): void
    {
        $status1 = TransactionStatus::pending();
        $status2 = TransactionStatus::pending();
        $status3 = TransactionStatus::completed();
        
        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
    }

    public function test_gets_all_statuses(): void
    {
        $allStatuses = TransactionStatus::getAllStatuses();
        
        $expected = [
            TransactionStatus::PENDING,
            TransactionStatus::COMPLETED,
            TransactionStatus::FAILED,
            TransactionStatus::CANCELLED,
            TransactionStatus::REFUNDED,
            TransactionStatus::PROCESSING,
        ];
        
        $this->assertEquals($expected, $allStatuses);
    }

    public function test_gets_all_labels(): void
    {
        $allLabels = TransactionStatus::getAllLabels();
        
        $expected = [
            TransactionStatus::PENDING => 'Pendiente',
            TransactionStatus::COMPLETED => 'Completada',
            TransactionStatus::FAILED => 'Fallida',
            TransactionStatus::CANCELLED => 'Cancelada',
            TransactionStatus::REFUNDED => 'Reembolsada',
            TransactionStatus::PROCESSING => 'Procesando',
        ];
        
        $this->assertEquals($expected, $allLabels);
    }

    public function test_converts_to_string(): void
    {
        $status = TransactionStatus::pending();
        
        $this->assertEquals(TransactionStatus::PENDING, $status->toString());
        $this->assertEquals(TransactionStatus::PENDING, (string) $status);
    }

    public function test_converts_to_array(): void
    {
        $status = TransactionStatus::completed();
        
        $expected = [
            'value' => TransactionStatus::COMPLETED,
            'label' => 'Completada',
            'is_final' => true,
        ];
        
        $this->assertEquals($expected, $status->toArray());
    }

    public function test_immutability(): void
    {
        $originalStatus = TransactionStatus::pending();
        
        // Los Value Objects son inmutables, no hay métodos que modifiquen el estado
        // Solo podemos crear nuevos objetos
        $newStatus = TransactionStatus::completed();
        
        $this->assertTrue($originalStatus->isPending());
        $this->assertTrue($newStatus->isCompleted());
        $this->assertNotEquals($originalStatus->getValue(), $newStatus->getValue());
    }

    /**
     * @dataProvider statusProvider
     */
    public function test_all_status_methods_work_correctly(string $statusValue, string $expectedLabel, bool $expectedIsFinal): void
    {
        $status = new TransactionStatus($statusValue);
        
        $this->assertEquals($statusValue, $status->getValue());
        $this->assertEquals($expectedLabel, $status->getLabel());
        $this->assertEquals($expectedIsFinal, $status->isFinal());
    }

    public static function statusProvider(): array
    {
        return [
            [TransactionStatus::PENDING, 'Pendiente', false],
            [TransactionStatus::COMPLETED, 'Completada', true],
            [TransactionStatus::FAILED, 'Fallida', true],
            [TransactionStatus::CANCELLED, 'Cancelada', true],
            [TransactionStatus::REFUNDED, 'Reembolsada', true],
            [TransactionStatus::PROCESSING, 'Procesando', false],
        ];
    }
}
