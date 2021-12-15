<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\Steps\RenderStepInterface;

class RenderStep implements RenderStepInterface
{
    public function __construct(private string $template = 'test')
    {
    }

    public function getTemplateDefinition(mixed $entity): TemplateDefinition
    {
        return new TemplateDefinition($this->template, [$entity]);
    }

    public function handle(mixed $entity, mixed $data): mixed
    {
        return $entity;
    }
}
