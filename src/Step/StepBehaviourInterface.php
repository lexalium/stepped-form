<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

interface StepBehaviourInterface
{
    /**
     * Returns true when submitted data affects form steps, else - false.
     * If true - forget from the storage all data saved after current step.
     */
    public function forgetDataAfterCurrent(mixed $entity): bool;
}
