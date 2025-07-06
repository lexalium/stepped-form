<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

/**
 * @template TEntity of object
 * @template-extends StepInterface<TEntity>
 */
interface RenderStepInterface extends StepInterface
{
    /**
     * Returns a template definition with template name and data that will be passed to it.
     *
     * @param TEntity&object $entity
     */
    public function getTemplateDefinition(object $entity, Steps $steps): TemplateDefinition;
}
