<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;

interface SessionStorageInterface
{
    /**
     * Returns current session key. Throws an exception when cannot read session key from the storage.
     * Returns null if there is no started form.
     *
     * @throws ReadSessionKeyException
     */
    public function getCurrent(): ?string;

    /**
     * Save current session key to the storage.
     */
    public function setCurrent(string $sessionKey): void;
}
