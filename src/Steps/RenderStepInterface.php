<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps;

use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

interface RenderStepInterface extends StepInterface
{
    /**
     * Returns a template definition with template name and data that will be placed to it.
     *
     * @param StepsCollection<Step> $steps
     */
    public function getTemplateDefinition(mixed $entity, StepsCollection $steps): TemplateDefinition;
}
