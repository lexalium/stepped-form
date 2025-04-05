<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\StepHandleException;
use Lexal\SteppedForm\Exception\StepNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Form\DataControl;
use Lexal\SteppedForm\Form\StepControl;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Form\Storage\FormStorage;
use Lexal\SteppedForm\Form\Storage\SessionKeyStorageInterface;
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

use function iterator_to_array;

final class SteppedFormTest extends TestCase
{
    use CreateObjectTrait;

    private SessionKeyStorageInterface $sessionStorage;
    private DataStorage $dataStorage;
    private DataControl $dataControl;
    private StepControl $stepControl;
    private FormBuilderInterface&Stub $builder;
    private MockObject $dispatcher;
    private SteppedFormInterface $form;

    protected function setUp(): void
    {
        $this->sessionStorage = new InMemorySessionKeyStorage();
        $formStorage = new FormStorage(new InMemoryStorage(), $this->sessionStorage);
        $this->dataStorage = new DataStorage($formStorage);
        $this->dataControl = new DataControl($this->dataStorage);
        $this->stepControl = new StepControl($formStorage);
        $this->builder = $this->createStub(FormBuilderInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->builder->method('isDynamic')
            ->willReturn(false);

        $this->form = new SteppedForm(
            $this->dataControl,
            $this->stepControl,
            $this->builder,
            $this->dispatcher,
        );
    }

    /**
     * @throws FormNotStartedException
     */
    public function testGetEntityThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormNotStartedException());

