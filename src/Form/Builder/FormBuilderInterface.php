<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\Steps;

interface FormBuilderInterface
{
    /**
     * Build a StepsCollection by the form entity.
     *
     * @return Steps<Step>
     */
    public function build(mixed $entity): Steps;
}
