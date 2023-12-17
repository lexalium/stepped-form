<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Step\StepKey;

interface StepControlInterface
{
    /**
     * Returns current step key. null - if form is not started.
     */
    public function getCurrent(): ?string;

    /**
     * Save current step key to the storage.
     */
    public function setCurrent(StepKey $key): void;

    /**
     * Throws an exception when the form had already started.
     *
     * @throws AlreadyStartedException
     */
    public function throwIfAlreadyStarted(): void;

    /**
     * Throws an exception when the form hasn't stated yet.
     *
     * @throws FormIsNotStartedException
     */
    public function throwIfNotStarted(): void;
}
