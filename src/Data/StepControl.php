<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;

class StepControl implements StepControlInterface
{
    public function __construct(
        private StorageInterface $storage,
        private string $namespace,
    ) {
    }

    public function setCurrent(string $key): StepControlInterface
    {
        $this->storage->put($this->getKey(), $key);

        return $this;
    }

    public function getCurrent(): string
    {
        $current = $this->storage->get($this->getKey());

        if ($current === null) {
            throw new CurrentStepNotFoundException();
        }

        return (string)$current;
    }

    public function hasCurrent(): bool
    {
        $current = $this->storage->get($this->getKey());

        return $current !== null;
    }

    public function reset(): StepControlInterface
    {
        $this->storage->forget($this->getKey());

        return $this;
    }

    private function getKey(): string
    {
        return "{$this->namespace}.current-step";
    }
}
