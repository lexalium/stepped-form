<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\ChangeSet;

use Lexal\SteppedForm\EntityCopy;
use Lexal\SteppedForm\ObjectDefinition;
use stdClass;

use function is_object;

final class ObjectTypeChangeSet implements ChangeSetTypeInterface
{
    /**
     * @param array<string, ChangeSetTypeInterface> $changeSet
     */
    public function __construct(private readonly array $changeSet, private readonly object $object)
    {
    }

    public function isEmpty(): bool
    {
        return empty($this->changeSet);
    }

    /**
     * @template T
     *
     * @param T $entity
     *
     * @return T
     */
    public function reflect(mixed $entity): mixed
    {
        if (is_object($entity) && !empty($this->changeSet)) {
            $entity = $this->canBeReplaced($entity)
                ? EntityCopy::copy($entity, $this->getPropertiesValues($entity))
                : EntityCopy::copy($this->object);
        }

        return $entity;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPropertiesValues(object $object): array
    {
        return $object instanceof stdClass
            ? $this->getDynamicPropertiesValues($object)
            : $this->getStaticPropertiesValues($object);
    }

    /**
     * @return array<string, mixed>
     */
    private function getDynamicPropertiesValues(stdClass $object): array
    {
        $properties = [];
        $objectProperties = ObjectDefinition::getPropertiesByObject($object);

        foreach ($this->changeSet as $property => $changeSet) {
            $value = null;

            if (isset($objectProperties[$property])) {
                $value = $objectProperties[$property]->getValue($object);
            }

            $properties[$property] = $changeSet->reflect($value);
        }

        return $properties;
    }

    /**
     * @return array<string, mixed>
     */
    private function getStaticPropertiesValues(object $object): array
    {
        $properties = [];

        foreach (ObjectDefinition::getPropertiesByObject($object) as $property) {
            if (isset($this->changeSet[$property->name])) {
                $properties[$property->name] = $this->changeSet[$property->name]->reflect($property->getValue($object));
            }
        }

        return $properties;
    }

    private function canBeReplaced(object $object): bool
    {
        if ($object instanceof stdClass) {
            return true;
        }

        $reflectionObject = ObjectDefinition::getReflectionObject($object);

        return !$reflectionObject->isInternal() && !$reflectionObject->isEnum();
    }
}
