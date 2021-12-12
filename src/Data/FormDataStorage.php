<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;

class FormDataStorage implements FormDataStorageInterface
{
    public function __construct(private StorageInterface $storage)
    {
    }

    public function put(string $key, mixed $data): FormDataStorageInterface
    {
        $this->storage->put($key, $data);

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage->get($key, $default);
    }

    public function keys(): array
    {
        return $this->storage->keys();
    }

    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }

    public function forget(string $key): FormDataStorageInterface
    {
        $this->storage->forget($key);

        return $this;
    }

    public function clear(): FormDataStorageInterface
    {
        $this->storage->clear();

        return $this;
    }

    public function getLast(): mixed
    {
        $keys = $this->keys();

        if (!$keys) {
            return null;
        }

        return $this->get(array_pop($keys));
    }

    public function forgetAfter(string $key): FormDataStorageInterface
    {
        $keys = $this->keys();

        $index = array_search($key, $keys, true);

        if ($index === false) {
            return $this;
        }

        foreach (array_slice($keys, $index) as $forgetKey) {
            $this->forget($forgetKey);
        }

        return $this;
    }
}
