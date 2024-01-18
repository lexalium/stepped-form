<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;

interface SessionStorageInterface
{
    /**
     * Returns session key from the storage. Returns null if there is no saved session key.
     * Throws an exception when cannot read session key from the storage.
     *
     * @throws ReadSessionKeyException
     */
    public function get(string $key): ?string;

    /**
     * Saves session key into the storage.
     */
    public function put(string $key, string $sessionKey): void;
}
