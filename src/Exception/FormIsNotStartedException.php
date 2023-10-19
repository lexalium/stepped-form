<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class FormIsNotStartedException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('The stepped form is not started yet.');
    }
}
