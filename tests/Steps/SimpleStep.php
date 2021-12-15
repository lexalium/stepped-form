<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Steps\StepInterface;

class SimpleStep implements StepInterface
{
    public function handle(mixed $entity, mixed $data): mixed
    {
        return $entity;
    }
}
