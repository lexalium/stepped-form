<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

use Lexal\SteppedForm\Exception\StepHandleException;

interface StepInterface
{
    /**
     * Handling step submit request.
     * Returns an updated entity that will be saved in the storage.
     *
     * $data will have null value when the step is not renderable.
     *
     * @throws StepHandleException
     */
    public function handle(mixed $entity, mixed $data): mixed;
}
