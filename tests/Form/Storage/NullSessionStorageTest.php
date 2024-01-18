<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\Storage;

use Lexal\SteppedForm\Form\Storage\NullSessionStorage;
use PHPUnit\Framework\TestCase;

final class NullSessionStorageTest extends TestCase
{
    public function testGet(): void
    {
        $storage = new NullSessionStorage();

        self::assertNull($storage->get('session'));
    }

    public function testPut(): void
    {
        $storage = new NullSessionStorage();

        $storage->put('session', 'main');

        self::assertNull($storage->get('session'));
    }
}
