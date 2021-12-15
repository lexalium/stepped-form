<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps;

use Lexal\SteppedForm\Steps\TitleStepInterface;

class TitledStep extends RenderStep implements TitleStepInterface
{
    public function __construct(private $title = 'test', string $template = 'test')
    {
        parent::__construct($template);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
