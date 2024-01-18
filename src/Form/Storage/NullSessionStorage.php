<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

final class NullSessionStorage implements SessionStorageInterface
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function put(string $key, string $sessionKey): void
    {
        // nothing to save
    }
}
