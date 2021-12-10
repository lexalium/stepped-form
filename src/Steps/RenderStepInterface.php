<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps;

use Lexal\SteppedForm\Entity\TemplateDefinition;

interface RenderStepInterface extends StepInterface
{
    /**
     * Returns a template definition with template name and data that will be placed to it
     */
    public function getTemplateDefinition(mixed $entity): TemplateDefinition;
}
