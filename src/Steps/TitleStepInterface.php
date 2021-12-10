<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Steps;

interface TitleStepInterface
{
    /**
     * Returns a title of the step
     */
    public function getTitle(): string;
}
