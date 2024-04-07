<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

class SimpleParent
{
    public static string $text = '';
    private int $price = 0; // @phpstan-ignore-line
}
