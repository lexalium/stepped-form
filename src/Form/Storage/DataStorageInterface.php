<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Step\StepKey;

/**
 * @template TEntity of object
 */
interface DataStorageInterface
{
    /**
     * Checks if the form contains entity for the given step key.
     */
    public function has(StepKey $key): bool;

    /**
     * Returns entity passed to initialize method.
     */
    public function getInitializeEntity(): object;

    /**
     * Returns entity for the given step. Returns null when no entity found.
     *
     * @return (TEntity&object)|null
     */
    public function get(StepKey $key): object|null;

    /**
     * Returns a last saved data.
     *
     * @return TEntity&object
     *
     * @throws KeysNotFoundInStorageException
     */
    public function getLast(): object;

    /**
     * Initializes storage with entity as session key.
     */
    public function initialize(object $entity, string $session): void;

    /**
     * Saves submitted step entity.
     *
     * @param TEntity&object $entity
     */
    public function put(StepKey $key, object $entity): void;

    /**
     * Removes all steps data after given.
     */
    public function forgetAfter(StepKey $key): void;

    /**
     * Clears a form storage.
     */
    public function clear(): void;
}
