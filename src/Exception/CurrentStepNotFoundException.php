<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class CurrentStepNotFoundException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('The current step key is not found in the storage.');
    }
}
