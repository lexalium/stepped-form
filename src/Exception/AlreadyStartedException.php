<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Steps\Collection\Step;

class AlreadyStartedException extends SteppedFormException
{
    public function __construct(private string $currentKey, private ?Step $currentStep)
    {
        parent::__construct('The form has already started.');
    }

    public function getCurrentStep(): ?Step
    {
        return $this->currentStep;
    }

    public function getCurrentKey(): string
    {
        return $this->currentKey;
    }
}
