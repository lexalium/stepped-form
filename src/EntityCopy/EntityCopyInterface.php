<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EntityCopy;

interface EntityCopyInterface
{
    /**
     * Returns a full copy of the given entity.
     */
    public function copy(mixed $entity): mixed;
}
