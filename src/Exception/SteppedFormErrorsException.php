<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class SteppedFormErrorsException extends SteppedFormException
{
    public function __construct(private array $errors)
    {
        parent::__construct();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
