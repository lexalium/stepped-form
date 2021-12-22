<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data\Storage;

interface StorageInterface
{
    /**
     * Sets data to the storage.
     */
    public function put(string $key, mixed $data): self;

    /**
     * Returns data from the storage by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Returns array of the keys added to the storage.
     *
     * @return string[]
     */
    public function keys(): array;

    /**
     * Checks if the storage contains data with the given key.
     */
    public function has(string $key): bool;

    /**
     * Removes data from the storage by the given key.
     */
    public function forget(string $key): self;

    /**
     * Removes all form data from the storage.
     */
    public function clear(): self;
}
