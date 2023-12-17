<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\Storage;

use Lexal\SteppedForm\Form\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;

final class ArrayStorageTest extends TestCase
{
    public function testPut(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        self::assertTrue($storage->has('key'));
        self::assertEquals('value', $storage->get('key'));

        $storage->put('key', 'value2');

        self::assertEquals('value2', $storage->get('key'));
    }

    public function testGet(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        self::assertEquals('value', $storage->get('key'));
        self::assertEquals('default', $storage->get('key-not-exists', 'default'));
        self::assertNull($storage->get('key-not-exists'));
    }

    public function testHas(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        self::assertTrue($storage->has('key'));
        self::assertFalse($storage->has('key2'));
    }

    public function testClear(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');
        $storage->put('key2', 'value2');

        $storage->clear();

        self::assertFalse($storage->has('key'));
        self::assertFalse($storage->has('key2'));
    }
}
