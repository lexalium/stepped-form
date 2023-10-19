<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\Steps;

final class StaticFormBuilder implements FormBuilderInterface
{
    /**
     * @var Steps<Step>|null
     */
    private ?Steps $steps = null;

    public function __construct(private readonly FormBuilderInterface $builder)
    {
    }

    public function build(mixed $entity): Steps
    {
        if ($this->steps === null) {
            $this->steps = $this->builder->build($entity);
        }

        return $this->steps;
    }
}
