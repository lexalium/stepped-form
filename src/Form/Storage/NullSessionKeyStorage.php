<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

final class NullSessionKeyStorage implements SessionKeyStorageInterface
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function put(string $key, string $session): void
    {
        // nothing to save
    }
}
