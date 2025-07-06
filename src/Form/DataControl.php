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
    public function __construct(private readonly DataStorageInterface $storage)
    {
    }

    public function getInitializeEntity(): object
    {
        return $this->storage->getInitializeEntity();
    }

    public function getEntity(): object
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

    public function getStepEntity(StepKey $key): object
    {
        $entity = $this->storage->get($key);

        if ($entity === null) {
            throw new EntityNotFoundException($key);
        }

        return $entity;
    }

    public function initialize(object $entity, string $session): void
    {
        $this->storage->initialize($entity, $session);
    }

    public function handle(Step $step, object $entity, bool $isDynamicForm): void
    {
        $isForgetAfter = $step->step instanceof StepBehaviourInterface && $step->step->forgetDataAfterCurrent($entity);

        if ($isForgetAfter || ($isDynamicForm && !$step->step instanceof StepBehaviourInterface)) {
            $this->storage->forgetAfter($step->key);
        }

        $this->storage->put($step->key, $entity);
    }

    public function cancel(): void
    {
        $this->storage->clear();
    }
}
