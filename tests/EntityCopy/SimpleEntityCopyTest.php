<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\EntityCopy;

use Lexal\SteppedForm\EntityCopy\SimpleEntityCopy;
use PHPUnit\Framework\TestCase;
use stdClass;

class SimpleEntityCopyTest extends TestCase
{
    public function testCopyScalar(): void
    {
        $entityCopy = new SimpleEntityCopy();

        self::assertEquals(5, $entityCopy->copy(5));
        self::assertEquals('string', $entityCopy->copy('string'));
        self::assertEquals(true, $entityCopy->copy(true));
        self::assertEquals(['key' => 'test', 'number' => 5], $entityCopy->copy(['key' => 'test', 'number' => 5]));
    }

    public function testCopyObject(): void
    {
        $entityCopy = new SimpleEntityCopy();

        $object = new stdClass();
        $object->number = 5;
        $object->text = 'string';

        $actualCopy = $entityCopy->copy($object);

        self::assertNotSame($object, $actualCopy);
        self::assertEquals(clone $object, $actualCopy);

        $array = ['state' => $object];

        $actualCopy = $entityCopy->copy($array);

        self::assertNotSame($array['state'], $actualCopy['state']);
        self::assertEquals(clone $array['state'], $actualCopy['state']);
    }
}
