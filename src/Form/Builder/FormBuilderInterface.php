<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\Steps;

interface FormBuilderInterface
{
    /**
     * Returns true if steps can be added depending on entity, else - false.
     */
    public function isDynamic(): bool;

    /**
     * Builds a Steps collection by the form entity.
     *
     * @return Steps<Step>
     */
    public function build(mixed $entity): Steps;
}
