<?php

namespace Modules\Core\ValueObjects;

final class Money
{
    private int $amount; // hubity

    public function __construct(int $amount)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Kwota nie może być ujemna.');
        }

        $this->amount = $amount;
    }

    public function add(Money $other): Money
    {
        return new Money($this->amount + $other->amount);
    }

    public function subtract(Money $other): Money
    {
        return new Money($this->amount - $other->amount);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function greaterThan(Money $other): bool
    {
        return $this->amount > $other->amount;
    }

    public static function fromHub(int $hub): self
    {
        return new self( (int) ($hub * 100) ); // Ensure integer casting
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function toHub(): string
    {
        return number_format($this->amount / 100, 2, '.', '');
    }
}
