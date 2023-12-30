<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Form\Storage\SessionStorageInterface;

final class InMemorySessionStorage implements SessionStorageInterface
{
    private ?string $sessionKey = null;

    public function getCurrent(): ?string
    {
        return $this->sessionKey;
    }

    public function setCurrent(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }
}
