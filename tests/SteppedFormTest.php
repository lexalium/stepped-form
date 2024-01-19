<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\EntityCopy\SimpleEntityCopy;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepHandleException;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Form\DataControl;
use Lexal\SteppedForm\Form\StepControl;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Form\Storage\FormStorage;
use Lexal\SteppedForm\Form\Storage\FormStorageInterface;
use Lexal\SteppedForm\Form\Storage\SessionStorageInterface;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Step\TemplateDefinition;
use Lexal\SteppedForm\SteppedForm;
use Lexal\SteppedForm\SteppedFormInterface;
use Lexal\SteppedForm\Tests\Step\RenderStep;
use Lexal\SteppedForm\Tests\Step\SimpleStep;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class SteppedFormTest extends TestCase
{
    private FormStorageInterface $storage;
    private SessionStorageInterface $sessionStorage;
    private DataStorage $dataStorage;
    private DataControl $dataControl;
    private StepControl $stepControl;
    private FormBuilderInterface&Stub $builder;
    private MockObject $dispatcher;
    private SteppedFormInterface $form;

    protected function setUp(): void
    {
        $this->sessionStorage = new InMemorySessionStorage();
        $this->storage = new FormStorage(new InMemoryStorage(), $this->sessionStorage);
        $this->dataStorage = new DataStorage($this->storage);
        $this->dataControl = new DataControl($this->dataStorage);
        $this->stepControl = new StepControl($this->storage);
        $this->builder = $this->createStub(FormBuilderInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->builder->method('isDynamic')
            ->willReturn(false);

        $this->form = new SteppedForm(
            $this->dataControl,
            $this->stepControl,
            $this->storage,
            $this->builder,
            $this->dispatcher,
            new SimpleEntityCopy(),
        );
    }

    /**
     * @throws FormIsNotStartedException
     */
    public function testGetEntityThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->form->getEntity();
    }

    #[DataProvider('startDataProvider')]
    public function testStart(Steps $steps, ?StepKey $expectedFirst, mixed $expectedEntity, ?string $expectedKey): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 6]);

        $this->builder->method('build')
            ->willReturn($steps);

        $this->dispatcher->method('dispatch')
            ->willReturn(new BeforeHandleStep(null, ['id' => 5], new Step(new StepKey('key'), new RenderStep())));

        $first = $this->form->start(['id' => 5]);

        self::assertEquals($expectedFirst, $first);
        self::assertEquals($expectedEntity, $this->dataControl->getInitializeEntity());
        self::assertEquals($expectedEntity, $this->dataControl->getEntity());
        self::assertEquals($expectedKey, $this->stepControl->getCurrent());
        self::assertEquals('__MAIN__', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));
        self::assertNull($this->dataStorage->get(new StepKey('key')));
    }

    /**
     * @return array<string, mixed>
     */
    public static function startDataProvider(): iterable
    {
        $step1 = new Step(new StepKey('key'), new RenderStep(), isSubmitted: true);
        $step2 = new Step(new StepKey('key2'), new RenderStep(), isSubmitted: true);
        $step3 = new Step(new StepKey('key3'), new SimpleStep(), isSubmitted: true);
        $step4 = new Step(new StepKey('key4'), new SimpleStep(), isSubmitted: true);

        yield 'first step is renderable' => [new Steps([$step1, $step2]), $step1->key, ['id' => 5], 'key'];
        yield 'first step is not renderable' => [new Steps([$step3, $step2]), $step2->key, ['id' => 5], 'key2'];
        yield 'without renderable' => [new Steps([$step3, $step4]), null, null, null];
    }

    public function testCanAgainStartFormWhenFirstNotRenderableStepThrowsException(): void
    {
        $this->expectExceptionObject(new StepHandleException(['can not handle']));

        $throwableStep = new class () implements StepInterface {
            public function handle(mixed $entity, mixed $data): mixed
            {
                throw new StepHandleException(['can not handle']);
            }
        };

        $this->builder->method('build')
            ->willReturn(new Steps([new Step(new StepKey('key'), $throwableStep)]));

        $this->dispatcher->method('dispatch')
            ->willReturn(new BeforeHandleStep(null, ['id' => 5], new Step(new StepKey('key'), new RenderStep())));

        try {
            $this->form->start(['id' => 5]);
        } catch (SteppedFormErrorsException) {
            // skip first exception
        }

        $this->form->start(['id' => 5]);
    }

    public function testStartThrowIfAlreadyStarted(): void
    {
        $this->expectExceptionObject(new AlreadyStartedException('key'));

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));

        $this->form->start(['id' => 5], 'main');
    }

    public function testStartWithDifferentNamespaces(): void
    {
        $this->builder->method('build')
            ->willReturn(new Steps([new Step(new StepKey('key'), new RenderStep(), isSubmitted: true)]));

        $this->form->start(['id' => 5], 'first');

        self::assertEquals(['id' => 5], $this->dataControl->getInitializeEntity());
        self::assertEquals('first', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));

        $this->form->start(['id' => 8], 'second');

        self::assertEquals(['id' => 8], $this->dataControl->getInitializeEntity());
        self::assertEquals('second', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));

        $this->storage->setCurrentSession('first');

        self::assertEquals(['id' => 5], $this->dataControl->getInitializeEntity());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(Steps $steps, StepKey $key, mixed $entity): void
    {
        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataControl->start(['id' => 4]);
        $this->dataStorage->put(new StepKey('key'), ['id' => 6]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 7]);

        $this->builder->method('build')
            ->willReturn($steps);

        $templateDefinition = $this->form->render($key);

        self::assertEquals(new TemplateDefinition('template', [$entity]), $templateDefinition);
    }

    /**
     * @return array<string, mixed>
     */
    public static function renderDataProvider(): iterable
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key3'), new RenderStep('template')),
        ]);

        yield 'template with previous step entity' => [$steps, new StepKey('key3'), ['id' => 6]];

        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        yield 'template with current step entity' => [$steps, new StepKey('key2'), ['id' => 7]];

        $steps = new Steps([
            new Step(new StepKey('key4'), new RenderStep('template')),
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        yield 'template with initial step entity' => [$steps, new StepKey('key4'), ['id' => 4]];
    }

    #[DataProvider('renderPreviousStepEntityNotFoundDataProvider')]
    public function testRenderPreviousStepEntityNotFound(StepInterface $step, ?StepKey $renderable): void
    {
        $exception = new EntityNotFoundException(new StepKey('key'));
        $exception->renderable = $renderable;

        $this->expectExceptionObject($exception);

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataControl->start(['id' => 4]);

        $steps = new Steps([
            new Step(new StepKey('key'), $step, isSubmitted: true),
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        try {
            $this->form->render(new StepKey('key2'));
        } catch (EntityNotFoundException $exception) {
            self::assertEquals($renderable, $exception->renderable);

            throw $exception;
        }
    }

    /**
     * @return iterable<string, array{0: StepInterface, 1: StepKey|null}>
     */
    public static function renderPreviousStepEntityNotFoundDataProvider(): iterable
    {
        yield 'previous step is renderable' => [new RenderStep(), new StepKey('key')];
        yield 'previous step is not renderable' => [new SimpleStep(), null];
    }

    public function testRenderThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->form->render(new StepKey('key'));
    }

    #[DataProvider('renderThrowIfPreviousNotSubmittedDataProvider')]
    public function testRenderThrowIfPreviousNotSubmitted(StepInterface $step, ?StepKey $renderable): void
    {
        $this->expectExceptionObject(StepIsNotSubmittedException::previous(new StepKey('key2'), $renderable));
        $this->expectExceptionMessage('Previous step [key2] is not submitted.');

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));

        $this->builder->method('build')
            ->willReturn(
                new Steps([
                    new Step(new StepKey('key'), $step, isSubmitted: true),
                    new Step(new StepKey('key2'), new SimpleStep(), isSubmitted: false),
                    new Step(new StepKey('key3'), new RenderStep()),
                ]),
            );

        try {
            $this->form->render(new StepKey('key3'));
        } catch (StepIsNotSubmittedException $exception) {
            self::assertEquals($renderable, $exception->renderable);

            throw $exception;
        }
    }

    /**
     * @return iterable<string, array{0: StepInterface, 1: ?StepKey}>
     */
    public static function renderThrowIfPreviousNotSubmittedDataProvider(): iterable
    {
        yield 'with previous renderable' => [new RenderStep(), new StepKey('key')];
        yield 'without previous renderable' => [new SimpleStep(), null];
    }

    public function testRenderStepIsNotRenderableException(): void
    {
        $this->expectExceptionObject(new StepNotRenderableException(new StepKey('key')));
        $this->expectExceptionMessage('The step [key] is not renderable.');

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));

        $this->builder->method('build')
            ->willReturn(new Steps([new Step(new StepKey('key'), new SimpleStep())]));

        $this->form->render(new StepKey('key'));
    }

    public function testHandle(): void
    {
        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);

        $step2 = new Step(new StepKey('key2'), new RenderStep(handleReturn: ['id' => 5, 'name' => 'handle']));
        $step3 = new Step(new StepKey('key3'), new RenderStep());

        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            $step2,
            $step3,
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $event = new BeforeHandleStep(['name' => 'handle'], ['id' => 5], $step2);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($event);

        $next = $this->form->handle(new StepKey('key2'), ['name' => 'handle']);

        $expectedEntity = ['id' => 5, 'name' => 'handle'];

        self::assertEquals($step3->key, $next);
        self::assertEquals($expectedEntity, $this->dataControl->getStepEntity(new StepKey('key2')));
        self::assertEquals($expectedEntity, $this->form->getEntity());
    }

    #[DataProvider('handleWithRebuildDataProvider')]
    public function testHandleWithRebuild(mixed $handleReturn, string $expectedNextKey): void
    {
        $form = new SteppedForm(
            $this->dataControl,
            $this->stepControl,
            $this->storage,
            new DynamicFormBuilder($handleReturn),
            $this->dispatcher, // @phpstan-ignore-line
            new SimpleEntityCopy(),
        );

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);

        $step = new Step(new StepKey('key1'), new RenderStep(handleReturn: $handleReturn));

        $event = new BeforeHandleStep(['name' => 'handle'], ['id' => 5], $step);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn($event);

        $next = $form->handle(new StepKey('key1'), $handleReturn);

        self::assertEquals($expectedNextKey, $next?->value);
    }

    /**
     * @return iterable<string, array{0: mixed, 1: string}>
     */
    public static function handleWithRebuildDataProvider(): iterable
    {
        yield 'without rebuild' => [['id' => 5], 'key3'];
        yield 'with rebuild' => [['id' => 5, 'rebuild' => true], 'key2'];
    }

    public function testHandleWithFinishForm(): void
    {
        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);

        $step2 = new Step(
            new StepKey('key2'),
            new RenderStep(handleReturn: ['id' => 5, 'name' => 'handle']),
            isSubmitted: true,
        );

        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            $step2,
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $matcher = $this->exactly(2);

        $this->dispatcher->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(static function (mixed $value) use ($matcher, $step2) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertEquals(new BeforeHandleStep(['name' => 'handle'], ['id' => 5], $step2), $value),
                    2 => self::assertEquals(new FormFinished(['id' => 5, 'name' => 'handle']), $value),
                    default => true,
                };

                return $value;
            });

        $next = $this->form->handle(new StepKey('key2'), ['name' => 'handle']);

        self::assertNull($next);
        self::assertNull($this->stepControl->getCurrent());
        self::assertNull($this->dataStorage->get(new StepKey('key2')));
    }

    #[DataProvider('handleWithFinishWithNotSubmittedStepsDataProvider')]
    public function testHandleWithFinishWithNotSubmittedSteps(StepInterface $step, ?StepKey $renderable): void
    {
        $this->expectExceptionObject(StepIsNotSubmittedException::finish(new StepKey('key'), $renderable));
        $this->expectExceptionMessage('The Step [key] is not submitted yet.');

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key2'));
        $this->dataStorage->put(new StepKey('key2'), ['id' => 5]);

        $steps = new Steps([
            new Step(new StepKey('key'), $step, isSubmitted: false),
            new Step(new StepKey('key2'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key3'), new RenderStep()),
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $this->dispatcher->method('dispatch')
            ->willReturn(new BeforeHandleStep(null, ['id' => 5], new Step(new StepKey('key'), new RenderStep())));

        try {
            $this->form->handle(new StepKey('key3'), ['name' => 'handle']);
        } catch (StepIsNotSubmittedException $exception) {
            self::assertEquals($renderable, $exception->renderable);

            throw $exception;
        }
    }

    /**
     * @return iterable<string, array{0: StepInterface, 1: ?StepKey}>
     */
    public static function handleWithFinishWithNotSubmittedStepsDataProvider(): iterable
    {
        yield 'with previous renderable' => [new RenderStep(), new StepKey('key')];
        yield 'without previous renderable' => [new SimpleStep(), null];
    }

    public function testHandleSteppedFormErrorsException(): void
    {
        $expected = new SteppedFormErrorsException([]);
        $expected->renderable = new StepKey('key');

        $this->expectExceptionObject($expected);

        $step = $this->createStub(StepInterface::class);

        $step->method('handle')
            ->willThrowException(new SteppedFormErrorsException([]));

        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), $step),
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $this->dispatcher->method('dispatch')
            ->willReturn(new BeforeHandleStep(null, ['id' => 5], new Step(new StepKey('key'), new RenderStep())));

        $this->form->start(['id' => 5]);
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->form->handle(new StepKey('key2'), ['name' => 'handle']);
    }

    public function testHandleThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->form->handle(new StepKey('key'), ['id' => 7]);
    }

    public function testHandleThrowIfPreviousNotSubmitted(): void
    {
        $this->expectExceptionObject(StepIsNotSubmittedException::previous(new StepKey('key2'), new StepKey('key')));

        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key3'));

        $this->builder->method('build')
            ->willReturn(
                new Steps([
                    new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
                    new Step(new StepKey('key2'), new SimpleStep(), isSubmitted: false),
                    new Step(new StepKey('key3'), new RenderStep()),
                ]),
            );

        $this->form->handle(new StepKey('key3'), ['id' => 7]);
    }

    /**
     * @throws FormIsNotStartedException
     */
    public function testCancel(): void
    {
        $this->storage->setCurrentSession('main');
        $this->stepControl->setCurrent(new StepKey('key'));
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);

        $this->form->cancel();

        self::assertNull($this->stepControl->getCurrent());
        self::assertNull($this->dataStorage->get(new StepKey('key')));
    }

    /**
     * @throws FormIsNotStartedException
     */
    public function testCancelThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->form->cancel();
    }
}
