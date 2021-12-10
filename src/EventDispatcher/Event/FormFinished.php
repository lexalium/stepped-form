<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

class FormFinished
{
    public function __construct(public mixed $data)
    {
    }
}
