<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data\Storage;

use function array_keys;

class ArrayStorage implements StorageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function put(string $key, mixed $data): StorageInterface
    {
        $this->data[$key] = $data;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function forget(string $key): StorageInterface
    {
        unset($this->data[$key]);

        return $this;
    }

    public function clear(): StorageInterface
    {
        $this->data = [];

        return $this;
    }
}
