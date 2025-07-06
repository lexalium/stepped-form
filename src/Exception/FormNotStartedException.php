<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class FormNotStartedException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('The form has not been started yet.');
    }
}
