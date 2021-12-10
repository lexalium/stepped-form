<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class EntityNotFoundException extends SteppedFormException
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('There is no data for the given [%s] step.', $key));
    }
}
