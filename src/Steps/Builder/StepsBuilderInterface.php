<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;

interface StepsBuilderInterface
{
    /**
     * Adds a new step to the collection
     */
    public function add(string $key, StepInterface $step): self;

    /**
     * Adds a new step after given one
     *
     * @throws StepNotFoundException
     */
    public function addAfter(string $after, string $key, StepInterface $step): self;

    /**
     * Adds a new step before given one
     *
     * @throws StepNotFoundException
     */
    public function addBefore(string $before, string $key, StepInterface $step): self;

    /**
     * Merge two collections to the one
     */
    public function merge(StepsCollection $collection): self;

    /**
     * Removes step from the collection
     */
    public function remove(string $key): self;

    /**
     * Builds a new steps collection
     */
    public function get(): StepsCollection;
}
