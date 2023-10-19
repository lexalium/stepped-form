<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Data\Storage;

use Lexal\SteppedForm\Form\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;

class ArrayStorageTest extends TestCase
{
    public function testPut(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        $this->assertTrue($storage->has('key'));
        $this->assertEquals('value', $storage->get('key'));

        $storage->put('key', 'value2');

        $this->assertEquals('value2', $storage->get('key'));
        $this->assertEquals(['key'], $storage->keys());
    }

    public function testGet(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        $this->assertEquals('value', $storage->get('key'));
        $this->assertEquals('default', $storage->get('key-not-exists', 'default'));
        $this->assertNull($storage->get('key-not-exists'));
    }

    public function testKeys(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');
        $storage->put('key2', 'value2');
        $storage->put('key', 'value3');

        $this->assertEquals(['key', 'key2'], $storage->keys());
    }

    public function testHas(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');

        $this->assertTrue($storage->has('key'));
        $this->assertFalse($storage->has('key2'));
    }

    public function testForget(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');
        $storage->forget('key');

        $this->assertFalse($storage->has('key'));
    }

    public function testClear(): void
    {
        $storage = new ArrayStorage();

        $storage->put('key', 'value');
        $storage->put('key2', 'value2');

        $storage->clear();

        $this->assertFalse($storage->has('key'));
        $this->assertFalse($storage->has('key2'));
    }
}
