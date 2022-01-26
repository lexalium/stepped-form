<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Builder;

use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class StaticFormBuilder implements FormBuilderInterface
{
    /**
     * @var StepsCollection<Step>|null
     */
    private ?StepsCollection $collection = null;

    public function __construct(private FormBuilderInterface $builder)
    {
    }

    public function build(mixed $entity): StepsCollection
    {
        if ($this->collection === null) {
            $this->collection = $this->builder->build($entity);
        }

        return $this->collection;
    }
}
