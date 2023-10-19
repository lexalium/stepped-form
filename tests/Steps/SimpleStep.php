<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Step\StepInterface;

class SimpleStep implements StepInterface
{
    public function __construct(private mixed $handleReturn = null)
    {
    }

    public function handle(mixed $entity, mixed $data): mixed
    {
        return $this->handleReturn ?: $entity;
    }
}
