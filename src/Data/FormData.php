<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

use Lexal\SteppedForm\Data\Storage\StorageInterface;

class FormData implements FormDataInterface
{
    private string $key;

    public function __construct(
        private StorageInterface $storage,
        string $namespace,
    ) {
        $this->key = "{$namespace}.steps";
    }

    public function set(string $key, mixed $data): FormDataInterface
    {
        $this->storage->put($this->getKey($key), $data);

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->storage->get($this->getKey($key));
    }

    public function getLast(): mixed
    {
        $keys = array_keys($this->storage->get($this->key, []));

        if (!$keys) {
            return null;
        }

        return $this->storage->get($this->getKey(array_pop($keys)));
    }

    public function has(string $key): bool
    {
        return $this->storage->has($this->getKey($key));
    }

    public function forget(string $key): FormDataInterface
    {
        $this->storage->forget($this->getKey($key));

        return $this;
    }

    public function forgetAfter(string $key): FormDataInterface
    {
        $keys = array_keys($this->storage->get($this->key, []));
        $index = array_search($key, $keys, true);

        if ($index === false) {
            return $this;
        }

        foreach (array_slice($keys, $index) as $keyToForget) {
            $this->forget($keyToForget);
        }

        return $this;
    }

    public function finish(): FormDataInterface
    {
        $this->storage->forget($this->key);

        return $this;
    }

    private function getKey(string $key): string
    {
        return "{$this->key}.{$key}";
    }
}
