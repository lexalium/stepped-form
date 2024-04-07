<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EntityCopy;

/**
 * @deprecated 3.0.1 Passing custom entity copy is deprecated
 */
interface EntityCopyInterface
{
    /**
     * Returns a full copy of the given entity.
     */
    public function copy(mixed $entity): mixed;
}
