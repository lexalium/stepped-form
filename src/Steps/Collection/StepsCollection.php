<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Collection;

use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\TitleStepInterface;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class StepsCollection implements Countable, IteratorAggregate
{
    /**
     * @var Step[]
     */
    private array $steps;

    /**
     * @var string[]
     */
    private array $keys;

    /**
     * @param Step[] $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = array_filter($steps, static fn (mixed $step) => $step instanceof Step);

        $keys = array_map(static fn (Step $step) => $step->getKey(), $this->steps);
        $this->keys = array_combine($keys, $keys);
    }

    /**
     * @throws NoStepsAddedException
     */
    public function first(): Step
    {
        if ($this->count() === 0) {
            throw new NoStepsAddedException();
        }

        return $this->steps[0];
    }

    /**
     * @throws StepNotFoundException
     */
    public function next(string $key): ?Step
    {
        $index = $this->getIndex($key);

        return $this->steps[++$index] ?? null;
    }

    /**
     * @throws StepNotFoundException
     */
    public function previous(string $key): ?Step
    {
        $index = $this->getIndex($key);

        return $this->steps[--$index] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->keys[$key]);
    }

    /**
     * @throws StepNotFoundException
     */
    public function get(string $key): Step
    {
        if (!$this->has($key)) {
            throw new StepNotFoundException($key);
        }

        return $this->steps[$key];
    }

    public function getTitled(): self
    {
        return new self(array_filter($this->steps, static function (Step $step) {
            return $step->getStep() instanceof TitleStepInterface;
        }));
    }

    public function count(): int
    {
        return count($this->steps);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_combine($this->keys, $this->steps));
    }

    /**
     * @throws StepNotFoundException
     */
    private function getIndex(string $key): int
    {
        $index = array_search($key, $this->keys, true);

        if ($index === false) {
            throw new StepNotFoundException($key);
        }

        return $index;
    }
}
