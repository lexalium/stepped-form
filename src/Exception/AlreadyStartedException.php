<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class AlreadyStartedException extends SteppedFormException
{
    public function __construct(public readonly string $currentKey)
    {
        parent::__construct('The form has already started.');
    }
}
