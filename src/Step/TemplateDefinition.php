<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Step;

final class TemplateDefinition
{
    /**
     * @param array<string|int, mixed> $data
     */
    public function __construct(public readonly string $template, public readonly array $data = [])
    {
    }
}
