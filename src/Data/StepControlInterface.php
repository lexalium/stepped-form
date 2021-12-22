<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;

interface StepControlInterface
{
    /**
     * Save current step name to the storage.
     */
    public function setCurrent(string $key): self;

    /**
     * Get current step name from the storage.
     *
     * @throws CurrentStepNotFoundException
     */
    public function getCurrent(): string;

    /**
     * Checks if the storage contains current step.
     */
    public function hasCurrent(): bool;

    /**
     * Removes current step name from the storage.
     */
    public function reset(): self;
}
