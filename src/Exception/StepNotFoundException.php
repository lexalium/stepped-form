<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class StepNotFoundException extends SteppedFormException
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('Step %s not found', $key));
    }
}
