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
use function array_unique;
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
    private array $steps = [];

    /**
     * @var string[]
     */
    private array $keys = [];

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

        $this->keys = array_unique($this->keys);
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
        return $this->steps[$key->value] ?? throw new StepNotFoundException($key);
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
    public function next(StepKey $key): ?Step
    {
        $index = $this->getIndex($key);

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
    public function previous(StepKey $key): ?Step
    {
        $index = $this->getIndex($key);

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
    public function currentOrPreviousRenderable(Step $step): ?Step
    {
        if ($step->step instanceof RenderStepInterface) {
            return $step;
        }

        $previous = $this->previous($step->key);

        return $previous === null ? null : $this->currentOrPreviousRenderable($previous);
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
    private function getIndex(StepKey $key): int
    {
        $index = array_search($key->value, $this->keys, true);

        if ($index === false) {
            throw new StepNotFoundException($key);
        }

        return (int)$index;
    }
}
