<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Entity;

class TemplateDefinition
{
    public function __construct(
        private string $template,
        private array $data = [],
    ) {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
