<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\StepKey;

use function sprintf;

final class EntityNotFoundException extends SteppedFormException
{
    public ?StepKey $renderable = null;

    public function __construct(public readonly StepKey $key)
    {
        parent::__construct(sprintf('There is no data for the given [%s] step.', $this->key));
    }
}
