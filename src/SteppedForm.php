<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\State\FormStateInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\RenderStepInterface;

class SteppedForm implements SteppedFormInterface
{
    private StepsCollection $steps;
    private bool $built = false;

    public function __construct(
        private FormStateInterface $formState,
        private FormBuilderInterface $builder,
        private EventDispatcherInterface $dispatcher,
    ) {
        $this->steps = new StepsCollection([]);
    }

    public function getEntity(): mixed
    {
        return $this->formState->getEntity();
    }

    /**
     * @inheritDoc
     *
     * @throws FormIsNotStartedException
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    public function start(mixed $entity): ?Step
    {
        $this->rebuild($entity);
        $this->formState->initialize($entity, $this->steps);

        $step = $this->steps->first();

        return $this->prepareRenderStep($step);
    }

    public function render(string $key): TemplateDefinition
    {
        $this->build($this->getEntity());
        $step = $this->steps->get($key)->getStep();

        if (!$step instanceof RenderStepInterface) {
            throw new StepNotRenderableException($key);
        }

        return $step->getTemplateDefinition($this->getCurrentOrPreviousStepEntity($key));
    }

    public function handle(string $key, mixed $data): ?Step
    {
        $this->build($this->getEntity());
        $step = $this->steps->get($key);

        $entity = $this->getHandleStepEntity($key);

        /** @var BeforeHandleStep $event */
        $event = $this->dispatcher->dispatch(new BeforeHandleStep($data, $entity, $step));

        $entity = $step->getStep()->handle($entity, $event->data);

        $this->rebuild($entity);
        $next = $this->steps->next($step->getKey());

        $this->formState->handle($step->getKey(), $entity, $next);

        if ($next === null) {
            $this->dispatcher->dispatch(new FormFinished($this->getEntity()));

            $this->cancel();

            return null;
        }

        return $this->prepareRenderStep($next, $event->data);
    }

    public function cancel(): void
    {
        $this->formState->finish();
    }

    private function build(mixed $entity): void
    {
        if (!$this->built) {
            $this->steps = $this->builder->build($entity);
            $this->built = true;
        }
    }

    private function rebuild(mixed $entity): void
    {
        $this->built = false;
        $this->build($entity);
    }

    /**
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws EntityNotFoundException
     * @throws FormIsNotStartedException
     */
    private function prepareRenderStep(Step $step, mixed $data = null): ?Step
    {
        if ($step->getStep() instanceof RenderStepInterface) {
            return $step;
        }

        $step = $this->handle($step->getKey(), $data);

        return $step !== null ? $this->prepareRenderStep($step, $data) : null;
    }

    /**
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     */
    private function getCurrentOrPreviousStepEntity(string $key): mixed
    {
        $entity = $this->formState->getStepEntity($key);

        if ($entity === null) {
            $entity = $this->getPreviousStepEntity($key);
        }

        return $entity;
    }

    /**
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     */
    private function getPreviousStepEntity(string $key): mixed
    {
        $previous = $this->steps->previous($key);

        return $previous !== null ? $this->formState->getStepEntity($previous->getKey()) : null;
    }

    /**
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     */
    private function getHandleStepEntity(string $key): mixed
    {
        $previous = $this->steps->previous($key);

        if ($previous !== null) {
            $key = $previous->getKey();
        }

        return $this->formState->getStepEntity($key);
    }
}
