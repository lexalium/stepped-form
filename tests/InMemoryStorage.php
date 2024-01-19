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

    public function get(string $key, string $sessionKey, mixed $default = null): mixed
    {
        return $this->data[$sessionKey][$key] ?? $default;
    }

    public function put(string $key, string $sessionKey, mixed $data): void
    {
        $this->data[$sessionKey][$key] = $data;
    }

    public function clear(string $sessionKey): void
    {
        unset($this->data[$sessionKey]);
    }
}
