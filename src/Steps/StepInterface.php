<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps;

use Lexal\SteppedForm\Exception\StepHandleException;

interface StepInterface
{
    /**
     * Handling step submit request
     * Returns a data that will be passed to the storage
	 *
	 * $data will have null value when the step is not renderable
     *
     * @throws StepHandleException
     */
    public function handle(mixed $entity, mixed $data): mixed;
}
