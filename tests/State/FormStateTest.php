<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\State;

use Lexal\SteppedForm\Data\FormDataStorageInterface;
use Lexal\SteppedForm\Data\StepControlInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\State\FormState;
use Lexal\SteppedForm\State\FormStateInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormStateTest extends TestCase
{
    private const KEY_INITIALIZE_ENTITY = '__INITIALIZE__';

    private MockObject $stepControl;
    private MockObject $formData;
    private FormStateInterface $formState;

    public function testGetEntity(): void
    {
        $this->stepControl->expects($this->once())
            ->method('hasCurrent')
            ->willReturn(true);

        $this->formData->expects($this->once())
            ->method('getLast')
            ->willReturn('value');

        $this->assertEquals('value', $this->formState->getEntity());
    }

    public function testGetEntityFromInitializeEntity(): void
    {
        $this->stepControl->expects($this->exactly(2))
            ->method('hasCurrent')
            ->willReturn(true);

        $this->formData->expects($this->once())
            ->method('getLast')
            ->willThrowException(new KeysNotFoundInStorageException());

        $this->formData->expects($this->once())
            ->method('get')
            ->with(self::KEY_INITIALIZE_ENTITY)
            ->willReturn('value');

        $this->assertEquals('value', $this->formState->getEntity());
    }

    public function testGetEntityFormIsNotStartedException(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->stepControl->expects($this->once())
            ->method('hasCurrent')
            ->willReturn(false);

        $this->formData->expects($this->never())
            ->method('getLast');

        $this->formState->getEntity();
    }

    public function testGetStepEntity(): void
    {
        $this->formData->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $this->formData->expects($this->once())
            ->method('get')
            ->willReturn('value');

        $this->assertEquals('value', $this->formState->getStepEntity('key'));
    }

    public function testGetStepEntityEntityNotFoundException(): void
    {
        $this->expectExceptionObject(new EntityNotFoundException('key'));

        $this->formData->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $this->formState->getStepEntity('key');
    }

    public function testInitializeWithEmptyState(): void
    {
        $this->formData->expects($this->once())
            ->method('clear');

        $this->formData->expects($this->once())
            ->method('put')
            ->with(self::KEY_INITIALIZE_ENTITY, 'value');

        $this->stepControl->expects($this->once())
            ->method('setCurrent')
            ->with('key');

        $this->stepControl->expects($this->once())
            ->method('getCurrent')
            ->willThrowException(new CurrentStepNotFoundException());

        $this->formState->initialize('value', $this->createCollection());
    }

    public function testInitializeWithExistsState(): void
    {
        $this->expectExceptionObject(
            new AlreadyStartedException(
                'key',
                new Step('key', $this->createMock(StepInterface::class)),
            ),
        );

        $this->formData->expects($this->never())
            ->method('put');

        $this->stepControl->expects($this->never())
            ->method('setCurrent');

        $this->stepControl->expects($this->once())
            ->method('getCurrent')
            ->willReturn('key');

        $this->formState->initialize('value', $this->createCollection());
    }

    public function testHandleWithoutNext(): void
    {
        $this->stepControl->expects($this->never())
            ->method('setCurrent');

        $this->handle();
    }

    public function testHandleWithNext(): void
    {
        $this->stepControl->expects($this->once())
            ->method('setCurrent')
            ->with('key');

        $this->handle(new Step('key', $this->createMock(StepInterface::class)));
    }

    public function testFinish(): void
    {
        $this->stepControl->expects($this->once())
            ->method('hasCurrent')
            ->willReturn(true);

        $this->formData->expects($this->once())
            ->method('clear');

        $this->stepControl->expects($this->once())
            ->method('reset');

        $this->formState->finish();
    }

    public function testFinishFormIsNotStartedException(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->stepControl->expects($this->once())
            ->method('hasCurrent')
            ->willReturn(false);

        $this->formData->expects($this->never())
            ->method('clear');

        $this->stepControl->expects($this->never())
            ->method('reset');

        $this->formState->finish();
    }

    protected function setUp(): void
    {
        $this->stepControl = $this->createMock(StepControlInterface::class);
        $this->formData = $this->createMock(FormDataStorageInterface::class);

        $this->formState = new FormState($this->formData, $this->stepControl);

        parent::setUp();
    }

    private function handle(?Step $next = null): void
    {
        $this->formData->expects($this->once())
            ->method('forgetAfter')
            ->with('key');

        $this->formData->expects($this->once())
            ->method('put')
            ->with('key', 'value');

        $this->formState->handle('key', 'value', $next);
    }

    private function createCollection(): StepsCollection
    {
        return new StepsCollection([
            new Step('key', $this->createMock(StepInterface::class)),
        ]);
    }
}
