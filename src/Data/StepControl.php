<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;

class StepControl implements StepControlInterface
{
    private const STORAGE_KEY = 'current-step';

    public function __construct(private StorageInterface $storage)
    {
    }

    public function setCurrent(string $key): StepControlInterface
    {
        $this->storage->put(self::STORAGE_KEY, $key);

        return $this;
    }

    public function getCurrent(): string
    {
        $current = $this->storage->get(self::STORAGE_KEY);

        if ($current === null) {
            throw new CurrentStepNotFoundException();
        }

        return (string)$current;
    }

    public function hasCurrent(): bool
    {
        $current = $this->storage->get(self::STORAGE_KEY);

        return $current !== null;
    }

    public function reset(): StepControlInterface
    {
        $this->storage->forget(self::STORAGE_KEY);

        return $this;
    }
}
