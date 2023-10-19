<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Step\StepKey;

use function array_keys;
use function array_pop;
use function array_search;
use function array_slice;

final class DataStorage implements DataStorageInterface
{
    private const STORAGE_KEY = '__STEPS__';

    public function __construct(private readonly StorageInterface $storage)
    {
    }

    public function has(StepKey $key): bool
    {
        return isset($this->getData()[$key->value]);
    }

    public function get(StepKey $key): mixed
    {
        return $this->getData()[$key->value] ?? null;
    }

    public function getLast(): mixed
    {
        $keys = $this->keys();

        if (!$keys) {
            throw new KeysNotFoundInStorageException();
        }

        return $this->get(new StepKey(array_pop($keys)));
    }

    public function put(StepKey $key, mixed $entity): void
    {
        $data = $this->getData();

        $data[$key->value] = $entity;

        $this->storage->put(self::STORAGE_KEY, $data);
    }

    public function forgetAfter(StepKey $key): void
    {
        $keys = $this->keys();

        $index = array_search($key->value, $keys, true);

        if ($index !== false) {
            $this->forget(...array_slice($keys, (int)$index));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getData(): array
    {
        return (array)$this->storage->get(self::STORAGE_KEY, []);
    }

    /**
     * @return string[]
     */
    private function keys(): array
    {
        return array_keys($this->getData());
    }

    private function forget(string ...$keys): void
    {
        $data = $this->getData();

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        $this->storage->put(self::STORAGE_KEY, $data);
    }
}
