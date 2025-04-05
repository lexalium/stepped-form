<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form\Builder;

use Lexal\SteppedForm\Step\Steps;

/**
 * @template TEntity of object
 */
interface FormBuilderInterface
{
    /**
     * Returns true if steps can be added depending on entity, else - false.
     */
    public function isDynamic(): bool;

    /**
     * Builds a Steps collection by the form entity.
     *
     * @param TEntity&object $entity
     */
    public function build(object $entity): Steps;
}
