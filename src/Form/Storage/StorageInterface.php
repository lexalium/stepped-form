<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

interface StorageInterface
{
    /**
     * Returns data from the storage by key.
     */
    public function get(string $key, string $sessionKey, mixed $default = null): mixed;

    /**
     * Sets data to the storage.
     */
    public function put(string $key, string $sessionKey, mixed $data): void;

    /**
     * Removes all form data from the storage.
     */
    public function clear(string $sessionKey): void;
}
