<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\Step;

class SteppedFormErrorsException extends SteppedFormException
{
    private ?Step $previous = null; // TODO: remove step access and set just step key

    /**
     * @param string[] $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct();
    }

    public function getPreviousStep(): ?Step
    {
        return $this->previous;
    }

    public function setPreviousStep(?Step $previous): void
    {
        $this->previous = $previous;
    }
}
