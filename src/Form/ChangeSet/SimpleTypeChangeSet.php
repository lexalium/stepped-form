<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\ChangeSet;

use Lexal\SteppedForm\EntityCopy;

final class SimpleTypeChangeSet implements ChangeSetTypeInterface
{
    public function __construct(private readonly mixed $changeSet)
    {
    }

    public function isEmpty(): bool
    {
        return false;
    }

    public function reflect(mixed $entity): mixed
    {
        return EntityCopy::copy($this->changeSet);
    }
}
