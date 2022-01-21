<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Collection;

use Closure;
use Lexal\SteppedForm\Steps\StepInterface;

class LazyStep extends Step
{
    public function __construct(
        string $key,
        StepInterface $step,
        private Closure $isCurrentCallback,
        private Closure $isSubmittedCallback,
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
