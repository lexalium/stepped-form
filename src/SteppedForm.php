<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\EntityCopy\EntityCopyInterface;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\EventDispatcherException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepHandleException;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Form\DataControlInterface;
use Lexal\SteppedForm\Form\StepControlInterface;
use Lexal\SteppedForm\Form\Storage\StorageInterface;
use Lexal\SteppedForm\Step\RenderStepInterface;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Step\TemplateDefinition;

final class SteppedForm implements SteppedFormInterface
{
    private Steps $steps;
    private bool $built = false;

    public function __construct(
        private readonly DataControlInterface $dataControl,
        private readonly StepControlInterface $stepControl,
        private readonly StorageInterface $storage,
        private readonly FormBuilderInterface $builder,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityCopyInterface $entityCopy,
    ) {
        $this->steps = new Steps();
    }

    public function getEntity(): mixed
    {
        $this->stepControl->throwIfNotStarted();

        return $this->dataControl->getEntity();
    }

    /**
     * @inheritDoc
     *
     * @throws StepIsNotSubmittedException
     * @throws EntityNotFoundException
     * @throws FormIsNotStartedException
     * @throws StepNotFoundException
     */
    public function start(mixed $entity): ?Step
    {
        $this->stepControl->throwIfAlreadyStarted(); // TODO: set first step as current or check by data control

        $this->storage->clear();
        $this->dataControl->start($entity);

        $this->build($entity);

        return $this->prepareRenderStep($this->steps->first());
    }

    public function render(StepKey $key): TemplateDefinition
    {
        $this->stepControl->throwIfNotStarted();

        $this->build($this->dataControl->getEntity());
        $step = $this->steps->get($key);

        $this->throwIfPreviousNotSubmitted($step);

        if (!$step->step instanceof RenderStepInterface) {
            throw new StepNotRenderableException($step->key);
        }

        $this->stepControl->setCurrent($key);

        return $step->step->getTemplateDefinition($this->getCurrentOrPreviousStepEntity($step), $this->steps);
    }

    public function handle(StepKey $key, mixed $data): ?Step
    {
        $this->stepControl->throwIfNotStarted();

        $this->build($this->dataControl->getEntity());
        $step = $this->steps->get($key);

        $this->throwIfPreviousNotSubmitted($step);

        try {
            $data = $this->handleStep($step, $data);

            return $this->prepareRenderStep($this->steps->next($step), $data);
        } catch (SteppedFormErrorsException $exception) {
            $exception->setPreviousStep($this->steps->previousRenderable($step));

            throw $exception;
        }
    }

    public function cancel(): void
    {
        $this->stepControl->throwIfNotStarted();

        $this->storage->clear();
    }

    /**
     * @throws EntityNotFoundException
     * @throws EventDispatcherException
     * @throws StepHandleException
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     */
    private function handleStep(Step $step, mixed $data): mixed
    {
        $entity = $this->getHandleStepEntity($step);

        /** @var BeforeHandleStep $event */
        $event = $this->dispatcher->dispatch(new BeforeHandleStep($data, $entity, $step));

        $entity = $step->step->handle($entity, $event->getData());

        $this->dataControl->handle($step, $entity);
        $this->rebuild($entity);

        return $event->getData();
    }

    /**
     * @throws StepIsNotSubmittedException
     * @throws EventDispatcherException
     * @throws StepNotFoundException
     */
    private function finish(): void
    {
        foreach ($this->steps as $step) {
            if (!$step->isSubmitted()) {
                throw StepIsNotSubmittedException::finish($step->key, $this->steps->previousRenderable($step));
            }
        }

        $this->dispatcher->dispatch(new FormFinished($this->dataControl->getEntity()));

        $this->storage->clear();
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
     * @throws StepIsNotSubmittedException
     * @throws FormIsNotStartedException
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     */
    private function prepareRenderStep(?Step $step, mixed $data = null): ?Step
    {
        if ($step === null) {
            $this->finish();

            return null;
        }

        return $step->step instanceof RenderStepInterface ? $step : $this->handle($step->key, $data);
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getCurrentOrPreviousStepEntity(Step $step): mixed
    {
        if (!$this->dataControl->hasStepEntity($step->key)) {
            $previous = $this->steps->previous($step);

            if ($previous === null) {
                return $this->dataControl->getInitializeEntity();
            }

            $step = $previous;
        }

        return $this->dataControl->getStepEntity($step->key);
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getHandleStepEntity(Step $step): mixed
    {
        $previous = $this->steps->previous($step);

        if ($previous === null) {
            $entity = $this->dataControl->getInitializeEntity();
        } else {
            $entity = $this->dataControl->getStepEntity($previous->key);
        }

        return $this->entityCopy->copy($entity);
    }

    /**
     * @throws StepNotFoundException
     * @throws StepIsNotSubmittedException
     */
    private function throwIfPreviousNotSubmitted(Step $step): void
    {
        $previous = $this->steps->previous($step);

        if ($previous !== null && !$previous->isSubmitted()) {
            throw StepIsNotSubmittedException::render($step->key, $this->steps->previousRenderable($step));
        }
    }
}
