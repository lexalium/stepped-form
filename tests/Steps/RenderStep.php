<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\Steps\RenderStepInterface;

class RenderStep extends SimpleStep implements RenderStepInterface
{
    public function __construct(private string $template = 'test', mixed $handleReturn = null)
    {
        parent::__construct($handleReturn);
    }

    public function getTemplateDefinition(mixed $entity): TemplateDefinition
    {
        return new TemplateDefinition($this->template, [$entity]);
    }
}
