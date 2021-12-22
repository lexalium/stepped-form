<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

class KeysNotFoundInStorageException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('There are no data saved in the storage.');
    }
}
