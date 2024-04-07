<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use ReflectionObject;
use ReflectionProperty;
use stdClass;

final class ObjectDefinition
{
    /**
     * @var array<class-string, ReflectionObject>
     */
    private static array $reflectionObjects = [];

    /**
     * @var array<class-string, array<string, ReflectionProperty>>
     */
    private static array $reflectionProperties = [];

    public static function getReflectionObject(object $object): ReflectionObject
    {
        if ($object::class === stdClass::class) {
            return new ReflectionObject($object);
        }

        return self::$reflectionObjects[$object::class] ??= new ReflectionObject($object);
    }

    /**
     * @return array<string, ReflectionProperty>
     */
    public static function getPropertiesByObject(object $object): array
    {
        return self::getProperties(self::getReflectionObject($object));
    }

    /**
     * @return array<string, ReflectionProperty>
     */
    public static function getProperties(ReflectionObject $reflectedObject): array
    {
        $className = $reflectedObject->name;

        if (isset(self::$reflectionProperties[$className])) {
            return self::$reflectionProperties[$className];
        }

        $properties = [];

        do {
            foreach ($reflectedObject->getProperties() as $property) {
                if (!$property->isStatic()) {
                    $properties[$property->name] = $property;
                }
            }
        } while ($reflectedObject = $reflectedObject->getParentClass());

        return $className === stdClass::class ? $properties : self::$reflectionProperties[$className] = $properties;
    }
}
