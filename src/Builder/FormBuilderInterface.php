<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Builder;

use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

interface FormBuilderInterface
{
    /**
     * Build a StepsCollection by the form entity
     *
     * @return StepsCollection<Step>
     */
    public function build(mixed $entity): StepsCollection;
}
