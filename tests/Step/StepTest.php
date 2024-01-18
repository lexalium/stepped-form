<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use PHPUnit\Framework\TestCase;

final class StepTest extends TestCase
{
    public function testDefaultState(): void
    {
        $step = new Step(new StepKey('key'), $this->createStub(StepInterface::class));

        self::assertFalse($step->isCurrent());
        self::assertFalse($step->isSubmitted());
    }
}
