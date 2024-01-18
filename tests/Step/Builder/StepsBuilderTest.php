<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step\Builder;

use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Form\DataControlInterface;
use Lexal\SteppedForm\Form\StepControlInterface;
use Lexal\SteppedForm\Step\Builder\StepsBuilder;
use Lexal\SteppedForm\Step\Builder\StepsBuilderInterface;
use Lexal\SteppedForm\Step\LazyStep;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Tests\Step\RenderStep;
use Lexal\SteppedForm\Tests\Step\SimpleStep;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class StepsBuilderTest extends TestCase
{
    private StepControlInterface&Stub $stepControl;
    private DataControlInterface&Stub $dataControl;
    private StepsBuilderInterface $builder;

    protected function setUp(): void
    {
        $this->stepControl = $this->createStub(StepControlInterface::class);
        $this->dataControl = $this->createStub(DataControlInterface::class);

        $this->builder = new StepsBuilder($this->stepControl, $this->dataControl);
    }

    public function testAdd(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $expected = new Steps([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key2', new RenderStep()),
        ]);

        self::assertEquals($expected, $this->builder->get());
    }

    /**
     * @throws StepNotFoundException
     */
    public function testAddAfter(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new SimpleStep());
        $this->builder->add('key3', new RenderStep());

        $this->builder->addAfter('key2', 'key4', new RenderStep());

        $expected = new Steps([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key2', new SimpleStep()),
            $this->createStep('key4', new RenderStep()),
            $this->createStep('key3', new RenderStep()),
        ]);

        self::assertEquals($expected, $this->builder->get());
    }

    /**
     * @throws StepNotFoundException
     */
    public function testAddAfterNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException(new StepKey('key')));

        $this->builder->addAfter('key', 'key4', new RenderStep());
    }

    /**
     * @throws StepNotFoundException
     */
    public function testAddBefore(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());
        $this->builder->add('key3', new RenderStep());

        $this->builder->addBefore('key2', 'key4', new RenderStep());

        $expected = new Steps([
            $this->createStep('key', new SimpleStep()),
            $this->createStep('key4', new RenderStep()),
            $this->createStep('key2', new RenderStep()),
            $this->createStep('key3', new RenderStep()),
        ]);

        self::assertEquals($expected, $this->builder->get());
    }

    /**
     * @throws StepNotFoundException
     */
    public function testAddBeforeNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException(new StepKey('key')));

        $this->builder->addBefore('key', 'key4', new RenderStep());
    }

    public function testMerge(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $this->builder->merge(
            new Steps([
                new Step(new StepKey('key'), new SimpleStep()),
                new Step(new StepKey('key4'), new RenderStep()),
            ]),
        );

        $expected = new Steps([
            new Step(new StepKey('key'), new SimpleStep()),
            $this->createStep('key2', new RenderStep()),
            new Step(new StepKey('key4'), new RenderStep()),
        ]);

        self::assertEquals($expected, $this->builder->get());
    }

    public function testRemove(): void
    {
        $this->builder->add('key', new SimpleStep());
        $this->builder->add('key2', new RenderStep());

        $this->builder->remove('key2');

        $expected = new Steps([
            $this->createStep('key', new SimpleStep()),
        ]);

        self::assertEquals($expected, $this->builder->get());
    }

    /**
     * @throws NoStepsAddedException
     */
    public function testLazyCallbacksPositive(): void
    {
        $this->builder->add('key', new SimpleStep());

        $step = $this->builder->get()->first();

        $this->stepControl->method('getCurrent')
            ->willReturn('key');

        $this->dataControl->method('hasStepEntity')
            ->willReturn(true);

        self::assertTrue($step->isSubmitted());
        self::assertTrue($step->isCurrent());
    }

    /**
     * @throws NoStepsAddedException
     */
    public function testLazyCallbacksNegative(): void
    {
        $this->builder->add('key', new SimpleStep());

        $step = $this->builder->get()->first();

        $this->stepControl->method('getCurrent')
            ->willReturn('key2');

        $this->dataControl->method('hasStepEntity')
            ->willReturn(false);

        self::assertFalse($step->isSubmitted());
        self::assertFalse($step->isCurrent());
    }

    private function createStep(string $key, StepInterface $step): Step
    {
        return new LazyStep(
            new StepKey($key),
            $step,
            fn (): bool => false,
            fn (): bool => false,
        );
    }
}
