<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\Builder;

use Lexal\SteppedForm\Form\Builder\StaticStepsFormBuilder;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Tests\Step\RenderStep;
use PHPUnit\Framework\TestCase;

final class StaticStepsFormBuilderTest extends TestCase
{
    public function testIsDynamic(): void
    {
        $builder = new StaticStepsFormBuilder(new Steps());

        self::assertFalse($builder->isDynamic());
    }

    public function testBuild(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key1'), new RenderStep()),
            new Step(new StepKey('key2'), new RenderStep()),
        ]);

        $builder = new StaticStepsFormBuilder($steps);

        self::assertEquals($steps, $builder->build(['id' => 5]));
    }
}
