<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

class Step
{
    public function __construct(
        public readonly StepKey $key,
        public readonly StepInterface $step,
        private readonly bool $isCurrent = false,
        private readonly bool $isSubmitted = false,
    ) {
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
