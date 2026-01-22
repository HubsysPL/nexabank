<?php

namespace Modules\Core\ValueObjects;
final class Money
{
    private int $amount; // hubity

    public function __construct(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Kwota nie może być ujemna.');
        }

        $this->amount = $amount;
    }

    public function add(Money $other): Money
    {
        return new Money($this->amount + $other->amount);
    }

    public function subtract(Money $other): Money
    {
        if ($this->amount < $other->amount) {
            throw new InsufficientFundsException();
        }

        return new Money($this->amount - $other->amount);
    }
}
