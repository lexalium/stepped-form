<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Form\Storage\StorageInterface;

final class InMemoryStorage implements StorageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function get(string $key, string $session, mixed $default = null): mixed
    {
        return $this->data[$session][$key] ?? $default;
    }

    public function put(string $key, string $session, mixed $data): void
    {
        $this->data[$session][$key] = $data;
    }

    public function clear(string $session): void
    {
        unset($this->data[$session]);
    }
}
