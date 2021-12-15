<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher\Event;

use Lexal\SteppedForm\Steps\Collection\Step;

class BeforeHandleStep
{
    public function __construct(
        public mixed $data,
        public mixed $entity,
        public Step $step,
    ) {
    }
}
