<?php

namespace Tests\Unit;

use Modules\Core\Exceptions\InsufficientFundsException;
use Modules\Core\ValueObjects\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_created_with_a_negative_amount()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money(-1);
    }

    /** @test */
    public function it_can_be_instantiated_with_a_zero_amount()
    {
        $money = new Money(0);
        $this->assertEquals(0, $money->getAmount());
    }

    /** @test */
    public function it_can_be_instantiated_with_a_positive_amount()
    {
        $money = new Money(100);
        $this->assertEquals(100, $money->getAmount());
    }

    /** @test */
    public function it_adds_another_money_value()
    {
        $money = new Money(100);
        $result = $money->add(new Money(50));
        $this->assertEquals(150, $result->getAmount());
    }

    /** @test */
    public function it_subtracts_another_money_value()
    {
        $money = new Money(100);
        $result = $money->subtract(new Money(50));
        $this->assertEquals(50, $result->getAmount());
    }
    
    /** @test */
    public function it_returns_correct_amount()
    {
        $money = new Money(100);
        $this->assertEquals(100, $money->getAmount());
    }
    
    /** @test */
    public function it_checks_if_the_amount_is_zero()
    {
        $money = new Money(0);
        $this->assertTrue($money->isZero());
        
        $money = new Money(1);
        $this->assertFalse($money->isZero());
    }
    
    /** @test */
    public function it_checks_if_the_amount_is_greater_than_another()
    {
        $money = new Money(100);
        $this->assertTrue($money->greaterThan(new Money(50)));
        $this->assertFalse($money->greaterThan(new Money(100)));
        $this->assertFalse($money->greaterThan(new Money(150)));
    }
    
    /** @test */
    public function it_can_be_created_from_hub()
    {
        $money = Money::fromHub(1);
        $this->assertEquals(100, $money->getAmount());
    }

    /** @test */
    public function it_can_create_a_zero_value_instance()
    {
        $money = Money::zero();
        $this->assertEquals(0, $money->getAmount());
    }

    /** @test */
    public function it_converts_to_hub_string_format()
    {
        $money = new Money(12345);
        $this->assertEquals('123.45', $money->toHub());
    }
}