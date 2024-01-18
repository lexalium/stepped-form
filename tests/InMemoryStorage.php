<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;
use Lexal\SteppedForm\Form\SessionControl;
use Lexal\SteppedForm\Form\SessionControlInterface;
use Lexal\SteppedForm\Form\Storage\StorageInterface;

final class InMemoryStorage implements StorageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly SessionControlInterface $sessionControl = new SessionControl(new InMemorySessionStorage()),
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function has(string $key): bool
    {
        return isset($this->data[$this->sessionControl->getCurrent()][$key]);
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$this->sessionControl->getCurrent()][$key] ?? $default;
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function put(string $key, mixed $data): void
    {
        $this->data[$this->sessionControl->getCurrent()][$key] = $data;
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function clear(): void
    {
        unset($this->data[$this->sessionControl->getCurrent()]);
    }
}
