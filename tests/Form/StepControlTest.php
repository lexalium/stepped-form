<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Form\StepControl;
use Lexal\SteppedForm\Form\Storage\ArrayStorage;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\InMemorySessionStorage;
use PHPUnit\Framework\TestCase;

final class StepControlTest extends TestCase
{
    private StepControl $stepControl;

    protected function setUp(): void
    {
        $this->stepControl = new StepControl(new ArrayStorage(new InMemorySessionStorage()));
    }

    public function testSetAndGetCurrent(): void
    {
        self::assertNull($this->stepControl->getCurrent());

        $this->stepControl->setCurrent(new StepKey('key'));

        self::assertEquals('key', $this->stepControl->getCurrent());
    }

    /**
     * @throws AlreadyStartedException
     */
    public function testThrowIfAlreadyStarted(): void
    {
        $this->expectExceptionObject(new AlreadyStartedException('key'));

        $this->stepControl->setCurrent(new StepKey('key'));

        $this->stepControl->throwIfAlreadyStarted();
    }

    /**
     * @doesNotPerformAssertions
     *
     * @throws AlreadyStartedException
     */
    public function testThrowIfAlreadyStartedWithoutException(): void
    {
        $this->stepControl->throwIfAlreadyStarted();
    }

    /**
     * @throws FormIsNotStartedException
     */
    public function testThrowIfNotStarted(): void
    {
        $this->expectExceptionObject(new FormIsNotStartedException());

        $this->stepControl->throwIfNotStarted();
    }

    /**
     * @doesNotPerformAssertions
     *
     * @throws FormIsNotStartedException
     */
    public function testThrowIfNotStartedWithoutException(): void
    {
        $this->stepControl->setCurrent(new StepKey('key'));

        $this->stepControl->throwIfNotStarted();
    }
}
