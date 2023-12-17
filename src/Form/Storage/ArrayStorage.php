<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

final class ArrayStorage implements StorageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function put(string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    public function clear(): void
    {
        $this->data = [];
    }
}
