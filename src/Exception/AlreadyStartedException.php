<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Steps\Collection\Step;

class AlreadyStartedException extends SteppedFormException
{
    public function __construct(private Step $currentStep)
    {
        parent::__construct('The form has already started.');
    }

    public function getCurrentStep(): Step
    {
        return $this->currentStep;
    }
}
