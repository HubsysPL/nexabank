<?php

use Modules\Core\Exceptions\InsufficientFundsException;
use Modules\Core\ValueObjects\Money;

it('throws an exception when created with a negative amount', function () {
    new Money(-100);
})->throws(\InvalidArgumentException::class, 'Kwota nie może być ujemna.');

it('can be instantiated with a zero amount', function () {
    $money = new Money(0);
    expect($money->getAmount())->toBe(0);
});

it('can be instantiated with a positive amount', function () {
    $money = new Money(1000);
    expect($money->getAmount())->toBe(1000);
});

it('adds another money value', function () {
    $money = new Money(1000);
    $other = new Money(500);
    $result = $money->add($other);

    expect($result)->toBeInstanceOf(Money::class)
        ->and($result->getAmount())->toBe(1500);
});

it('subtracts another money value', function () {
    $money = new Money(1000);
    $other = new Money(500);
    $result = $money->subtract($other);

    expect($result)->toBeInstanceOf(Money::class)
        ->and($result->getAmount())->toBe(500);
});

it('throws an exception when subtracting with insufficient funds', function () {
    $money = new Money(500);
    $other = new Money(1000);

    $money->subtract($other);
})->throws(InsufficientFundsException::class, 'Niewystarczające środki.');

it('returns correct amount', function () {
    $money = new Money(12345);
    expect($money->getAmount())->toBe(12345);
});

it('checks if the amount is zero', function () {
    $zeroMoney = new Money(0);
    $nonZeroMoney = new Money(100);

    expect($zeroMoney->isZero())->toBeTrue()
        ->and($nonZeroMoney->isZero())->toBeFalse();
});

it('checks if the amount is greater than another', function () {
    $money = new Money(1000);
    $smaller = new Money(500);
    $equal = new Money(1000);

    expect($money->greaterThan($smaller))->toBeTrue()
        ->and($money->greaterThan($equal))->toBeFalse();
});

it('can be created from hubits', function () {
    $money = Money::fromHub(123);
    expect($money->getAmount())->toBe(12300);
});

it('can create a zero value instance', function () {
    $zeroMoney = Money::zero();
    expect($zeroMoney->isZero())->toBeTrue();
});

it('converts to hubits string format', function () {
    $money = new Money(12345);
    expect($money->toHub())->toBe('123.45');

    $money = new Money(100);
    expect($money->toHub())->toBe('1.00');

    $money = new Money(50);
    expect($money->toHub())->toBe('0.50');
});
