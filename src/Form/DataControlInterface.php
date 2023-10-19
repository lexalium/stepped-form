<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;

interface DataControlInterface
{
    /**
     * Returns an entity passed at the form start.
     */
    public function getInitializeEntity(): mixed;

    /**
     * Returns a stepped form data of the last submitted step.
     */
    public function getEntity(): mixed;

    /**
     * Checks if a given step contains data.
     */
    public function hasStepEntity(StepKey $key): bool;

    /**
     * Returns a data related to the given step.
     *
     * @throws EntityNotFoundException
     */
    public function getStepEntity(StepKey $key): mixed;

    /**
     * Initializes a new form state. Saves entity to the storage.
     */
    public function start(mixed $entity): void;

    /**
     * Sets a step data and updates current step value.
     */
    public function handle(Step $step, mixed $entity): void;
}
