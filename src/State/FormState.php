<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\State;

use Lexal\SteppedForm\Data\FormDataStorageInterface;
use Lexal\SteppedForm\Data\StepControlInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class FormState implements FormStateInterface
{
    public function __construct(
        private FormDataStorageInterface $formData,
        private StepControlInterface $stepControl,
    ) {
    }

    public function getEntity(): mixed
    {
        if (!$this->stepControl->hasCurrent()) {
            throw new FormIsNotStartedException();
        }

        return $this->formData->getLast();
    }

    public function getStepEntity(string $key): mixed
    {
        $entity = $this->formData->get($key);

        if ($entity === null) {
            throw new EntityNotFoundException($key);
        }

        return $entity;
    }

    public function initialize(mixed $entity, StepsCollection $steps): void
    {
        try {
            $current = $this->stepControl->getCurrent();

            try {
                $currentStep = $steps->get($current);
            } catch (StepNotFoundException) {
                $currentStep = null;
            }

            throw new AlreadyStartedException($current, $currentStep);
        } catch (CurrentStepNotFoundException) {
            // nothing to do
        }

        $step = $steps->first();

        $this->formData->put($step->getKey(), $entity);
        $this->stepControl->setCurrent($step->getKey());
    }

    public function handle(string $key, mixed $entity, ?Step $next = null): void
    {
        $this->formData->forgetAfter($key);
        $this->formData->put($key, $entity);

        if ($next !== null) {
            $this->stepControl->setCurrent($next->getKey());
        }
    }

    public function finish(): void
    {
        if (!$this->stepControl->hasCurrent()) {
            throw new FormIsNotStartedException();
        }

        $this->formData->clear();
        $this->stepControl->reset();
    }
}
