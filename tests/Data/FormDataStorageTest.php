<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Data;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Form\Storage\DataStorageInterface;
use Lexal\SteppedForm\Form\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormDataStorageTest extends TestCase
{
    private MockObject $storage;
    private DataStorageInterface $formData;

    public function testGetLastWithData(): void
    {
        $this->storage->expects($this->once())
            ->method('keys')
            ->willReturn(['key', 'key2', 'key3']);

        $this->storage->expects($this->once())
            ->method('get')
            ->with('key3')
            ->willReturn('value3');

        $this->assertEquals('value3', $this->formData->getLast());
    }

    public function testGetLastWithoutData(): void
    {
        $this->expectExceptionObject(new KeysNotFoundInStorageException());

        $this->storage->expects($this->once())
            ->method('keys')
            ->willReturn([]);

        $this->formData->getLast();
    }

    public function testForgetAfterWithData(): void
    {
        $this->storage->expects($this->once())
            ->method('keys')
            ->willReturn(['key', 'key2', 'key3']);

        $this->storage->expects($this->exactly(2))
            ->method('forget')
            ->withConsecutive(['key2'], ['key3']);

        $this->formData->forgetAfter('key2');
    }

    public function testForgetAfterWithoutData(): void
    {
        $this->storage->expects($this->once())
            ->method('keys')
            ->willReturn([]);

        $this->storage->expects($this->never())
            ->method('forget');

        $this->formData->forgetAfter('key2');
    }

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);

        $this->formData = new DataStorage($this->storage);

        parent::setUp();
    }
}
