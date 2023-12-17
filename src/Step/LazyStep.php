<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

use Closure;

final class LazyStep extends Step
{
    public function __construct(
        StepKey $key,
        StepInterface $step,
        private readonly Closure $isCurrentCallback,
        private readonly Closure $isSubmittedCallback,
    ) {
        parent::__construct($key, $step);
    }

    public function isCurrent(): bool
    {
        return ($this->isCurrentCallback)();
    }

    public function isSubmitted(): bool
    {
        return ($this->isSubmittedCallback)();
    }
}
