<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Form\Storage\NullSessionStorage;
use Lexal\SteppedForm\Form\Storage\SessionStorageInterface;

final class SessionControl implements SessionControlInterface
{
    private const STORAGE_KEY = '__CURRENT_SESSION_KEY__';

    public function __construct(private readonly SessionStorageInterface $storage = new NullSessionStorage())
    {
    }

    public function getCurrent(): string
    {
        return $this->storage->get(self::STORAGE_KEY) ?? self::DEFAULT_SESSION_KEY;
    }

    public function setCurrent(string $sessionKey): void
    {
        $this->storage->put(self::STORAGE_KEY, $sessionKey);
    }
}
