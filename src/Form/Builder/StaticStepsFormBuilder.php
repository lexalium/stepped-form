<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\Steps;

final class StaticStepsFormBuilder implements FormBuilderInterface
{
    /**
     * @param Steps<Step> $steps
     */
    public function __construct(private readonly Steps $steps)
    {
    }

    public function build(mixed $entity): Steps
    {
        return $this->steps;
    }
}
