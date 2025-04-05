<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use stdClass;

trait CreateObjectTrait
{
    /**
     * @param array<string, mixed> $properties
     */
    private static function createObject(array $properties): object
    {
        $object = new stdClass();

        foreach ($properties as $name => $value) {
            $object->{$name} = $value;
        }

        return $object;
    }
}
