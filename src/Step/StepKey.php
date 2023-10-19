<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

use InvalidArgumentException;
use Stringable;

use function preg_match;

final class StepKey implements Stringable
{
    private const STEP_KEY_PATTERN = '/[A-Za-z-_]/';

    public function __construct(public readonly string $value)
    {
        if (!preg_match(self::STEP_KEY_PATTERN, $this->value)) {
            throw new InvalidArgumentException('The step key must have only "A-z", "-" and "_".');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
