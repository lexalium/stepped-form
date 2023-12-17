<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

interface StorageInterface
{
    /**
     * Checks if the storage contains data with the given key.
     */
    public function has(string $key): bool;

    /**
     * Returns data from the storage by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Sets data to the storage.
     */
    public function put(string $key, mixed $data): void;

    /**
     * Removes all form data from the storage.
     */
    public function clear(): void;
}
