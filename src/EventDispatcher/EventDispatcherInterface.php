<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\EventDispatcher;

use Lexal\SteppedForm\Exception\EventDispatcherException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * @throws EventDispatcherException
     * @throws SteppedFormErrorsException
     */
    public function dispatch(object $event): object;
}
