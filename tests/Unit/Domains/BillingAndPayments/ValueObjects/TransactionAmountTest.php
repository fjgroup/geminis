<?php

namespace Tests\Unit\Domains\BillingAndPayments\ValueObjects;

use App\Domains\BillingAndPayments\Domain\ValueObjects\TransactionAmount;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test para TransactionAmount Value Object
 * 
 * Valida que el Value Object cumple con los principios de DDD
 * y mantiene la inmutabilidad y validaciones correctas
 */
class TransactionAmountTest extends TestCase
{
    public function test_can_create_transaction_amount_with_valid_data(): void
    {
        $amount = new TransactionAmount(100.50, 'USD');
        
        $this->assertEquals(100.50, $amount->getAmount());
        $this->assertEquals('USD', $amount->getCurrency());
    }

    public function test_rounds_amount_to_two_decimals(): void
    {
        $amount = new TransactionAmount(100.555, 'USD');
        
        $this->assertEquals(100.56, $amount->getAmount());
    }

    public function test_converts_currency_to_uppercase(): void
    {
        $amount = new TransactionAmount(100.00, 'usd');
        
        $this->assertEquals('USD', $amount->getCurrency());
    }

    public function test_can_create_from_string(): void
    {
        $amount = TransactionAmount::fromString('100.50', 'EUR');
        
        $this->assertEquals(100.50, $amount->getAmount());
        $this->assertEquals('EUR', $amount->getCurrency());
    }

    public function test_can_create_zero_amount(): void
    {
        $amount = TransactionAmount::zero('USD');
        
        $this->assertEquals(0.0, $amount->getAmount());
        $this->assertTrue($amount->isZero());
    }

    public function test_validates_amount_is_finite(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El monto debe ser un número finito');
        
        new TransactionAmount(INF, 'USD');
    }

    public function test_validates_currency_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La moneda debe ser un código de 3 caracteres');
        
        new TransactionAmount(100.00, 'US');
    }

    public function test_validates_empty_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La moneda debe ser un código de 3 caracteres');
        
        new TransactionAmount(100.00, '');
    }

    public function test_can_check_if_positive(): void
    {
        $positiveAmount = new TransactionAmount(100.00, 'USD');
        $zeroAmount = new TransactionAmount(0.00, 'USD');
        $negativeAmount = new TransactionAmount(-100.00, 'USD');
        
        $this->assertTrue($positiveAmount->isPositive());
        $this->assertFalse($zeroAmount->isPositive());
        $this->assertFalse($negativeAmount->isPositive());
    }

    public function test_can_check_if_negative(): void
    {
        $positiveAmount = new TransactionAmount(100.00, 'USD');
        $zeroAmount = new TransactionAmount(0.00, 'USD');
        $negativeAmount = new TransactionAmount(-100.00, 'USD');
        
        $this->assertFalse($positiveAmount->isNegative());
        $this->assertFalse($zeroAmount->isNegative());
        $this->assertTrue($negativeAmount->isNegative());
    }

    public function test_can_add_amounts_with_same_currency(): void
    {
        $amount1 = new TransactionAmount(100.00, 'USD');
        $amount2 = new TransactionAmount(50.00, 'USD');
        
        $result = $amount1->add($amount2);
        
        $this->assertEquals(150.00, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function test_cannot_add_amounts_with_different_currencies(): void
    {
        $amount1 = new TransactionAmount(100.00, 'USD');
        $amount2 = new TransactionAmount(50.00, 'EUR');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se pueden operar montos con diferentes monedas: USD vs EUR');
        
        $amount1->add($amount2);
    }

    public function test_can_subtract_amounts(): void
    {
        $amount1 = new TransactionAmount(100.00, 'USD');
        $amount2 = new TransactionAmount(30.00, 'USD');
        
        $result = $amount1->subtract($amount2);
        
        $this->assertEquals(70.00, $result->getAmount());
    }

    public function test_can_multiply_amount(): void
    {
        $amount = new TransactionAmount(100.00, 'USD');
        
        $result = $amount->multiply(1.5);
        
        $this->assertEquals(150.00, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function test_can_compare_amounts(): void
    {
        $amount1 = new TransactionAmount(100.00, 'USD');
        $amount2 = new TransactionAmount(100.00, 'USD');
        $amount3 = new TransactionAmount(50.00, 'USD');
        
        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
    }

    public function test_can_compare_greater_than(): void
    {
        $amount1 = new TransactionAmount(100.00, 'USD');
        $amount2 = new TransactionAmount(50.00, 'USD');
        
        $this->assertTrue($amount1->greaterThan($amount2));
        $this->assertFalse($amount2->greaterThan($amount1));
    }

    public function test_can_compare_less_than(): void
    {
        $amount1 = new TransactionAmount(50.00, 'USD');
        $amount2 = new TransactionAmount(100.00, 'USD');
        
        $this->assertTrue($amount1->lessThan($amount2));
        $this->assertFalse($amount2->lessThan($amount1));
    }

    public function test_formats_correctly(): void
    {
        $amount = new TransactionAmount(1234.56, 'USD');
        
        $this->assertEquals('1,234.56 USD', $amount->format());
    }

    public function test_converts_to_string(): void
    {
        $amount = new TransactionAmount(100.00, 'USD');
        
        $this->assertEquals('100.00 USD', $amount->toString());
        $this->assertEquals('100.00 USD', (string) $amount);
    }

    public function test_converts_to_array(): void
    {
        $amount = new TransactionAmount(100.50, 'USD');
        
        $expected = [
            'amount' => 100.50,
            'currency' => 'USD',
            'formatted' => '100.50 USD'
        ];
        
        $this->assertEquals($expected, $amount->toArray());
    }

    public function test_immutability(): void
    {
        $originalAmount = new TransactionAmount(100.00, 'USD');
        $newAmount = $originalAmount->add(new TransactionAmount(50.00, 'USD'));
        
        // El monto original no debe cambiar
        $this->assertEquals(100.00, $originalAmount->getAmount());
        $this->assertEquals(150.00, $newAmount->getAmount());
    }
}
