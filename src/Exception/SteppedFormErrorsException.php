<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class SteppedFormErrorsException extends SteppedFormException
{
    /**
     * @param string[] $errors
     */
    public function __construct(private array $errors)
    {
        parent::__construct();
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
