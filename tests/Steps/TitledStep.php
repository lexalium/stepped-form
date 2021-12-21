<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Steps\TitleStepInterface;

class TitledStep extends RenderStep implements TitleStepInterface
{
    public function __construct(private string $title = 'test', string $template = 'test', mixed $handleReturn = null)
    {
        parent::__construct($template, $handleReturn);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
