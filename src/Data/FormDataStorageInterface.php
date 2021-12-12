<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;

interface FormDataStorageInterface extends StorageInterface
{
    /**
     * Returns a last saved data
     */
    public function getLast(): mixed;

    /**
     * Removes all steps data after given
     */
    public function forgetAfter(string $key): self;
}
