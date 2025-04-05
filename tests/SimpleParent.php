<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

class SimpleParent
{
    public static string $text = '';

    public function __construct(private int $price = 0)
    {
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}
