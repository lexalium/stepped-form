<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Step\StepKey;

interface DataStorageInterface
{
    /**
     * Checks if the form contains entity for the given step key.
     */
    public function has(StepKey $key): bool;

    /**
     * Returns entity for the given step. Return null when no entity found.
     */
    public function get(StepKey $key): mixed;

    /**
     * Returns a last saved data.
     *
     * @throws KeysNotFoundInStorageException
     */
    public function getLast(): mixed;

    /**
     * Saves submitted step entity.
     */
    public function put(StepKey $key, mixed $entity): void;

    /**
     * Removes all steps data after given.
     */
    public function forgetAfter(StepKey $key): void;
}
