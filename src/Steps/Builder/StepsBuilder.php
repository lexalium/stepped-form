<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Builder;

use Closure;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\State\FormStateInterface;
use Lexal\SteppedForm\Steps\Collection\LazyStep;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;

use function array_merge;
use function array_keys;
use function array_replace;
use function array_search;
use function array_slice;
use function iterator_to_array;

class StepsBuilder implements StepsBuilderInterface
{
    private const DEFAULT_INDEX = 0;
    private const SLICE_OFFSET = 0;

    /**
     * @var Step[]
     */
    private array $steps = [];

    public function __construct(private FormStateInterface $formState)
    {
    }

    public function add(string $key, StepInterface $step): self
    {
        $this->steps[$key] = $this->createStep($key, $step);

        return $this;
    }

    /**
     * @throws StepNotFoundException
     */
    public function addAfter(string $after, string $key, StepInterface $step): self
    {
        if (!$this->has($after)) {
            throw new StepNotFoundException($after);
        }

        $index = $this->getIndex($after);

        return $this->addToIndex($index + 1, $key, $step);
    }

    /**
     * @throws StepNotFoundException
     */
    public function addBefore(string $before, string $key, StepInterface $step): self
    {
        if (!$this->has($before)) {
            throw new StepNotFoundException($before);
        }

        $index = $this->getIndex($before);

        return $this->addToIndex($index, $key, $step);
    }

    public function merge(StepsCollection $collection): self
    {
        $this->steps = array_merge($this->steps, iterator_to_array($collection));

        return $this;
    }

    public function remove(string $key): self
    {
        unset($this->steps[$key]);

        return $this;
    }

    public function get(): StepsCollection
    {
        $collection = new StepsCollection($this->steps);

        $this->steps = [];

        return $collection;
    }

    private function has(string $key): bool
    {
        return isset($this->steps[$key]);
    }

    private function getIndex(string $key): int
    {
        $index = array_search($key, array_keys($this->steps), true);

        return $index === false ? self::DEFAULT_INDEX : $index;
    }

    private function addToIndex(int $index, string $key, StepInterface $step): self
    {
        $this->steps = array_replace(
            array_slice($this->steps, self::SLICE_OFFSET, $index, true),
            [$key => $this->createStep($key, $step)],
            array_slice($this->steps, $index, null, true),
        );

        return $this;
    }

    private function createStep(string $key, StepInterface $step): Step
    {
        return new LazyStep(
            $key,
            $step,
            $this->getIsCurrentCallback($key),
            $this->getIsSubmittedCallback($key),
        );
    }

    private function getIsCurrentCallback(string $key): Closure
    {
        return function () use ($key) {
            return $this->formState->getCurrentStep() === $key;
        };
    }

    private function getIsSubmittedCallback(string $key): Closure
    {
        return function () use ($key) {
            return $this->formState->hasStepEntity($key);
        };
    }
}
