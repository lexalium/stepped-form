<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

final class FormFinished
{
    public function __construct(public readonly mixed $entity)
    {
    }
}
