<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Entity;

class TemplateDefinition
{
    /**
     * @param array<string|int, mixed> $data
     */
    public function __construct(
        private string $template,
        private array $data = [],
    ) {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
