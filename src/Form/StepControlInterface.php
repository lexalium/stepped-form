<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Form;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Step\StepKey;

interface StepControlInterface
{
    /**
     * Returns current step key. null - if form has not been started.
     */
    public function getCurrent(): ?string;

    /**
     * Save current step key to the storage.
     */
    public function setCurrent(StepKey $key): void;

    /**
     * Throws an exception when the form had already been started.
     *
     * @throws AlreadyStartedException
     */
    public function throwIfAlreadyStarted(): void;

    /**
     * Throws an exception when the form hasn't stated yet.
     *
     * @throws FormNotStartedException
     */
    public function throwIfNotStarted(): void;
}
