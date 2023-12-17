<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

interface RenderStepInterface extends StepInterface
{
    /**
     * Returns a template definition with template name and data that will be passed to it.
     *
     * @param Steps<Step> $steps
     */
    public function getTemplateDefinition(mixed $entity, Steps $steps): TemplateDefinition;
}
