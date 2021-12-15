<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps;

interface TitleStepInterface extends RenderStepInterface
{
    /**
     * Returns a title of the step
     */
    public function getTitle(): string;
}
