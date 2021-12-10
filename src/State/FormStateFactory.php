<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\State;

use Lexal\SteppedForm\Data\FormData;
use Lexal\SteppedForm\Data\StepControl;
use Lexal\SteppedForm\Data\Storage\StorageInterface;

final class FormStateFactory
{
    public static function create(StorageInterface $storage, string $namespace): FormStateInterface
    {
        return new FormState(
            new FormData($storage, $namespace),
            new StepControl($storage, $namespace),
        );
    }
}
