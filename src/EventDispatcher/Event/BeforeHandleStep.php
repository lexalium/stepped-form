<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

use Lexal\SteppedForm\Step\Step;

final class BeforeHandleStep
{
    public function __construct(private mixed $data, public readonly mixed $entity, public readonly Step $step)
    {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
