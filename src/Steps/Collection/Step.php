<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Collection;

use Lexal\SteppedForm\Steps\StepInterface;

class Step
{
    public function __construct(
        private string $key,
        private StepInterface $step,
        private bool $isCurrent = false,
        private bool $isSubmitted = false,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function isSubmitted(): bool
    {
        return $this->isSubmitted;
    }
}
