<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\Storage\DataStorageInterface;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepBehaviourInterface;
use Lexal\SteppedForm\Step\StepKey;

final class DataControl implements DataControlInterface
{
    private const KEY_INITIALIZE_ENTITY = '__INITIALIZE__';

    public function __construct(private readonly DataStorageInterface $storage)
    {
    }

    public function getInitializeEntity(): mixed
    {
        return $this->storage->get(new StepKey(self::KEY_INITIALIZE_ENTITY));
    }

    public function getEntity(): mixed
    {
        try {
            $entity = $this->storage->getLast();
        } catch (KeysNotFoundInStorageException) {
            $entity = $this->getInitializeEntity();
        }

        return $entity;
    }

    public function hasStepEntity(StepKey $key): bool
    {
        return $this->storage->has($key);
    }

    public function getStepEntity(StepKey $key): mixed
    {
        if (!$this->hasStepEntity($key)) {
            throw new EntityNotFoundException($key);
        }

        return $this->storage->get($key);
    }

    public function start(mixed $entity): void
    {
        $this->storage->put(new StepKey(self::KEY_INITIALIZE_ENTITY), $entity);
    }

    public function handle(Step $step, mixed $entity, bool $isDynamicForm): void
    {
        $isForgetAfter = $step->step instanceof StepBehaviourInterface && $step->step->forgetDataAfterCurrent($entity);

        if ($isForgetAfter || ($isDynamicForm && !$step->step instanceof StepBehaviourInterface)) {
            $this->storage->forgetAfter($step->key);
        }

        $this->storage->put($step->key, $entity);
    }
}
