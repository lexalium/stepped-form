<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\ChangeSet;

use function is_array;

final class ArrayTypeChangeSet implements ChangeSetTypeInterface
{
    /**
     * @param array<string|int, ChangeSetTypeInterface> $changeSet
     */
    public function __construct(private readonly array $changeSet)
    {
    }

    public function isEmpty(): bool
    {
        return empty($this->changeSet);
    }

    /**
     * @template T of mixed
     *
     * @param array<string|int, mixed>|T $entity
     *
     * @return array<string|int, mixed>|T
     */
    public function reflect(mixed $entity): mixed
    {
        if (is_array($entity)) {
            foreach ($this->changeSet as $key => $item) {
                $entity[$key] = $item->reflect($entity[$key] ?? null);
            }
        }

        return $entity;
    }
}
