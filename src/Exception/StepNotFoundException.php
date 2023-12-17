<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\StepKey;

use function sprintf;

final class StepNotFoundException extends SteppedFormException
{
    public function __construct(public readonly StepKey $key)
    {
        parent::__construct(sprintf('The step [%s] is not found', $key));
    }
}
