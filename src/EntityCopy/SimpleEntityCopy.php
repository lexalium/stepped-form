<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EntityCopy;

use function is_array;
use function is_object;

final class SimpleEntityCopy implements EntityCopyInterface
{
    public function copy(mixed $entity): mixed
    {
        if (is_array($entity)) {
            $entity = $this->copyArray($entity);
        } elseif (is_object($entity)) {
            $entity = clone $entity;
        }

        return $entity;
    }

    /**
     * @param array<int|string, mixed> $entity
     * @return array<int|string, mixed>
     */
    private function copyArray(array $entity): array
    {
        $copied = [];

        foreach ($entity as $key => $value) {
            $copied[$key] = $this->copy($value);
        }

        return $copied;
    }
}
