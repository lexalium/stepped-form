<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\Storage\DataStorage;
use Lexal\SteppedForm\Form\Storage\FormStorage;
use Lexal\SteppedForm\Form\Storage\FormStorageInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Tests\CreateObjectTrait;
use Lexal\SteppedForm\Tests\InMemoryStorage;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function get_debug_type;
use function sprintf;

final class DataStorageTest extends TestCase
{
    use CreateObjectTrait;

    private DataStorage $dataStorage;
    private InMemoryStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new InMemoryStorage();
        $this->dataStorage = new DataStorage(new FormStorage($this->storage));
    }

    public function testGetInitializeEntityWithoutStartingForm(): void
    {
        $this->expectException(RuntimeException::class);

        $this->dataStorage->getInitializeEntity();
    }

    /**
     * @throws KeysNotFoundInStorageException
     */
    public function testGetLast(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));

        self::assertEquals(self::createObject(['id' => 6]), $this->dataStorage->getLast());

        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 7]));

        self::assertEquals(self::createObject(['id' => 7]), $this->dataStorage->getLast());
    }

    public function testGetLastKeysNotFoundException(): void
    {
        $this->expectExceptionObject(new KeysNotFoundInStorageException());
        $this->expectExceptionMessage('There are no data saved in the storage.');

        $this->dataStorage->initialize(self::createObject(['id' => 5]), 'main');

        $this->dataStorage->getLast();
    }

    public function testGetLastWhenEntityNotFound(): void
    {
        $this->expectExceptionObject(new KeysNotFoundInStorageException());
        $this->expectExceptionMessage('There are no data saved in the storage.');

        $data = [
            'key' => self::createObject(['id' => 5]),
            'key2' => null,
        ];

        $this->storage->put('__STEPS__', FormStorageInterface::DEFAULT_SESSION_KEY, $data);

        self::assertEquals(self::createObject(['id' => 5]), $this->dataStorage->get(new StepKey('key')));

        $this->dataStorage->getLast();
    }

    public function testInitialize(): void
    {
        $this->dataStorage->initialize(self::createObject(['id' => 5]), 'main');

        self::assertEquals(self::createObject(['id' => 5]), $this->dataStorage->getInitializeEntity());

        $this->dataStorage->initialize(self::createObject(['name' => 'test']), 'customer');

        self::assertEquals(self::createObject(['name' => 'test']), $this->dataStorage->getInitializeEntity());
    }

    public function testPutDifferentEntityTypes(): void
    {
        $entity1 = new class () extends stdClass {
            public int $id = 7;
        };

        $entity2 = new class () extends stdClass {
            public int $id = 6;
        };

        $this->expectExceptionObject(
            new RuntimeException(
                sprintf(
                    'Entities should have the same type between steps. Expected: %s. Given: %s.',
                    get_debug_type($entity1),
                    get_debug_type($entity2),
                ),
            ),
        );

        $this->dataStorage->put(new StepKey('key'), $entity1);
        $this->dataStorage->put(new StepKey('key2'), $entity2);
    }

    public function testPutCheckExistenceAndGet(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 7]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertFalse($this->dataStorage->has(new StepKey('key3')));

        self::assertEquals(self::createObject(['id' => 7]), $this->dataStorage->get(new StepKey('key')));
        self::assertEquals(self::createObject(['id' => 8]), $this->dataStorage->get(new StepKey('key2')));
        self::assertNull($this->dataStorage->get(new StepKey('key3')));

        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));

        self::assertTrue($this->dataStorage->has(new StepKey('key')));
        self::assertTrue($this->dataStorage->has(new StepKey('key2')));
        self::assertFalse($this->dataStorage->has(new StepKey('key3')));

        self::assertEquals(self::createObject(['id' => 5]), $this->dataStorage->get(new StepKey('key')));
        self::assertEquals(self::createObject(['id' => 5]), $this->dataStorage->get(new StepKey('key2')));
        self::assertNull($this->dataStorage->get(new StepKey('key3')));
    }

    public function testPutWithoutAnyChanges(): void
    {
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));
        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 9]));

        self::assertEquals(self::createObject(['id' => 8]), $this->dataStorage->get(new StepKey('key2')));
        self::assertEquals(self::createObject(['id' => 9]), $this->dataStorage->get(new StepKey('key3')));

        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));

        self::assertEquals(self::createObject(['id' => 8]), $this->dataStorage->get(new StepKey('key2')));
        self::assertEquals(self::createObject(['id' => 9]), $this->dataStorage->get(new StepKey('key3')));
    }

    public function testPutSkipReflect(): void
    {
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));
        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 9]));

        self::assertEquals(self::createObject(['id' => 8]), $this->dataStorage->get(new StepKey('key2')));
        self::assertEquals(self::createObject(['id' => 9]), $this->dataStorage->get(new StepKey('key3')));

        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));

        self::assertEquals(self::createObject(['id' => 8]), $this->dataStorage->get(new StepKey('key2')));
        self::assertEquals(self::createObject(['id' => 9]), $this->dataStorage->get(new StepKey('key3')));
    }

    public function testPutAndReflectToNextEntities(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 8]));
        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 9]));
        $this->dataStorage->put(new StepKey('key4'), self::createObject(['id' => 10]));

        $expectedEntity = self::createObject(['id' => 8, 'name' => 'name']);

        $this->dataStorage->put(new StepKey('key2'), $expectedEntity);

        self::assertEquals(self::createObject(['id' => 5]), $this->dataStorage->get(new StepKey('key')));
        self::assertSame(
            $expectedEntity,
            $this->dataStorage->get(new StepKey('key2')),
        );

        self::assertEquals(
            self::createObject(['id' => 9, 'name' => 'name']),
            $this->dataStorage->get(new StepKey('key3')),
        );

        self::assertEquals(
            self::createObject(['id' => 10, 'name' => 'name']),
            $this->dataStorage->get(new StepKey('key4')),
        );
    }

    public function testForgetAfter(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));
        $this->dataStorage->put(new StepKey('key2'), self::createObject(['id' => 6]));
        $this->dataStorage->put(new StepKey('key3'), self::createObject(['id' => 7]));

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

    public function testClear(): void
    {
        $this->dataStorage->put(new StepKey('key'), self::createObject(['id' => 5]));

        $this->dataStorage->clear();

        self::assertNull($this->dataStorage->get(new StepKey('key')));
    }
}
