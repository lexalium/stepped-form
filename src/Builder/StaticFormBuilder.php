<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Builder;

use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class StaticFormBuilder implements FormBuilderInterface
{
    /**
     * @param StepsCollection<Step> $collection
     */
    public function __construct(private StepsCollection $collection)
    {
    }

    public function build(mixed $entity): StepsCollection
    {
        return $this->collection;
    }
}
