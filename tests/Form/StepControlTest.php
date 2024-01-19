<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Form\StepControl;
use Lexal\SteppedForm\Form\Storage\FormStorage;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\InMemoryStorage;
use PHPUnit\Framework\TestCase;

final class StepControlTest extends TestCase
{
    private StepControl $stepControl;

    protected function setUp(): void
    {
        $this->stepControl = new StepControl(new FormStorage(new InMemoryStorage()));
    }

    public function testSetAndGetCurrent(): void
    {
        self::assertNull($this->stepControl->getCurrent());

        $this->stepControl->setCurrent(new StepKey('key'));

        self::assertEquals('key', $this->stepControl->getCurrent());
    }

    public function testThrowIfAlreadyStarted(): void
    {
        $this->expectExceptionObject(new AlreadyStartedException('key'));
        $this->expectExceptionMessage('The form has already started.');

        $this->stepControl->setCurrent(new StepKey('key'));

        try {
            $this->stepControl->throwIfAlreadyStarted();
        } catch (AlreadyStartedException $exception) {
            self::assertEquals('key', $exception->currentKey);

            throw $exception;
        }
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testThrowIfAlreadyStartedWithoutException(): void
    {
        $this->stepControl->throwIfAlreadyStarted();
    }

    public function testThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());
        $this->expectExceptionMessage('The stepped form is not started yet.');

        $this->stepControl->throwIfNotStarted();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testThrowIfNotStartedWithoutException(): void
    {
        $this->stepControl->setCurrent(new StepKey('key'));

        $this->stepControl->throwIfNotStarted();
    }
}
