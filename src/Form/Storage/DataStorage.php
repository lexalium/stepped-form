<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSet;
use Lexal\SteppedForm\Step\StepKey;

use function array_keys;
use function array_pop;
use function array_search;
use function array_slice;
use function is_array;
use function is_object;

final class DataStorage implements DataStorageInterface
{
    private const STORAGE_KEY = '__STEPS__';

    public function __construct(private readonly FormStorageInterface $storage)
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

        $this->checkAvailabilityToPut($key->value, $entity, $data);

        $data = $this->reflect($key->value, $entity, $data);
        $data[$key->value] = $entity;

        $this->storage->put(self::STORAGE_KEY, $data);
    }

    public function forgetAfter(StepKey $key): void
    {
        $keys = $this->keys();

        /** @var int|false $index */
        $index = array_search($key->value, $keys, true);

        if ($index !== false) {
            $this->forget(...array_slice($keys, $index + 1));
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

    /**
     * @param array<string, mixed> $data
     */
    private function checkAvailabilityToPut(string $key, mixed $entity, array $data): void
    {
        $keys = $this->keys();
        $index = $this->getIndex($key);

        if ($index === null || $index <= 0 || !isset($keys[$index - 1], $data[$keys[$index - 1]])) {
            return;
        }

        $previous = $data[$keys[$index - 1]];

        if (
            (is_array($entity) && is_array($previous))
            || (is_object($entity) && is_object($previous) && $entity::class === $previous::class)
        ) {
            return;
        }

        trigger_deprecation(
            'lexal/stepped-form',
            '3.1.0',
            'Entities should have the same type between steps. Only array or object allowed to use.',
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function reflect(string $key, mixed $entity, array $data): array
    {
        $index = $this->getIndex($key);

        if ($index === null && !isset($data[$key])) {
            return $data;
        }

        $changeSet = ChangeSet::compute($entity, $data[$key]);

        if ($changeSet->isEmpty()) {
            return $data;
        }

        foreach (array_slice($this->keys(), $index + 1) as $reflectKey) {
            if (isset($data[$reflectKey])) {
                $data[$reflectKey] = $changeSet->reflect($data[$reflectKey]);
            }
        }

        return $data;
    }

    private function getIndex(string $key): ?int
    {
        $keys = $this->keys();

        /** @var int|false $index */
        $index = array_search($key, $keys, true);

        return $index === false ? null : $index;
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
