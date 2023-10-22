<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\StepKey;

use function sprintf;

final class StepIsNotSubmittedException extends SteppedFormException
{
    private function __construct(string $message, public readonly StepKey $key, public readonly ?StepKey $renderable)
    {
        parent::__construct($message);
    }

    public static function finish(StepKey $key, ?StepKey $renderable): self
    {
        return new self(sprintf('The Step [%s] is not submitted yet.', $key), $key, $renderable);
    }

    public static function render(StepKey $key, ?StepKey $renderable): self
    {
        return new self(sprintf('Cannot render step if previous step [%s] is not submitted.', $key), $key, $renderable);
    }
}
