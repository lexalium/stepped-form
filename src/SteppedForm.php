<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\EntityCopy\EntityCopyInterface;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\EventDispatcherException;
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
     * @throws StepNotFoundException
     */
    public function start(mixed $entity): ?Step
    {
        $this->stepControl->throwIfAlreadyStarted();

        $this->build($entity);

        $first = $this->steps->first();

        $this->storage->clear();
        $this->dataControl->start($entity);

        return $this->prepareRenderStep($first);
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

        return $step->step->getTemplateDefinition($this->getCurrentOrPreviousStepEntity($step), $this->steps);
    }

    public function handle(StepKey $key, mixed $data): ?Step
    {
        $this->stepControl->throwIfNotStarted();

        $this->build($this->dataControl->getEntity());
        $step = $this->steps->get($key);

        $this->throwIfPreviousNotSubmitted($step);

        return $this->handleStep($step, $data);
    }

    public function cancel(): void
    {
        $this->stepControl->throwIfNotStarted();

        $this->storage->clear();
    }

    /**
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws StepIsNotSubmittedException
     */
    private function handleStep(Step $step, mixed $data): ?Step
    {
        $entity = $this->getHandleStepEntity($step);

        try {
            /** @var BeforeHandleStep $event */
            $event = $this->dispatcher->dispatch(new BeforeHandleStep($data, $entity, $step));

            $entity = $step->step->handle($entity, $event->getData());

            $this->dataControl->handle($step, $entity, $this->builder->isDynamic());
            $this->rebuild($entity);

            return $this->prepareRenderStep($this->steps->next($step->key), $event->getData());
        } catch (SteppedFormErrorsException $exception) {
            $exception->renderable = $this->steps->currentOrPreviousRenderable($step)?->key;

            throw $exception;
        }
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
        if ($this->builder->isDynamic()) {
            $this->built = false;
            $this->build($entity);
        }
    }

    /**
     * @throws StepIsNotSubmittedException
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

        if ($step->step instanceof RenderStepInterface) {
            $this->stepControl->setCurrent($step->key);

            return $step;
        }

        return $this->handleStep($step, $data);
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
                throw StepIsNotSubmittedException::finish(
                    $step->key,
                    $this->steps->currentOrPreviousRenderable($step)?->key,
                );
            }
        }

        $this->dispatcher->dispatch(new FormFinished($this->dataControl->getEntity()));

        $this->storage->clear();
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getCurrentOrPreviousStepEntity(Step $step): mixed
    {
        if (!$this->dataControl->hasStepEntity($step->key)) {
            $previous = $this->steps->previous($step->key);

            if ($previous === null) {
                return $this->dataControl->getInitializeEntity();
            }

            $step = $previous;
        }

        return $this->getStepEntity($step);
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getHandleStepEntity(Step $step): mixed
    {
        $previous = $this->steps->previous($step->key);

        if ($previous === null) {
            $entity = $this->dataControl->getInitializeEntity();
        } else {
            $entity = $this->getStepEntity($previous);
        }

        return $this->entityCopy->copy($entity);
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getStepEntity(Step $step): mixed
    {
        try {
            return $this->dataControl->getStepEntity($step->key);
        } catch (EntityNotFoundException $exception) {
            $exception->renderable = $this->steps->currentOrPreviousRenderable($step)?->key;

            throw $exception;
        }
    }

    /**
     * @throws StepNotFoundException
     * @throws StepIsNotSubmittedException
     */
    private function throwIfPreviousNotSubmitted(Step $step): void
    {
        $previous = $this->steps->previous($step->key);

        if ($previous !== null && !$previous->isSubmitted()) {
            throw StepIsNotSubmittedException::previous(
                $previous->key,
                $this->steps->currentOrPreviousRenderable($previous)?->key,
            );
        }
    }
}
