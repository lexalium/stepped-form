<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Storage;

use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSet;
use Lexal\SteppedForm\Step\StepKey;
use RuntimeException;

use function array_keys;
use function array_pop;
use function array_search;
use function array_slice;
use function count;
use function get_debug_type;
use function sprintf;

/**
 * @template TEntity of object
 *
 * @template-implements DataStorageInterface<TEntity>
 */
final class DataStorage implements DataStorageInterface
{
    private const STORAGE_KEY = '__STEPS__';
    private const KEY_INITIALIZE_ENTITY = '__INITIALIZE__';

    public function __construct(private readonly FormStorageInterface $storage)
    {
    }

    public function has(StepKey $key): bool
    {
        return isset($this->getData()[$key->value]);
    }

    public function getInitializeEntity(): object
    {
        $entity = $this->storage->get(self::KEY_INITIALIZE_ENTITY);

        if ($entity === null) {
            throw new RuntimeException(
                sprintf('%s method can be called only within stepped-form context.', __METHOD__),
            );
        }

        return $entity;
    }

    public function get(StepKey $key): object|null
    {
        return $this->getData()[$key->value] ?? null;
    }

    public function getLast(): object
    {
        $keys = $this->keys();

        $lastKey = array_pop($keys);

        if ($lastKey === null) {
            throw new KeysNotFoundInStorageException();
        }

        $entity = $this->get(new StepKey($lastKey));

        if ($entity === null) {
            throw new KeysNotFoundInStorageException();
        }

        return $entity;
    }

    public function initialize(object $entity, string $session): void
    {
        $this->storage->setCurrentSession($session);
        $this->storage->put(self::KEY_INITIALIZE_ENTITY, $entity);
    }

    public function put(StepKey $key, object $entity): void
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

    public function clear(): void
    {
        $this->storage->clear();
    }

    /**
     * @return array<string, TEntity&object>
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
     * @param array<string, TEntity&object> $data
     */
    private function checkAvailabilityToPut(string $key, object $entity, array $data): void
    {
        $keys = $this->keys();
        $index = $this->getIndex($key);

        if ($index === null) {
            $index = count($keys);
        }

        if (!isset($keys[$index - 1], $data[$keys[$index - 1]])) {
            return;
        }

        $previous = $data[$keys[$index - 1]];

        if ($entity::class !== $previous::class) {
            throw new RuntimeException(
                sprintf(
                    'Entities should have the same type between steps. Expected: %s. Given: %s.',
                    get_debug_type($entity),
                    get_debug_type($previous),
                ),
            );
        }
    }

    /**
     * @param array<string, TEntity&object> $data
     *
     * @return array<string, TEntity&object>
     */
    private function reflect(string $key, object $entity, array $data): array
    {
        $index = $this->getIndex($key);

        if ($index === null || !isset($data[$key])) {
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
