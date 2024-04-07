<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\ChangeSet;

use Lexal\SteppedForm\Form\ChangeSet\SimpleTypeChangeSet;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SimpleTypeChangeSetTest extends TestCase
{
    public function testIsEmpty(): void
    {
        $changeSet = new SimpleTypeChangeSet(15);

        self::assertFalse($changeSet->isEmpty());
    }

    public function testReflectOnScalar(): void
    {
        $changeSet = new SimpleTypeChangeSet(15);

        self::assertEquals(15, $changeSet->reflect(18));
    }

    public function testReflectOnObject(): void
    {
        $object = new stdClass();
        $object->namge = 'name';

        $changeSet = new SimpleTypeChangeSet($object);

        $reflected = $changeSet->reflect(18);

        self::assertEquals($object, $reflected);
        self::assertNotSame($object, $reflected);
    }
}
