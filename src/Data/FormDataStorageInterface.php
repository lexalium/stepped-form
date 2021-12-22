<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;
use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;

interface FormDataStorageInterface extends StorageInterface
{
    /**
     * Returns a last saved data.
     *
     * @throws KeysNotFoundInStorageException
     */
    public function getLast(): mixed;

    /**
     * Removes all steps data after given.
     */
    public function forgetAfter(string $key): self;
}
