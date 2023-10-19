<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepInterface;
use Lexal\SteppedForm\Step\Steps;

interface StepsBuilderInterface
{
    /**
     * Adds a new step to the collection.
     */
    public function add(string $key, StepInterface $step): self;

    /**
     * Adds a new step after given one.
     *
     * @throws StepNotFoundException
     */
    public function addAfter(string $after, string $key, StepInterface $step): self;

    /**
     * Adds a new step before given one.
     *
     * @throws StepNotFoundException
     */
    public function addBefore(string $before, string $key, StepInterface $step): self;

    /**
     * Merge two collections to the one.
     *
     * @param Steps<Step> $steps
     */
    public function merge(Steps $steps): self;

    /**
     * Removes step from the collection.
     */
    public function remove(string $key): self;

    /**
     * Builds a new steps collection.
     *
     * @return Steps<Step>
     */
    public function get(): Steps;
}
