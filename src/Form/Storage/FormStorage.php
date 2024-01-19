<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\ReadSessionKeyException;

final class FormStorage implements FormStorageInterface
{
    private const STORAGE_KEY = '__CURRENT_SESSION_KEY__';

    public function __construct(
        private readonly StorageInterface $storage,
        private readonly SessionStorageInterface $sessionStorage = new NullSessionStorage(),
    ) {
    }

    public function setCurrentSession(string $sessionKey): void
    {
        $this->sessionStorage->put(self::STORAGE_KEY, $sessionKey);
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage->get($key, $this->getCurrentSession(), $default);
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function put(string $key, mixed $data): void
    {
        $this->storage->put($key, $this->getCurrentSession(), $data);
    }

    /**
     * @inheritDoc
     *
     * @throws ReadSessionKeyException
     */
    public function clear(): void
    {
        $this->storage->clear($this->getCurrentSession());
    }

    /**
     * @throws ReadSessionKeyException
     */
    private function getCurrentSession(): string
    {
        return $this->sessionStorage->get(self::STORAGE_KEY) ?? self::DEFAULT_SESSION_KEY;
    }
}
