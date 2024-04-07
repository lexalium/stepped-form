<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\ObjectDefinition;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;

final class ObjectDefinitionTest extends TestCase
{
    public function testGetReflectionForStdObject(): void
    {
        $object = $this->createStdObject();
        $reflectionObject = ObjectDefinition::getReflectionObject($object);

        self::assertNotSame($reflectionObject, ObjectDefinition::getReflectionObject($object));
    }

    public function testGetReflectionForCustomObject(): void
    {
        $object = new SimpleEntity();
        $reflectionObject = ObjectDefinition::getReflectionObject($object);

        self::assertSame($reflectionObject, ObjectDefinition::getReflectionObject($object));
    }

    public function testGetPropertiesOfStdObject(): void
    {
        $object = $this->createStdObject();
        $properties = ObjectDefinition::getPropertiesByObject($object);

        self::assertEquals(['name'], array_keys($properties));

        $object->color = 'red';
        $properties = ObjectDefinition::getPropertiesByObject($object);

        self::assertEquals(['name', 'color'], array_keys($properties));
    }

    public function testGetPropertiesOfCustomObject(): void
    {
        $object = new SimpleEntity();
        $properties = ObjectDefinition::getPropertiesByObject($object);

        self::assertEquals(['name', 'price'], array_keys($properties));
    }

    private function createStdObject(): stdClass
    {
        $object = new stdClass();

        $object->name = 'name';

        return $object;
    }
}
