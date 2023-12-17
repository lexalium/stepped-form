<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\StepKey;

class SteppedFormErrorsException extends SteppedFormException
{
    public ?StepKey $renderable = null;

    /**
     * @param string[] $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct();
    }
}
