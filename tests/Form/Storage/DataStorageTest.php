<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\InMemoryStorage;
use PHPUnit\Framework\TestCase;

final class DataStorageTest extends TestCase
{
    private DataStorage $dataStorage;

    protected function setUp(): void
    {
        $this->dataStorage = new DataStorage(new InMemoryStorage());
    }

    /**
     * @throws KeysNotFoundInStorageException
     */
    public function testGetLast(): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);

        self::assertEquals(['id' => 6], $this->dataStorage->getLast());

        $this->dataStorage->put(new StepKey('key3'), ['id' => 7]);

        self::assertEquals(['id' => 7], $this->dataStorage->getLast());
    }

    /**
     * @throws KeysNotFoundInStorageException
     */
    public function testGetLastKeysNotFoundException(): void
    {
        $this->expectExceptionObject(new KeysNotFoundInStorageException());
        $this->expectExceptionMessage('There are no data saved in the storage.');

        $this->dataStorage->getLast();
    }

    public function testPutCheckExistenceAndGet(): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 7]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 8]);

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertFalse($this->dataStorage->has(new StepKey('key3')));

        self::assertEquals(['id' => 7], $this->dataStorage->get(new StepKey('key')));
        self::assertEquals(['id' => 8], $this->dataStorage->get(new StepKey('key2')));
        self::assertNull($this->dataStorage->get(new StepKey('key3')));

        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertFalse($this->dataStorage->has(new StepKey('key3')));

        self::assertEquals(['id' => 5], $this->dataStorage->get(new StepKey('key')));
        self::assertEquals(['id' => 8], $this->dataStorage->get(new StepKey('key2')));
        self::assertNull($this->dataStorage->get(new StepKey('key3')));
    }

    public function testForgetAfter(): void
    {
        $this->dataStorage->put(new StepKey('key'), ['id' => 5]);
        $this->dataStorage->put(new StepKey('key2'), ['id' => 6]);
        $this->dataStorage->put(new StepKey('key3'), ['id' => 7]);

        $this->dataStorage->forgetAfter(new StepKey('key3'));

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertTrue($this->dataStorage->has(new StepKey('key3')));

        $this->dataStorage->forgetAfter(new StepKey('key4'));

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertTrue($this->dataStorage->has(new StepKey('key3')));

        $this->dataStorage->forgetAfter(new StepKey('key'));

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertFalse($this->dataStorage->has(new StepKey('key2')));
        self::assertFalse($this->dataStorage->has(new StepKey('key3')));
    }
}
