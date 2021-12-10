<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\EventDispatcher\Event\BeforeHandleStep;
use Lexal\SteppedForm\EventDispatcher\Event\FormFinished;
use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
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

    public function start(mixed $entity): ?Step
    {
		$this->formState->initialize($entity, $this->steps);
		$this->build($entity);

        $step = $this->steps->first();

        return $this->prepareRenderStep($step);
    }

    public function render(string $key): TemplateDefinition
    {
		$this->build($this->getEntity());
        $step = $this->steps->get($key);

        if (!$step->getStep() instanceof RenderStepInterface) {
            throw new StepNotRenderableException($key);
        }

        return $step->getStep()->getTemplateDefinition($this->getStepEntity($key));
    }

    public function handle(string $key, mixed $data): ?Step
    {
		$this->build($this->getEntity());
        $step = $this->steps->get($key);

		/** @var BeforeHandleStep $event */
        $event = $this->dispatcher->dispatch(new BeforeHandleStep($data, $step));

        $entity = $step->getStep()->handle($this->getStepEntity($key), $event->data);

        $this->build($entity);

        $next = $this->steps->next($step->getKey());

        $this->formState->handle($step->getKey(), $entity, $next);

        if ($next === null) {
            $this->dispatcher->dispatch(new FormFinished($this->getEntity()));

			$this->cancel();

            return null;
        }

        return $this->prepareRenderStep($next);
    }

    public function cancel(): void
    {
        $this->formState->finish();
    }

	private function build(mixed $entity): void
	{
		$this->steps = $this->builder->build($entity);
	}

    /**
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws EntityNotFoundException
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
    private function getStepEntity(string $key): mixed
    {
        $entity = $this->formState->getEntity($key);

        if ($entity === null) {
            $previous = $this->steps->previous($key);

            if ($previous === null) {
                throw new EntityNotFoundException($key);
            }

            $entity = $this->formState->getEntity($previous->getKey());
        }

        return $entity;
    }
}
