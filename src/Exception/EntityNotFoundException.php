<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Exception;

use function sprintf;

class EntityNotFoundException extends SteppedFormException
{
    public function __construct(private string $key)
    {
        parent::__construct(sprintf('There is no data for the given [%s] step.', $this->key));
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
