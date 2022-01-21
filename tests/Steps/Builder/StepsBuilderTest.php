<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\State\FormStateInterface;
use Lexal\SteppedForm\Steps\Builder\StepsBuilder;
use Lexal\SteppedForm\Steps\Builder\StepsBuilderInterface;
use Lexal\SteppedForm\Steps\Collection\LazyStep;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;
use Lexal\SteppedForm\Tests\Steps\RenderStep;
use Lexal\SteppedForm\Tests\Steps\SimpleStep;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StepsBuilderTest extends TestCase
{
    private MockObject $formState;
    private StepsBuilderInterface $builder;

    public function testAdd(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $expected = new StepsCollection([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key2', new RenderStep()),
        ]);

        $this->assertEquals($expected, $this->builder->get());
    }

    public function testAddAfter(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $this->builder->addAfter('key', 'key4', new RenderStep());

        $expected = new StepsCollection([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key4', new RenderStep()),
            $this->createStep('key2', new RenderStep()),
        ]);

        $this->assertEquals($expected, $this->builder->get());
    }

    public function testAddAfterNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $this->builder->addAfter('key', 'key4', new RenderStep());
    }

    public function testAddBefore(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());
        $this->builder->add('key3', new RenderStep());

        $this->builder->addBefore('key2', 'key4', new RenderStep());

        $expected = new StepsCollection([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key4', new RenderStep()),
            $this->createStep('key2', new RenderStep()),
            $this->createStep('key3', new RenderStep()),
        ]);

        $this->assertEquals($expected, $this->builder->get());
    }

    public function testAddBeforeNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $this->builder->addBefore('key', 'key4', new RenderStep());
    }

    public function testMerge(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $this->builder->merge(
            new StepsCollection([
                new Step('key', new SimpleStep()),
                new Step('key4', new RenderStep()),
            ]),
        );

        $expected = new StepsCollection([
            new Step('key', new SimpleStep()),
            $this->createStep('key2', new RenderStep()),
            new Step('key4', new RenderStep()),
        ]);

        $this->assertEquals($expected, $this->builder->get());
    }

    public function testRemove(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $this->builder->remove('key2');

        $expected = new StepsCollection([
            $this->createStep('key', new SimpleStep()),
        ]);

        $this->assertEquals($expected, $this->builder->get());
    }

    public function testLazyCallbacksPositive(): void
    {
        $this->builder->add('key', new SimpleStep());

        $step = $this->builder->get()->first();

        $this->formState->expects($this->once())
            ->method('getCurrentStep')
            ->willReturn('key');

        $this->formState->expects($this->once())
            ->method('hasStepEntity')
            ->willReturn(true);

        $this->assertTrue($step->isSubmitted());
        $this->assertTrue($step->isCurrent());
    }

    public function testLazyCallbacksNegative(): void
    {
        $this->builder->add('key', new SimpleStep());

        $step = $this->builder->get()->first();

        $this->formState->expects($this->once())
            ->method('getCurrentStep')
            ->willReturn('key2');

        $this->formState->expects($this->once())
            ->method('hasStepEntity')
            ->willReturn(false);

        $this->assertFalse($step->isSubmitted());
        $this->assertFalse($step->isCurrent());
    }

    protected function setUp(): void
    {
        $this->formState = $this->createMock(FormStateInterface::class);

        $this->builder = new StepsBuilder($this->formState);

        parent::setUp();
    }

    private function createStep(string $key, StepInterface $step): Step
    {
        return new LazyStep(
            $key,
            $step,
            fn (): bool => false,
            fn (): bool => false,
        );
    }
}
