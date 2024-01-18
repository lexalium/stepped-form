<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Form\Storage\SessionStorageInterface;

final class InMemorySessionStorage implements SessionStorageInterface
{
    private ?string $sessionKey = null;

    public function get(string $key): ?string
    {
        return $this->sessionKey;
    }

    public function put(string $key, string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }
}
