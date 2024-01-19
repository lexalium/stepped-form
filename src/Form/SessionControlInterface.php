<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;

interface SessionControlInterface
{
    public const DEFAULT_SESSION_KEY = '__MAIN__';

    /**
     * Returns current session key. Throws an exception when cannot read session key from the storage.
     *
     * @throws ReadSessionKeyException
     */
    public function getCurrent(): string;

    /**
     * Save current session key to the storage.
     */
    public function setCurrent(string $sessionKey): void;
}
