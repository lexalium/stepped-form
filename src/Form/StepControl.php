<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Form\Storage\StorageInterface;
use Lexal\SteppedForm\Step\StepKey;

final class StepControl implements StepControlInterface
{
    private const STORAGE_KEY = '__CURRENT_STEP__';

    public function __construct(private readonly StorageInterface $storage)
    {
    }

    public function getCurrent(): ?string
    {
        return $this->storage->get(self::STORAGE_KEY);
    }

    public function setCurrent(StepKey $key): void
    {
        $this->storage->put(self::STORAGE_KEY, $key->value);
    }

    public function throwIfAlreadyStarted(): void
    {
        $current = $this->storage->get(self::STORAGE_KEY);

        if ($current !== null) {
            throw new AlreadyStartedException((string)$current);
        }
    }

    public function throwIfNotStarted(): void
    {
        $current = $this->storage->get(self::STORAGE_KEY);

        if ($current === null) {
            throw new FormIsNotStartedException();
        }
    }
}
