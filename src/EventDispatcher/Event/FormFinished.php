<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

class FormFinished
{
    public function __construct(private mixed $entity)
    {
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }
}
