<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;

/**
 * @template TEntity of object
 */
interface DataControlInterface
{
    /**
     * Returns an entity passed at the form start.
     *
     * @return TEntity&object
     */
    public function getInitializeEntity(): object;

    /**
     * Returns a stepped form data of the last submitted step.
     *
     * @return TEntity&object
     */
    public function getEntity(): object;

    /**
     * Checks if a given step contains data.
     */
    public function hasStepEntity(StepKey $key): bool;

    /**
     * Returns a data related to the given step.
     *
     * @return TEntity&object
     *
     * @throws EntityNotFoundException
     */
    public function getStepEntity(StepKey $key): object;

    /**
     * Initializes a new form state. Saves entity and session key to the storage.
     *
     * @param TEntity&object $entity
     */
    public function initialize(object $entity, string $session): void;

    /**
     * Sets a step data and updates current step value.
     *
     * @param TEntity&object $entity
     */
    public function handle(Step $step, object $entity, bool $isDynamicForm): void;

    /**
     * Cancels current form and clears active form state.
     */
    public function cancel(): void;
}
