<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

/**
 * @template TEntity of object
 */
interface StepBehaviourInterface
{
    /**
     * Returns true when submitted data affects form steps, else - false.
     * If true - data after current step will be removed from the storage.
     *
     * Useful for dynamic forms.
     *
     * @param TEntity&object $entity
     */
    public function forgetDataAfterCurrent(object $entity): bool;
}
