<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

final class ReadSessionKeyException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('Unable to get current form session key.');
    }
}
