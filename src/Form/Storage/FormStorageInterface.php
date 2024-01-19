<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

interface FormStorageInterface
{
    public const DEFAULT_SESSION_KEY = '__MAIN__';

    /**
     * Sets current session key for the form.
     */
    public function setCurrentSession(string $sessionKey): void;

    /**
     * Returns data from the form storage by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Sets data to the form storage.
     */
    public function put(string $key, mixed $data): void;

    /**
     * Removes all form data from the storage.
     */
    public function clear(): void;
}
