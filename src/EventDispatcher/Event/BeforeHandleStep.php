<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

use Lexal\SteppedForm\Steps\Collection\Step;

class BeforeHandleStep
{
    public function __construct(
        private mixed $data,
        private mixed $entity,
        private Step $step,
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }

    public function getStep(): Step
    {
        return $this->step;
    }
}
