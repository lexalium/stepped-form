<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\State;

use Lexal\SteppedForm\Data\FormDataInterface;
use Lexal\SteppedForm\Data\StepControlInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\CurrentStepNotFoundException;
use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class FormState implements FormStateInterface
{
    public function __construct(
        private FormDataInterface $formData,
        private StepControlInterface $stepControl,
    ) {
    }

    /**
     * @throws AlreadyStartedException
     * @throws NoStepsAddedException
     * @throws StepNotFoundException
     */
    public function initialize(mixed $entity, StepsCollection $steps): void
    {
        try {
            $current = $this->stepControl->getCurrent();

            throw new AlreadyStartedException($steps->get($current));
        } catch (CurrentStepNotFoundException) {
            // nothing to do
        }

        $step = $steps->first();

        $this->finish();
        $this->formData->set($step->getKey(), $entity);
        $this->stepControl->setCurrent($step->getKey());
    }

    public function handle(string $key, mixed $entity, ?Step $next = null): void
    {
        $this->formData->forgetAfter($key);
        $this->formData->set($key, $entity);

        if ($next !== null) {
            $this->stepControl->setCurrent($next->getKey());
        }
    }

    public function getEntity(?string $key = null): mixed
    {
        return $key === null ? $this->formData->getLast() : $this->formData->get($key);
    }

    public function finish(): void
    {
        $this->formData->finish();
        $this->stepControl->reset();
    }
}
