<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Form\DataControl;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Form\Storage\FormStorage;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepBehaviourInterface;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\CreateObjectTrait;
use Lexal\SteppedForm\Tests\InMemoryStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DataControlTest extends TestCase
{
    use CreateObjectTrait;

    private DataStorage $dataStorage;
    private DataControl $dataControl;

    protected function setUp(): void
    {
        $this->dataStorage = new DataStorage(new FormStorage(new InMemoryStorage()));
        $this->dataControl = new DataControl($this->dataStorage);
    }

    public function testInitializeAndGetInitializeEntity(): void
    {
        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');

        self::assertEquals(self::createObject(['id' => 5]), $this->dataControl->getInitializeEntity());
    }

    public function testGetEntityWithoutSubmittedStepsAndWithInitializeEntity(): void
    {
        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');

        self::assertEquals(self::createObject(['id' => 5]), $this->dataControl->getEntity());
    }

    public function testGetEntityWithSubmittedSteps(): void
    {
        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 6]));

        self::assertEquals(self::createObject(['id' => 6]), $this->dataControl->getEntity());
    }

    /**
     * @throws EntityNotFoundException
     */
    public function testGetStepEntity(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));

        self::assertEquals(self::createObject(['id' => 5]), $this->dataControl->getStepEntity(new StepKey('key')));
    }

    /**
     * @throws EntityNotFoundException
     */
    public function testGetStepEntityEntityNotFoundException(): void
    {
        $this->expectExceptionObject(new EntityNotFoundException(new StepKey('key')));
        $this->expectExceptionMessage('There is no data for the given [key] step.');

        $this->dataControl->getStepEntity(new StepKey('key'));
    }

    /**
     * @throws EntityNotFoundException
     */
    #[DataProvider('handleDataProvider')]
    public function testHandle(StepInterface $step, bool $isDynamicForm, ?object $expectedDataKey3): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));
        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 7, 'name' => 'test']));

        $this->dataControl->handle(
            new Step(new StepKey('key2'), $step),
            self::createObject(['id' => 99]),
            $isDynamicForm,
        );

        self::assertEquals(self::createObject(['id' => 99]), $this->dataControl->getStepEntity(new StepKey('key2')));
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
            self::createObject(['id' => 99, 'name' => 'test']),
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
            self::createObject(['id' => 99, 'name' => 'test']),
        ];

        yield 'static form and step does not implement StepBehaviourInterface' => [
            self::createSimpleStep(),
            false,
            self::createObject(['id' => 99, 'name' => 'test']),
        ];
    }

    public function testCancel(): void
    {
        $this->dataControl->initialize(self::createObject(['id' => 5]), 'main');
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 6]));

        self::assertTrue($this->dataControl->hasStepEntity(new StepKey('key')));

        $this->dataControl->cancel();

        self::assertFalse($this->dataControl->hasStepEntity(new StepKey('key')));
    }

    private static function createSimpleStep(): StepInterface
    {
        return new class () implements StepInterface {
            public function handle(object $entity, mixed $data): object
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

            public function forgetDataAfterCurrent(object $entity): bool
            {
                return $this->forgetAfterCurrent;
            }

            public function handle(object $entity, mixed $data): object
            {
                return $entity;
            }
        };
    }
}
