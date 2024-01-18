<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Form\DataControlInterface;
use Lexal\SteppedForm\Form\StepControlInterface;
use Lexal\SteppedForm\Step\LazyStep;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;

use function array_keys;
use function array_merge;
use function array_replace;
use function array_search;
use function array_slice;
use function iterator_to_array;

final class StepsBuilder implements StepsBuilderInterface
{
    /**
     * @var Step[]
     */
    private array $steps = [];

    public function __construct(
        private readonly StepControlInterface $stepControl,
        private readonly DataControlInterface $dataControl,
    ) {
    }

    public function add(string $key, StepInterface $step): self
    {
        $this->steps[$key] = $this->createStep(new StepKey($key), $step);

        return $this;
    }

    /**
     * @throws StepNotFoundException
     */
    public function addAfter(string $after, string $key, StepInterface $step): self
    {
        $index = $this->getIndex($after);

        return $this->addToIndex($index + 1, $key, $step);
    }

    /**
     * @throws StepNotFoundException
     */
    public function addBefore(string $before, string $key, StepInterface $step): self
    {
        $index = $this->getIndex($before);

        return $this->addToIndex($index, $key, $step);
    }

    public function merge(Steps $steps): self
    {
        $this->steps = array_merge($this->steps, iterator_to_array($steps));

        return $this;
    }

    public function remove(string $key): self
    {
        unset($this->steps[$key]);

        return $this;
    }

    public function get(): Steps
    {
        $steps = new Steps($this->steps);

        $this->steps = [];

        return $steps;
    }

    /**
     * @throws StepNotFoundException
     */
    private function getIndex(string $key): int
    {
        $index = array_search($key, array_keys($this->steps), true);

        if ($index === false) {
            throw new StepNotFoundException(new StepKey($key));
        }

        return $index;
    }

    private function addToIndex(int $index, string $key, StepInterface $step): self
    {
        $this->steps = array_replace(
            array_slice($this->steps, 0, $index, true),
            [$key => $this->createStep(new StepKey($key), $step)],
            $this->steps,
        );

        return $this;
    }

    private function createStep(StepKey $key, StepInterface $step): Step
    {
        return new LazyStep(
            $key,
            $step,
            fn (): bool => $this->stepControl->getCurrent() === $key->value,
            fn (): bool => $this->dataControl->hasStepEntity($key),
        );
    }
}
