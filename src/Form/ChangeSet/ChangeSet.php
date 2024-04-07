<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\ChangeSet;

use Lexal\SteppedForm\ObjectDefinition;

use function is_array;
use function is_object;

final class ChangeSet
{
    /**
     * @param object|array<string, mixed> $current
     * @param ($current is object ? object : array<string, mixed>) $previous
     */
    public static function compute(object|array $current, object|array $previous): ChangeSetTypeInterface
    {
        return is_array($current)
            ? self::computeArraysChangeSet($current, $previous)[1]
            : self::computeObjectsChangeSet($current, $previous)[1];
    }

    /**
     * @template T of mixed
     *
     * @param T $current
     * @param T $previous
     *
     * @return array{0: bool, 1: ChangeSetTypeInterface}
     */
    private static function computeChangeSet(mixed $current, mixed $previous): array
    {
        return match (true) {
            is_array($current) && is_array($previous) => self::computeArraysChangeSet($current, $previous),
            is_object($current) && is_object($previous) => self::computeObjectsChangeSet($current, $previous),
            default => [$current !== $previous, new SimpleTypeChangeSet($current)],
        };
    }

    /**
     * @template TArray of array<string, mixed>
     *
     * @param TArray $current
     * @param TArray $previous
     *
     * @return array{0: bool, 1: ArrayTypeChangeSet, 2: array<string, ChangeSetTypeInterface>}
     */
    private static function computeArraysChangeSet(array $current, array $previous): array
    {
        $changeSet = [];

        foreach ($current as $key => $value) {
            [$isChanged, $updates] = self::computeChangeSet($value, $previous[$key] ?? null);

            if ($isChanged) {
                $changeSet[$key] = $updates;
            }
        }

        return [!empty($changeSet), new ArrayTypeChangeSet($changeSet), $changeSet];
    }

    /**
     * @param object $current
     * @param object $previous
     *
     * @return array{0: bool, 1: ObjectTypeChangeSet}
     */
    private static function computeObjectsChangeSet(object $current, object $previous): array
    {
        if ($current::class !== $previous::class) {
            return [false, new ObjectTypeChangeSet([], $current)];
        }

        [$isChanged, , $changeSet] = self::computeArraysChangeSet(
            self::objectToArray($current),
            self::objectToArray($previous),
        );

        return [$isChanged, new ObjectTypeChangeSet($changeSet, $current)];
    }

    /**
     * @return array<string, mixed>
     */
    private static function objectToArray(object $object): array
    {
        $data = [];

        foreach (ObjectDefinition::getPropertiesByObject($object) as $property) {
            if ($property->isInitialized($object)) {
                $data[$property->name] = $property->getValue($object);
            }
        }

        return $data;
    }
}
