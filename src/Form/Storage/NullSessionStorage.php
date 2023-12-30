<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\SteppedFormInterface;

final class NullSessionStorage implements SessionStorageInterface
{
    public function getCurrent(): ?string
    {
        return SteppedFormInterface::DEFAULT_SESSION_KEY;
    }

    public function setCurrent(string $sessionKey): void
    {
        // nothing to save
    }
}
