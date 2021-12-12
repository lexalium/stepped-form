<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Builder;

use Lexal\SteppedForm\Steps\Collection\StepsCollection;

interface FormBuilderInterface
{
    public function build(mixed $entity): StepsCollection;
}