        $this->form->getEntity();
    }

    #[DataProvider('startDataProvider')]
    public function testStart(
        Steps $steps,
        ?StepKey $expectedFirst,
        ?object $expectedEntity,
        ?string $expectedKey,
    ): void {
        $this->builder->method('build')
            ->willReturn($steps);

        $this->dispatcher->method('dispatch')
            ->willReturn(
                new BeforeHandleStep(
                    null,
                    self::createObject(['id' => 5]),
                    new Step(new StepKey('key'), new RenderStep()),
                ),
            );

        $first = $this->form->start(self::createObject(['id' => 5]));

        self::assertEquals($expectedFirst, $first);
        self::assertEquals($expectedEntity, $this->dataControl->getInitializeEntity());
        self::assertEquals($expectedEntity, $this->dataControl->getEntity());
        self::assertEquals($expectedKey, $this->stepControl->getCurrent());
        self::assertEquals('__MAIN__', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function startDataProvider(): iterable
    {
        $step1 = new Step(new StepKey('key'), new RenderStep(), isSubmitted: true);
        $step2 = new Step(new StepKey('key2'), new RenderStep(), isSubmitted: true);
        $step3 = new Step(new StepKey('key3'), new SimpleStep(), isSubmitted: true);

        yield 'first step is renderable' => [
            new Steps([$step1, $step2]),
            $step1->key,
            self::createObject(['id' => 5]),
            'key',
        ];

        yield 'first step is not renderable' => [new Steps([$step3, $step2]),
            $step2->key,
            self::createObject(['id' => 5]),
            'key2',
        ];
    }

    public function testStartWithoutRenderable(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 6]));

        $step1 = new Step(new StepKey('key3'), new SimpleStep(), isSubmitted: true);
        $step2 = new Step(new StepKey('key4'), new SimpleStep(), isSubmitted: true);

        $this->builder->method('build')
            ->willReturn(new Steps([$step1, $step2]));

        $this->dispatcher->method('dispatch')
            ->willReturn(
                new BeforeHandleStep(
                    null,
                    self::createObject(['id' => 5]),
                    $step1,
                ),
            );

        $first = $this->form->start(self::createObject(['id' => 5]));

        self::assertEquals(null, $first);
        self::assertEquals(null, $this->stepControl->getCurrent());
        self::assertEquals('__MAIN__', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));
        self::assertNull($this->dataStorage->get(new StepKey('key')));
    }

    public function testCanAgainStartFormWhenFirstNotRenderableStepThrowsException(): void
    {
        $this->expectExceptionObject(new StepHandleException(['can not handle']));

        $throwableStep = new class () implements StepInterface {
            public function handle(object $entity, mixed $data): object
            {
                throw new StepHandleException(['can not handle']);
            }
        };

        $this->builder->method('build')
            ->willReturn(new Steps([new Step(new StepKey('key'), $throwableStep)]));

        $this->dispatcher->method('dispatch')
            ->willReturn(
                new BeforeHandleStep(
                    null,
                    self::createObject(['id' => 5]),
                    new Step(new StepKey('key'), new RenderStep()),
                ),
            );

        try {
            $this->form->start(self::createObject(['id' => 5]));
        } catch (SteppedFormErrorsException) {
            // skip first exception
        }

        $this->form->start(self::createObject(['id' => 5]));
    }

    public function testStartThrowIfAlreadyStarted(): void
    {
        $this->expectExceptionObject(new AlreadyStartedException('key'));

        // first start call
        $this->initialize(['id' => 5], [new Step(new StepKey('key'), new RenderStep())]);

        // first start call with the different session key
        $this->form->start(self::createObject(['id' => 5]), 'first');

        self::assertEquals(self::createObject(['id' => 5]), $this->form->getEntity());

        // second start call with the session key from the first call
        $this->form->start(self::createObject(['id' => 5]));
    }

    public function testStartWithDifferentNamespaces(): void
    {
        $this->builder->method('build')
            ->willReturn(new Steps([new Step(new StepKey('key'), new RenderStep(), isSubmitted: true)]));

        $this->form->start(self::createObject(['id' => 5]), 'first');

        self::assertEquals(self::createObject(['id' => 5]), $this->dataControl->getInitializeEntity());
        self::assertEquals('first', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));

        $this->form->start(self::createObject(['id' => 8]), 'second');

        self::assertEquals(self::createObject(['id' => 8]), $this->dataControl->getInitializeEntity());
        self::assertEquals('second', $this->sessionStorage->get('__CURRENT_SESSION_KEY__'));
    }

    /**
     * @param string[] $submitSteps
     *
     * @throws SteppedFormException
     */
    #[DataProvider('renderDataProvider')]
    public function testRender(Steps $steps, StepKey $key, object $entity, array $submitSteps): void
    {
        $this->initialize(['id' => 4], iterator_to_array($steps), $submitSteps);

        $templateDefinition = $this->form->render($key);

        self::assertEquals(new TemplateDefinition('template', [$entity]), $templateDefinition);
    }

    /**
     * @return array<string, mixed>
     */
    public static function renderDataProvider(): iterable
    {
        $step1 = new Step(
            new StepKey('key'),
            new RenderStep(handleReturn: self::createObject(['id' => 6])),
            isSubmitted: true,
        );

        $steps = new Steps([
            $step1,
            new Step(new StepKey('key3'), new RenderStep('template')),
        ]);

        yield 'template with previous step entity' => [
            $steps,
            new StepKey('key3'),
            self::createObject(['id' => 6]),
            ['key'],
        ];

        $steps = new Steps([
            $step1,
            new Step(
                new StepKey('key2'),
                new RenderStep('template', handleReturn: self::createObject(['id' => 7])),
                isSubmitted: true,
            ),
            new Step(new StepKey('key3'), new RenderStep()),
        ]);

        yield 'template with current step entity' => [
            $steps,
            new StepKey('key2'),
            self::createObject(['id' => 7]),
            ['key', 'key2'],
        ];

        $steps = new Steps([
            new Step(new StepKey('key4'), new RenderStep('template')),
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        yield 'template with initial step entity' => [$steps, new StepKey('key4'), self::createObject(['id' => 4]), []];
    }

    public function testRenderWithBuildCall(): void
    {
        $steps = new Steps([
            new Step(new StepKey('key'), new RenderStep('template')),
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');
        $this->stepControl->setCurrent(new StepKey('key'));

        $templateDefinition = $this->form->render(new StepKey('key'));

        self::assertEquals(new TemplateDefinition('template', [self::createObject(['id' => 5])]), $templateDefinition);
    }

    #[DataProvider('renderPreviousStepEntityNotFoundDataProvider')]
    public function testRenderPreviousStepEntityNotFound(StepInterface $step, ?StepKey $renderable): void
    {
        $exception = new EntityNotFoundException(new StepKey('key2'));
        $exception->renderable = $renderable;

        $this->expectExceptionObject($exception);

        $steps = [
            new Step(new StepKey('key'), $step, isSubmitted: true),
            new Step(new StepKey('key2'), new SimpleStep(), isSubmitted: true),
            new Step(new StepKey('key3'), new RenderStep('template')),
        ];

        $this->initialize(['id' => 4], $steps);

        $this->dataStorage->forgetAfter(new StepKey('key'));

        try {
            $this->form->render(new StepKey('key3'));
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
        $this->expectExceptionObject(new FormNotStartedException());

        $this->form->render(new StepKey('key'));
    }

    #[DataProvider('renderThrowIfPreviousNotSubmittedDataProvider')]
    public function testRenderThrowIfPreviousNotSubmitted(StepInterface $step, ?StepKey $renderable): void
    {
        $this->expectExceptionObject(StepNotSubmittedException::previous(new StepKey('key2'), $renderable));
        $this->expectExceptionMessage('Previous step [key2] has not been submitted.');

        $steps = [
            new Step(new StepKey('key'), $step, isSubmitted: true),
            new Step(new StepKey('key2'), new SimpleStep(), isSubmitted: false),
            new Step(new StepKey('key3'), new RenderStep()),
        ];

        $this->initialize(['id' => 4], $steps);

        try {
            $this->form->render(new StepKey('key3'));
        } catch (StepNotSubmittedException $exception) {
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

    public function testRenderStepNotRenderableException(): void
    {
        $this->expectExceptionObject(new StepNotRenderableException(new StepKey('key')));
        $this->expectExceptionMessage('The step [key2] is not renderable.');

        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), new SimpleStep()),
        ];

        $this->initialize(['id' => 4], $steps);

        $this->form->render(new StepKey('key2'));
    }

    public function testHandle(): void
    {
        $step2 = new Step(
            new StepKey('key2'),
            new RenderStep(handleReturn: self::createObject(['id' => 5, 'name' => 'handle'])),
        );

        $step3 = new Step(new StepKey('key3'), new RenderStep());

        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            $step2,
            $step3,
        ];

        $this->initialize(['id' => 4], $steps);
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));

        $event = new BeforeHandleStep(['name' => 'handle'], self::createObject(['id' => 5]), $step2);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($event);

        $next = $this->form->handle(new StepKey('key2'), ['name' => 'handle']);

        $expectedEntity = self::createObject(['id' => 5, 'name' => 'handle']);

        self::assertEquals($step3->key, $next);
        self::assertEquals($expectedEntity, $this->dataControl->getStepEntity(new StepKey('key2')));
        self::assertEquals($expectedEntity, $this->form->getEntity());
    }

    public function testHandleWithBuildCall(): void
    {
        $step = new Step(new StepKey('key'), new RenderStep('template'));

        $steps = new Steps([
            $step,
            new Step(new StepKey('key2'), new RenderStep('template')),
        ]);

        $this->builder->method('build')
            ->willReturn($steps);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn(
                new BeforeHandleStep([], self::createObject(['id' => 5]), $step),
            );

        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');
        $this->stepControl->setCurrent(new StepKey('key'));

        $nextKey = $this->form->handle(new StepKey('key'), []);

        self::assertEquals(new StepKey('key2'), $nextKey);
    }

    #[DataProvider('handleWithRebuildDataProvider')]
    public function testHandleWithRebuild(object $handleReturn, string $expectedNextKey): void
    {
        $form = new SteppedForm(
            $this->dataControl,
            $this->stepControl,
            new DynamicFormBuilder($handleReturn),
            $this->dispatcher, // @phpstan-ignore-line
        );

        $form->start(self::createObject(['id' => 5]));

        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));

        $step = new Step(new StepKey('key1'), new RenderStep(handleReturn: $handleReturn));

        $event = new BeforeHandleStep(['name' => 'handle'], self::createObject(['id' => 5]), $step);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn($event);

        $next = $form->handle(new StepKey('key1'), $handleReturn);

        self::assertEquals($expectedNextKey, $next?->value);
    }

    /**
     * @return iterable<string, array{0: object, 1: string}>
     */
    public static function handleWithRebuildDataProvider(): iterable
    {
        yield 'without rebuild' => [self::createObject(['id' => 5]), 'key3'];
        yield 'with rebuild' => [self::createObject(['id' => 5, 'rebuild' => true]), 'key2'];
    }

    public function testHandleWithFinishForm(): void
    {
        $step2 = new Step(
            new StepKey('key2'),
            new RenderStep(handleReturn: self::createObject(['id' => 5, 'name' => 'handle'])),
            isSubmitted: true,
        );

        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            $step2,
        ];

        $this->initialize(['id' => 4], $steps);
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));

        $matcher = $this->exactly(2);

        $this->dispatcher->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(static function (mixed $value) use ($matcher, $step2) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertEquals(
                        new BeforeHandleStep(['name' => 'handle'], self::createObject(['id' => 5]), $step2),
                        $value,
                    ),
                    2 => self::assertEquals(
                        new FormFinished(self::createObject(['id' => 5, 'name' => 'handle'])),
                        $value,
                    ),
                    default => true,
                };

                return $value;
            });

        $next = $this->form->handle(new StepKey('key2'), ['name' => 'handle']);

        self::assertNull($next);
        self::assertNull($this->stepControl->getCurrent());
        self::assertNull($this->dataStorage->get(new StepKey('key2')));
    }

    #[DataProvider('handleWhenFinishWithNotSubmittedStepsDataProvider')]
    public function testHandleWhenFinishWithNotSubmittedSteps(StepInterface $step, ?StepKey $renderable): void
    {
        $this->expectExceptionObject(StepNotSubmittedException::finish(new StepKey('key'), $renderable));
        $this->expectExceptionMessage('The step [key] has not been submitted yet.');

        $steps = [
            new Step(new StepKey('key'), $step, isSubmitted: false),
            new Step(
                new StepKey('key2'),
                new RenderStep(handleReturn: self::createObject(['id' => 5])),
                isSubmitted: true,
            ),
            new Step(new StepKey('key3'), new RenderStep()),
        ];

        $this->initialize(['id' => 4], $steps);
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 5]));

        try {
            $this->form->handle(new StepKey('key3'), ['name' => 'handle']);
        } catch (StepNotSubmittedException $exception) {
            self::assertEquals($renderable, $exception->renderable);

            throw $exception;
        }
    }

    /**
     * @return iterable<string, array{0: StepInterface, 1: ?StepKey}>
     */
    public static function handleWhenFinishWithNotSubmittedStepsDataProvider(): iterable
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

        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), $step),
        ];

        $this->initialize(['id' => 5], $steps, ['key']);

        $this->form->handle(new StepKey('key2'), ['name' => 'handle']);
    }

    public function testHandleThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormNotStartedException());

        $this->form->handle(new StepKey('key'), ['id' => 7]);
    }

    public function testHandleThrowIfPreviousNotSubmitted(): void
    {
        $this->expectExceptionObject(StepNotSubmittedException::previous(new StepKey('key2'), new StepKey('key')));

        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), new SimpleStep(), isSubmitted: false),
            new Step(new StepKey('key3'), new RenderStep()),
        ];

        $this->initialize(['id' => 5], $steps);

        $this->form->handle(new StepKey('key3'), ['id' => 7]);
    }

    /**
     * @throws SteppedFormException
     */
    public function testCancel(): void
    {
        $steps = [
            new Step(new StepKey('key'), new RenderStep(), isSubmitted: true),
            new Step(new StepKey('key2'), new RenderStep()),
        ];

        $this->initialize(['id' => 5], $steps, ['key']);

        $this->form->cancel();

        self::assertNull($this->stepControl->getCurrent());
        self::assertNull($this->dataStorage->get(new StepKey('key')));
    }

    /**
     * @throws FormNotStartedException
     */
    public function testCancelThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormNotStartedException());

        $this->form->cancel();
    }

    /**
     * @param array<string, mixed> $initial
     * @param Step[] $steps
     * @param string[] $submitSteps
     *
     * @throws SteppedFormException
     */
    private function initialize(array $initial, array $steps, array $submitSteps = []): void
    {
        $this->builder->method('build')
            ->willReturn(new Steps($steps));

        $this->dispatcher->method('dispatch')
            ->willReturn(
                new BeforeHandleStep(
                    [],
                    self::createObject(['random' => 5]),
                    new Step(new StepKey('random'), new RenderStep()),
                ),
            );

        $this->form->start(self::createObject($initial));

        foreach ($submitSteps as $step) {
            $this->form->handle(new StepKey($step), []);
        }
    }
}
