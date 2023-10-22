<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Step;

use Lexal\SteppedForm\Step\RenderStepInterface;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Step\TemplateDefinition;

final class RenderStep extends SimpleStep implements RenderStepInterface
{
    public function __construct(private readonly string $template = 'test', mixed $handleReturn = null)
    {
        parent::__construct($handleReturn);
    }

    public function getTemplateDefinition(mixed $entity, Steps $steps): TemplateDefinition
    {
        return new TemplateDefinition($this->template, [$entity]);
    }
}
