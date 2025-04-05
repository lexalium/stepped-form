<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use Lexal\SteppedForm\Step\StepKey;

use function sprintf;

final class StepNotSubmittedException extends SteppedFormException
{
    private function __construct(string $message, public readonly StepKey $key, public readonly ?StepKey $renderable)
    {
        parent::__construct($message);
    }

    public static function finish(StepKey $key, ?StepKey $renderable): self
    {
        return new self(sprintf('The step [%s] has not been submitted yet.', $key), $key, $renderable);
    }

    public static function previous(StepKey $key, ?StepKey $renderable): self
    {
        return new self(sprintf('Previous step [%s] has not been submitted.', $key), $key, $renderable);
    }
}
