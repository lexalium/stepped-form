<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher;

use Lexal\SteppedForm\Exception\EventDispatcherException;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * @throws EventDispatcherException
     */
    public function dispatch(object $event): object;
}
