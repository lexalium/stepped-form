<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Form\Storage\SessionKeyStorageInterface;

final class InMemorySessionKeyStorage implements SessionKeyStorageInterface
{
    private ?string $sessionKey = null;

    public function get(string $key): ?string
    {
        return $this->sessionKey;
    }

    public function put(string $key, string $session): void
    {
        $this->sessionKey = $session;
    }
}
