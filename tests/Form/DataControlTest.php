<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Form\DataControl;
use Lexal\SteppedForm\Form\Storage\ArrayStorage;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepBehaviourInterface;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\InMemorySessionStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataControlTest extends TestCase
{
    private DataStorage $dataStorage;
    private DataControl $dataControl;

    protected function setUp(): void
    {
        $this->dataStorage = new DataStorage(new ArrayStorage(new InMemorySessionStorage()));
        $this->dataControl = new DataControl($this->dataStorage);
    }

    public function testStartAndGetInitializeEntity(): void
    {
        self::assertNull($this->dataControl->getInitializeEntity());

        $this->dataControl->start(['id' => 5]);

        self::assertEquals(['id' => 5], $this->dataControl->getInitializeEntity());
    }

    public function testGetEntityWithoutSubmittedStepsAndWithInitializeEntity(): void
    {
        $this->dataControl->start(['id' => 5]);

        self::assertEquals(['id' => 5], $this->dataControl->getEntity());
    }

    public function testGetEntityWithSubmittedSteps(): void
    {
        $this->dataControl->start(['id' => 5]);
        $this->dataStorage->put(new StepKey('key'), ['id' => 6]);

        self::assertEquals(['id' => 6], $this->dataControl->getEntity());
    }

    /**
     * @throws EntityNotFoundException
     */
    public function testGetStepEntity(): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);

        self::assertEquals(['id' => 5], $this->dataControl->getStepEntity(new StepKey('key')));
    }

    /**
     * @throws EntityNotFoundException
     */
    public function testGetStepEntityEntityNotFoundException(): void
    {
        $this->expectExceptionObject(new EntityNotFoundException(new StepKey('key')));

        $this->dataControl->getStepEntity(new StepKey('key'));
    }

    /**
     * @throws EntityNotFoundException
     */
    #[DataProvider('handleDataProvider')]
    public function testHandle(StepInterface $step, bool $isDynamicForm, mixed $expectedDataKey3): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);
        $this->dataStorage->put(new StepKey('key3'), ['id' => 7]);

        $this->dataControl->handle(new Step(new StepKey('key2'), $step), ['id' => 99], $isDynamicForm);

        self::assertEquals(['id' => 99], $this->dataControl->getStepEntity(new StepKey('key2')));
        self::assertEquals($expectedDataKey3, $this->dataStorage->get(new StepKey('key3')));
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function handleDataProvider(): iterable
    {
        yield 'dynamic form and step implements StepBehaviourInterface with forget = true' => [
            self::createStepBehaviourStep(true),
            true,
            null,
        ];

        yield 'dynamic form and step implements StepBehaviourInterface with forget = false' => [
            self::createStepBehaviourStep(false),
            true,
            ['id' => 7],
        ];

        yield 'dynamic form and step does not implement StepBehaviourInterface' => [
            self::createSimpleStep(),
            true,
            null,
        ];

        yield 'static form and step implements StepBehaviourInterface with forget = true' => [
            self::createStepBehaviourStep(true),
            false,
            null,
        ];

        yield 'static form and step implements StepBehaviourInterface with forget = false' => [
            self::createStepBehaviourStep(false),
            false,
            ['id' => 7],
        ];

        yield 'static form and step does not implement StepBehaviourInterface' => [
            self::createSimpleStep(),
            false,
            ['id' => 7],
        ];
    }

    private static function createSimpleStep(): StepInterface
    {
        return new class () implements StepInterface {
            public function handle(mixed $entity, mixed $data): mixed
            {
                return $entity;
            }
        };
    }

    private static function createStepBehaviourStep(bool $forgetAfterCurrent): StepInterface
    {
        return new class ($forgetAfterCurrent) implements StepInterface, StepBehaviourInterface {
            public function __construct(private readonly bool $forgetAfterCurrent)
            {
            }

            public function forgetDataAfterCurrent(mixed $entity): bool
            {
                return $this->forgetAfterCurrent;
            }

            public function handle(mixed $entity, mixed $data): mixed
            {
                return $entity;
            }
        };
    }
}
