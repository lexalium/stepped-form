<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\State;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

interface FormStateInterface
{
    /**
     * Initializes a new form state. Saves entity to the storage and a first step key
     *
     * @throws AlreadyStartedException
     * @throws StepNotFoundException
     */
    public function initialize(mixed $entity, StepsCollection $steps): void;

    /**
     * Sets a step data and updates current step value
     */
    public function handle(string $key, mixed $entity, ?Step $next = null): void;

    /**
     * Returns a form data or data related to the given step
     */
    public function getEntity(?string $key = null): mixed;

    /**
     * Clear all form data
     */
    public function finish(): void;
}
