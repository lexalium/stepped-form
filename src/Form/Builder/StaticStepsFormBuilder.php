<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Steps;

final class StaticStepsFormBuilder implements FormBuilderInterface
{
    public function __construct(private readonly Steps $steps)
    {
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public function build(object $entity): Steps
    {
        return $this->steps;
    }
}
