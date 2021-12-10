<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Collection;

use Lexal\SteppedForm\Steps\StepInterface;

class Step
{
    public function __construct(private string $key, private StepInterface $step)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }
}
