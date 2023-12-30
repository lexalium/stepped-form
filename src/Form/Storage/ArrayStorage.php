<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;

final class ArrayStorage implements StorageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly SessionStorageInterface $sessionStorage)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function has(string $key): bool
    {
        return isset($this->data[$this->sessionStorage->getCurrent()][$key]);
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$this->sessionStorage->getCurrent()][$key] ?? $default;
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function put(string $key, mixed $data): void
    {
        $this->data[$this->sessionStorage->getCurrent()][$key] = $data;
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function clear(): void
    {
        unset($this->data[$this->sessionStorage->getCurrent()]);
    }
}
