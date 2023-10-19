<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Traversable;

use function array_search;
use function count;
use function reset;

/**
 * @template-implements IteratorAggregate<Step>
 */
final class Steps implements Countable, IteratorAggregate
{
    /**
     * @var array<string, Step>
     */
    private array $steps;

    /**
     * @var string[]
     */
    private array $keys;

    /**
     * @param Step[] $steps
     */
    public function __construct(array $steps = [])
    {
        foreach ($steps as $step) {
            if ($step instanceof Step) {
                $this->steps[$step->key->value] = $step;
                $this->keys[] = $step->key->value;
            }
        }
    }

    public function has(StepKey $key): bool
    {
        return isset($this->steps[$key->value]);
    }

    /**
     * @throws StepNotFoundException
     */
    public function get(StepKey $key): Step
    {
        if ($this->has($key)) {
            return $this->steps[$key->value];
        }

        throw new StepNotFoundException($key);
    }

    /**
     * @throws NoStepsAddedException
     */
    public function first(): Step
    {
        $first = reset($this->steps);

        if ($first === false) {
            throw new NoStepsAddedException();
        }

        return $first;
    }

    /**
     * @throws StepNotFoundException
     */
    public function next(Step $step): ?Step
    {
        $index = $this->getIndex($step);

        $index++;

        if (!isset($this->keys[$index])) {
            return null;
        }

        $nextKey = $this->keys[$index];

        return $this->steps[$nextKey] ?? null;
    }

    /**
     * @throws StepNotFoundException
     */
    public function previous(Step $step): ?Step
    {
        $index = $this->getIndex($step);

        $index--;

        if ($index < 0 || !isset($this->keys[$index])) {
            return null;
        }

        $previousKey = $this->keys[$index];

        return $this->steps[$previousKey] ?? null;
    }

    /**
     * @throws StepNotFoundException
     */
    public function previousRenderable(Step $step): ?Step
    {
        $previous = $this->previous($step);

        if ($previous === null) {
            return null;
        }

        return $previous->step instanceof RenderStepInterface ? $previous : $this->previousRenderable($previous);
    }

    public function count(): int
    {
        return count($this->steps);
    }

    /**
     * @return Traversable<string, Step>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->steps);
    }

    /**
     * @throws StepNotFoundException
     */
    private function getIndex(Step $step): int
    {
        $index = array_search($step->key->value, $this->keys, true);

        if ($index === false) {
            throw new StepNotFoundException($step->key);
        }

        return (int)$index;
    }
}
