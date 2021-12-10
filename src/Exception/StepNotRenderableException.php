<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class StepNotRenderableException extends SteppedFormException
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('The step [%s] is not renderable.', $key));
    }
}
