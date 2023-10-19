<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Form\Storage\DataStorageInterface;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;

final class DataControl implements DataControlInterface
{
    private const KEY_INITIALIZE_ENTITY = '__INITIALIZE__';

    public function __construct(private readonly DataStorageInterface $formData)
    {
    }

    public function getInitializeEntity(): mixed
    {
        return $this->formData->get(new StepKey(self::KEY_INITIALIZE_ENTITY));
    }

    public function getEntity(): mixed
    {
        try {
            $entity = $this->formData->getLast();
        } catch (KeysNotFoundInStorageException) {
            $entity = $this->getInitializeEntity();
        }

        return $entity;
    }

    public function hasStepEntity(StepKey $key): bool
    {
        return $this->formData->has($key);
    }

    public function getStepEntity(StepKey $key): mixed
    {
        if (!$this->hasStepEntity($key)) {
            throw new EntityNotFoundException($key);
        }

        return $this->formData->get($key);
    }

    public function start(mixed $entity): void
    {
        $this->formData->put(new StepKey(self::KEY_INITIALIZE_ENTITY), $entity);
    }

    public function handle(Step $step, mixed $entity): void
    {
        $this->formData->forgetAfter($step->key); // TODO: forget after only when steps is dynamic and step don't has interface
        $this->formData->put($step->key, $entity);
    }
}
