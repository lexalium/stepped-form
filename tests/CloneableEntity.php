<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

final class CloneableEntity
{
    public function __construct(public readonly string $text, public string $name = 'name')
    {
    }

    public function __clone(): void
    {
        $this->name = 'name2';
    }
}
