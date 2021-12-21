<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Data;

use Lexal\SteppedForm\Data\StepControl;
use Lexal\SteppedForm\Data\StepControlInterface;
use Lexal\SteppedForm\Data\Storage\StorageInterface;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StepControlTest extends TestCase
{
    private const STORAGE_KEY = 'current-step';

    private MockObject $storage;
    private StepControlInterface $stepControl;

    public function testSetCurrent(): void
    {
        $this->storage->expects($this->once())
            ->method('put')
            ->with(self::STORAGE_KEY, 'key');

        $this->stepControl->setCurrent('key');
    }

    public function testGetCurrent(): void
    {
        $this->storage->expects($this->once())
            ->method('get')
            ->with(self::STORAGE_KEY)
            ->willReturn('key');

        $this->stepControl->getCurrent();
    }

    public function testGetCurrentNotFound(): void
    {
        $this->expectExceptionObject(new CurrentStepNotFoundException());

        $this->storage->expects($this->once())
            ->method('get')
            ->with(self::STORAGE_KEY)
            ->willReturn(null);

        $this->stepControl->getCurrent();
    }

    public function testHasCurrent(): void
    {
        $this->storage->expects($this->once())
            ->method('get')
            ->with(self::STORAGE_KEY)
            ->willReturn('key');

        $this->assertTrue($this->stepControl->hasCurrent());
    }

    public function testDoesntHaveCurrent(): void
    {
        $this->storage->expects($this->once())
            ->method('get')
            ->with(self::STORAGE_KEY)
            ->willReturn(null);

        $this->assertFalse($this->stepControl->hasCurrent());
    }

    public function testReset(): void
    {
        $this->storage->expects($this->once())
            ->method('forget')
            ->with(self::STORAGE_KEY);

        $this->stepControl->reset();
    }

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);

        $this->stepControl = new StepControl($this->storage);

        parent::setUp();
    }
}
