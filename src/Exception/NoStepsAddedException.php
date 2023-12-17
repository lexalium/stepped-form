<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class NoStepsAddedException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('No steps have been added to the form.');
    }
}
