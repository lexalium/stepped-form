<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;

use function sprintf;

final class StepIsNotSubmittedException extends SteppedFormException
{
    private function __construct(string $message, public readonly StepKey $key, public readonly ?Step $previous)
    {
        parent::__construct($message);
    }

    public static function finish(StepKey $key, ?Step $previous): self
    {
        return new self(sprintf('The Step [%s] is not submitted yet.', $key), $key, $previous);
    }

    public static function render(StepKey $key, ?Step $previous): self
    {
        return new self(sprintf('Cannot render step if previous step [%s] is not submitted.', $key), $key, $previous);
    }
}
