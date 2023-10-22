<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step;

use InvalidArgumentException;
use Lexal\SteppedForm\Step\StepKey;
use PHPUnit\Framework\TestCase;

final class StepKeyTest extends TestCase
{
    public function testCreateWithInvalidKeyValue(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('The step key must have only "A-z", "0-9", "-" and "_".'),
        );

        new StepKey('key+ hello');
    }
}
