<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Steps\Collection\Step;

use function sprintf;

class StepIsNotSubmittedException extends SteppedFormException
{
    public function __construct(private Step $step)
    {
        parent::__construct(sprintf('The Step [%s] is not submitted yet.', $this->step->getKey()));
    }

    public function getStep(): Step
    {
        return $this->step;
    }
}
