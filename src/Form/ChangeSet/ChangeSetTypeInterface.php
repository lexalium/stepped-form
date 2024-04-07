<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\ChangeSet;

interface ChangeSetTypeInterface
{
    /**
     * Checks if current change set is empty.
     */
    public function isEmpty(): bool;

    /**
     * Reflects current change set onto given entity.
     */
    public function reflect(mixed $entity): mixed;
}
