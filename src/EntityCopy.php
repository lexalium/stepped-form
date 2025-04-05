<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use ReflectionObject;
use stdClass;

use function array_key_exists;
use function is_array;
use function is_numeric;
use function is_object;

final class EntityCopy
{
    public static function copy(mixed $value, mixed ...$replace): mixed
    {
        return match (true) {
            is_array($value) => self::copyArray($value, ...$replace),
            is_object($value) => self::copyObject($value, ...$replace),
            default => empty($replace) ? $value : $replace[0],
        };
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     */
    private static function copyArray(array $array, mixed ...$replace): array
    {
        $replace = empty($replace) ? [] : $replace[0];

        foreach ($array as $key => $value) {
            $array[$key] = isset($replace[$key]) ? self::copy($value, $replace[$key]) : self::copy($value);
        }

        return $array;
    }

    private static function copyObject(object $object, mixed ...$replace): ?object
    {
        if (!empty($replace)) {
            if ($replace[0] === null) {
                return null;
            }

            $replace = (array)$replace[0];
        }

        $reflectionObject = ObjectDefinition::getReflectionObject($object);

        if ($reflectionObject->isEnum() || ($reflectionObject->isInternal() && !$reflectionObject->isCloneable())) {
            return $object;
        }

        $newObject = self::cloneObject($reflectionObject, $object);

        foreach (ObjectDefinition::getProperties($reflectionObject) as $property) {
            if ($property->isReadOnly() && $property->isInitialized($newObject)) {
                continue;
            }

            $value = isset($replace[$property->name]) || array_key_exists($property->name, $replace)
                ? self::copy($property->getValue($object), $replace[$property->name])
                : self::copy($property->getValue($object));

            unset($replace[$property->name]);

            $property->setValue($newObject, $value);
        }

        return self::addMissedDynamicProperties($newObject, $replace);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private static function addMissedDynamicProperties(object $object, array $properties): object
    {
        if (!$object instanceof stdClass) {
            return $object;
        }

        foreach ($properties as $property => $value) {
            if (!is_numeric($property)) {
                $object->{$property} = self::copy($value);
            }
        }

        return $object;
    }

    private static function cloneObject(ReflectionObject $reflectionObject, object $object): object
    {
        return match (true) {
            $reflectionObject->hasMethod('__clone') && $reflectionObject->isCloneable(),
            $reflectionObject->isInternal() && $reflectionObject->isCloneable() => clone $object,
            default => $reflectionObject->newInstanceWithoutConstructor(),
        };
    }
}
