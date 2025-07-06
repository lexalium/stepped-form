<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step;

use Lexal\SteppedForm\Step\StepInterface;

class SimpleStep implements StepInterface
{
    public function __construct(private readonly ?object $handleReturn = null)
    {
    }

    public function handle(object $entity, mixed $data): object
    {
        return $this->handleReturn ?: $entity;
    }
}
