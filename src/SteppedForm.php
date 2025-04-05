<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\EventDispatcherException;
use Lexal\SteppedForm\Exception\StepNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Form\DataControlInterface;
use Lexal\SteppedForm\Form\StepControlInterface;
use Lexal\SteppedForm\Form\Storage\FormStorageInterface;
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
        private readonly FormBuilderInterface $builder,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->steps = new Steps();
    }

    public function getEntity(): object
    {
        $this->stepControl->throwIfNotStarted();

        return $this->dataControl->getEntity();
    }

    /**
     * @inheritDoc
     *
     * @throws StepNotSubmittedException
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     */
    public function start(object $entity, string $session = FormStorageInterface::DEFAULT_SESSION_KEY): ?StepKey
    {
        $this->dataControl->initialize($entity, $session);

        $this->stepControl->throwIfAlreadyStarted();

        $this->build($entity);

        return $this->prepareRenderStepKey($this->steps->first());
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

    public function handle(StepKey $key, mixed $data): ?StepKey
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

        $this->dataControl->cancel();
    }

    /**
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws StepNotSubmittedException
     */
    private function handleStep(Step $step, mixed $data): ?StepKey
    {
        $entity = $this->getHandleStepEntity($step);

        try {
            $event = $this->dispatcher->dispatch(new BeforeHandleStep($data, $entity, $step));

            $entity = $step->step->handle($entity, $event->getData());

            $this->dataControl->handle($step, $entity, $this->builder->isDynamic());
            $this->rebuild($entity);

            return $this->prepareRenderStepKey($this->steps->next($step->key), $event->getData());
        } catch (SteppedFormErrorsException $exception) {
            $exception->renderable = $this->steps->currentOrPreviousRenderable($step)?->key;

            throw $exception;
        }
    }

    private function build(object $entity): void
    {
        if (!$this->built) {
            $this->steps = $this->builder->build($entity);
            $this->built = true;
        }
    }

    private function rebuild(object $entity): void
    {
        if ($this->builder->isDynamic()) {
            $this->built = false;
            $this->build($entity);
        }
    }

    /**
     * @throws StepNotSubmittedException
     * @throws EntityNotFoundException
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     */
    private function prepareRenderStepKey(?Step $step, mixed $data = null): ?StepKey
    {
        if ($step === null) {
            $this->finish();

            return null;
        }

        if ($step->step instanceof RenderStepInterface) {
            $this->stepControl->setCurrent($step->key);

            return $step->key;
        }

        return $this->handleStep($step, $data);
    }

    /**
     * @throws StepNotSubmittedException
     * @throws EventDispatcherException
     * @throws StepNotFoundException
     */
    private function finish(): void
    {
        foreach ($this->steps as $step) {
            if (!$step->isSubmitted()) {
                throw StepNotSubmittedException::finish(
                    $step->key,
                    $this->steps->currentOrPreviousRenderable($step)?->key,
                );
            }
        }

        $this->dispatcher->dispatch(new FormFinished($this->dataControl->getEntity()));

        $this->dataControl->cancel();
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getCurrentOrPreviousStepEntity(Step $step): object
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
    private function getHandleStepEntity(Step $step): object
    {
        $previous = $this->steps->previous($step->key);

        if ($previous === null) {
            $entity = $this->dataControl->getInitializeEntity();
        } else {
            $entity = $this->getStepEntity($previous);
        }

        return EntityCopy::copy($entity);
    }

    /**
     * @throws StepNotFoundException
     * @throws EntityNotFoundException
     */
    private function getStepEntity(Step $step): object
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
     * @throws StepNotSubmittedException
     */
    private function throwIfPreviousNotSubmitted(Step $step): void
    {
        $previous = $this->steps->previous($step->key);

        if ($previous !== null && !$previous->isSubmitted()) {
            throw StepNotSubmittedException::previous(
                $previous->key,
                $this->steps->currentOrPreviousRenderable($previous)?->key,
            );
        }
    }
}
