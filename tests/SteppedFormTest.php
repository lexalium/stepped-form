<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\State\FormStateInterface;
use Lexal\SteppedForm\SteppedForm;
use Lexal\SteppedForm\SteppedFormInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\RenderStepInterface;
use Lexal\SteppedForm\Steps\StepInterface;
use Lexal\SteppedForm\Tests\Steps\RenderStep;
use Lexal\SteppedForm\Tests\Steps\SimpleStep;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SteppedFormTest extends TestCase
{
    private const SIMPLE_ENTITY = [
        'key' => 'value',
    ];

    private SteppedFormInterface $form;
    private FormStateInterface|MockObject $formState;
    private FormBuilderInterface|MockObject $builder;
    private EventDispatcherInterface|MockObject $dispatcher;

    public function testStart(): void
    {
        $expected = new Step('key', new RenderStep());
        $collection = new StepsCollection([
            new Step('key', new RenderStep()),
            new Step('key2', new RenderStep()),
        ]);

        $this->builder->expects($this->once())
            ->method('build')
            ->with(self::SIMPLE_ENTITY)
            ->willReturn($collection);

        $this->formState->expects($this->once())
            ->method('initialize')
            ->with(self::SIMPLE_ENTITY, $collection);

        $step = $this->form->start(self::SIMPLE_ENTITY);

        $this->assertEquals($expected, $step);
    }

    public function testStartFirstStepIsNotRenderable(): void
    {
        $expected = new Step('key2', new RenderStep());
        $collection = new StepsCollection([
            new Step('key', $this->createHandleStep(StepInterface::class, self::SIMPLE_ENTITY)),
            new Step('key3', $this->createHandleStep(StepInterface::class, self::SIMPLE_ENTITY)),
            new Step('key2', new RenderStep()),
        ]);

        $this->builder->expects($this->exactly(3))
            ->method('build')
            ->withConsecutive([self::SIMPLE_ENTITY], [self::SIMPLE_ENTITY], [self::SIMPLE_ENTITY])
            ->willReturnOnConsecutiveCalls($collection, $collection, $collection);

        $this->testHandleWithCount(
            2,
            'key',
            self::SIMPLE_ENTITY,
            null,
            [
                new Step('key', $this->createHandleStep(StepInterface::class, self::SIMPLE_ENTITY)),
                new Step('key3', $this->createHandleStep(StepInterface::class, self::SIMPLE_ENTITY)),
            ],
            [
                new Step('key3', $this->createHandleStep(StepInterface::class, self::SIMPLE_ENTITY)),
                $expected,
            ],
            ['key', 'key'],
        );

        $this->assertEquals($expected, $this->form->start(self::SIMPLE_ENTITY));
    }

    public function testRender(): void
    {
        $collection = new StepsCollection([
            new Step('key', new RenderStep()),
            new Step('key2', new RenderStep()),
        ]);

        $this->formState->expects($this->once())
            ->method('getEntity')
            ->willReturn(self::SIMPLE_ENTITY);

        $this->builder->expects($this->once())
            ->method('build')
            ->with(self::SIMPLE_ENTITY)
            ->willReturn($collection);

        $this->formState->expects($this->once())
            ->method('getStepEntity')
            ->with('key')
            ->willReturn(self::SIMPLE_ENTITY);

        $expected = new TemplateDefinition('test', [self::SIMPLE_ENTITY]);
        $definition = $this->form->render('key');

        $this->assertEquals($expected, $definition);
    }

    public function testRenderEntityFromPreviousStep(): void
    {
        $collection = new StepsCollection([
            new Step('key', new RenderStep()),
            new Step('key2', new RenderStep()),
        ]);

        $this->formState->expects($this->once())
            ->method('getEntity')
            ->willReturn(self::SIMPLE_ENTITY);

        $this->builder->expects($this->once())
            ->method('build')
            ->with(self::SIMPLE_ENTITY)
            ->willReturn($collection);

        $this->formState->expects($this->exactly(2))
            ->method('getStepEntity')
            ->withConsecutive(['key2'], ['key'])
            ->willReturnOnConsecutiveCalls(null, self::SIMPLE_ENTITY);

        $expected = new TemplateDefinition('test', [self::SIMPLE_ENTITY]);
        $definition = $this->form->render('key2');

        $this->assertEquals($expected, $definition);
    }

    public function testRenderStepIsNotRenderableException(): void
    {
        $this->expectExceptionObject(new StepNotRenderableException('key'));

        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $this->formState->expects($this->once())
            ->method('getEntity')
            ->willReturn(self::SIMPLE_ENTITY);

        $this->builder->expects($this->once())
            ->method('build')
            ->with(self::SIMPLE_ENTITY)
            ->willReturn($collection);

        $this->form->render('key');
    }

    public function testHandle(): void
    {
        $data = ['step' => 'test'];
        $entity = self::SIMPLE_ENTITY + $data;

        $expected = new Step('key3', new RenderStep());
        $collection = new StepsCollection([
            new Step('key', $this->createHandleStep(RenderStepInterface::class, $entity)),
            new Step('key3', new RenderStep()),
            new Step('key2', new RenderStep()),
        ]);

        $this->builder->expects($this->exactly(2))
            ->method('build')
            ->withConsecutive([self::SIMPLE_ENTITY], [$entity])
            ->willReturnOnConsecutiveCalls($collection, $collection);

        $this->testHandleWithCount(
            1,
            'key',
            $entity,
            $data,
            [new Step('key', $this->createHandleStep(RenderStepInterface::class, $entity))],
            [$expected],
            ['key'],
        );

        $this->assertEquals($expected, $this->form->handle('key', $data));
    }

    protected function setUp(): void
    {
        $this->formState = $this->createMock(FormStateInterface::class);
        $this->builder = $this->createMock(FormBuilderInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->form = new SteppedForm(
            $this->formState,
            $this->builder,
            $this->dispatcher,
        );

        parent::setUp();
    }

    private function testHandleWithCount(
        int $count,
        string $key,
        mixed $entityAfterHandle,
        mixed $data,
        array $stepsForHandle,
        array $nextSteps,
        array $keysForGetStepEntity,
    ): void {
        $this->formState->expects($this->exactly($count))
            ->method('getEntity')
            ->willReturn(self::SIMPLE_ENTITY);

        $this->formState->expects($this->exactly($count))
            ->method('getStepEntity')
            ->withConsecutive(...array_map(
                static fn (string $key) => [$key],
                $keysForGetStepEntity,
            ))
            ->willReturnOnConsecutiveCalls(...array_fill(0, $count, self::SIMPLE_ENTITY));

        $events = array_map(
            static fn (Step $step) => new BeforeHandleStep($data, self::SIMPLE_ENTITY, $step),
            $stepsForHandle,
        );

        $this->dispatcher->expects($this->exactly($count))
            ->method('dispatch')
            ->withConsecutive(...array_map(
                static fn (BeforeHandleStep $event) => [$event],
                $events,
            ))
            ->willReturnOnConsecutiveCalls(...$events);

        $keys = [$key] + array_map(static fn (Step $step) => $step->getKey(), array_filter($stepsForHandle));

        $this->formState->expects($this->exactly($count))
            ->method('handle')
            ->withConsecutive(...array_map(
                static fn (string $key, ?Step $nextStep) => [$key, $entityAfterHandle, $nextStep],
                $keys,
                $nextSteps,
            ));
    }

    private function createHandleStep(string $className, mixed $return): StepInterface|MockObject
    {
        $step = $this->createMock($className);

        $step->method('handle')
            ->willReturn($return);

        return $step;
    }
}