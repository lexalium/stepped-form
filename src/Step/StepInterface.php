<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

use Lexal\SteppedForm\Exception\StepHandleException;

/**
 * @template TEntity of object
 */
interface StepInterface
{
    /**
     * Handling step submit request.
     * Returns an updated entity that will be saved in the storage.
     *
     * $data will have null value when the step is not renderable.
     *
     * @param TEntity&object $entity
     *
     * @return TEntity&object
     *
     * @throws StepHandleException
     */
    public function handle(object $entity, mixed $data): object;
}
