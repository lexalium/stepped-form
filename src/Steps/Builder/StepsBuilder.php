<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;

class StepsBuilder implements StepsBuilderInterface
{
    private const DEFAULT_INDEX = 0;

    /**
     * @var Step[]
     */
    private array $steps = [];

    public function add(string $key, StepInterface $step): self
    {
        $this->steps[$key] = new Step($key, $step);

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

        return $this->addToIndex($index - 1, $key, $step);
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
            array_slice($this->steps, 0, $index, true),
            [$key => new Step($key, $step)],
            array_slice($this->steps, $index, null, true)
        );

        return $this;
    }
}
