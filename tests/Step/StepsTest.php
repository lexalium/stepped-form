<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step;

use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StepsTest extends TestCase
{
    public function testCreateNoErrorsWithDifferentTypes(): void
    {
        /** @phpstan-ignore-next-line */
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new stdClass(),
            5,
            'string',
            ['key' => 'key'],
        ]);

        self::assertEquals(1, $steps->count());
    }

    public function testCreateWithDuplicateKeys(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new Step(new StepKey('key'), new SimpleStep()),
        ]);

        self::assertEquals(1, $steps->count());
        self::assertNull($steps->next(new StepKey('key')));
    }

    public function testFirst(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new Step(new StepKey('key2'), new SimpleStep()),
        ]);

        $expected = new Step(new StepKey('key'), new SimpleStep());

        self::assertEquals($expected, $steps->first());
    }

    public function testFirstNoStepsAddedException(): void
    {
        $this->expectExceptionObject(new NoStepsAddedException());
        $this->expectExceptionMessage('No steps have been added to the form.');

        $steps = new Steps([]);

        $steps->first();
    }

    public function testNext(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new Step(new StepKey('key2'), new SimpleStep()),
        ]);

        $expected = new Step(new StepKey('key2'), new SimpleStep());

        self::assertEquals($expected, $steps->next(new StepKey('key')));
    }

    public function testNextIsNotExists(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
        ]);

        $this->assertNull($steps->next(new StepKey('key')));
    }

    public function testNextStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException(new StepKey('key')));

        $steps = new Steps([]);

        $steps->next(new StepKey('key'));
    }

    public function testPrevious(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new Step(new StepKey('key2'), new SimpleStep()),
        ]);

        $expected = new Step(new StepKey('key'), new SimpleStep());

        self::assertEquals($expected, $steps->previous(new StepKey('key2')));
    }

    public function testPreviousIsNotExists(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
        ]);

        $this->assertNull($steps->previous(new StepKey('key')));
    }

    public function testPreviousStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException(new StepKey('key')));

        $steps = new Steps([]);

        $steps->previous(new StepKey('key'));
    }

    public function testCurrentOrPreviousRenderable(): void
    {
        $step1 = new Step(new StepKey('key'), new RenderStep());
        $step2 = new Step(new StepKey('key2'), new RenderStep());
        $step3 = new Step(new StepKey('key3'), new SimpleStep());
        $step4 = new Step(new StepKey('key4'), new SimpleStep());

        $steps = new Steps([$step1, $step2, $step3, $step4]);

        self::assertEquals($step1, $steps->currentOrPreviousRenderable($step1));
        self::assertEquals($step2, $steps->currentOrPreviousRenderable($step2));
        self::assertEquals($step2, $steps->currentOrPreviousRenderable($step3));
        self::assertEquals($step2, $steps->currentOrPreviousRenderable($step4));
    }

    public function testHas(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
        ]);

        $this->assertTrue($steps->has(new StepKey('key')));
        $this->assertFalse($steps->has(new StepKey('key2')));
    }

    public function testGet(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
        ]);

        $expected = new Step(new StepKey('key'), new SimpleStep());

        self::assertEquals($expected, $steps->get(new StepKey('key')));
    }

    public function testGetStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException(new StepKey('key')));
        $this->expectExceptionMessage('The step [key] is not found');

        $steps = new Steps([]);

        $steps->get(new StepKey('key'));
    }

    public function testCount(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            new Step(new StepKey('key3'), new SimpleStep()),
            new Step(new StepKey('key'), new RenderStep()),
        ]);

        $this->assertCount(2, $steps);
    }
}
