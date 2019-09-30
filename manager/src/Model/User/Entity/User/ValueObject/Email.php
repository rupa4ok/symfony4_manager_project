<?php

declare(strict_types=1);

namespace App\Model\User\Entity\User\ValueObject;

use Webmozart\Assert\Assert;

class Email
{
    private $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Неккоректный email');
        }
        $this->value = mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
    
    public function isEqual(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }
}