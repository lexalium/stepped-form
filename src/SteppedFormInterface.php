<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\TemplateDefinition;

interface SteppedFormInterface
{
    /**
     * Returns a form data.
     *
     * @throws FormIsNotStartedException
     */
    public function getEntity(): mixed;

    /**
     * Starts a new form session and return a first Step.
     * If already started will throw AlreadyStartedException exception.
     *
     * @throws NoStepsAddedException
     * @throws AlreadyStartedException
     * @throws SteppedFormErrorsException
     */
    public function start(mixed $entity): ?Step;

    /**
     * Returns a Template Definition for given step.
     *
     * @throws StepNotFoundException
     * @throws StepNotRenderableException
     * @throws EntityNotFoundException
     * @throws FormIsNotStartedException
     * @throws StepIsNotSubmittedException
     */
    public function render(StepKey $key): TemplateDefinition;

    /**
     * Handles a form step and returns next step or null when there is no next step.
     *
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws EntityNotFoundException
     * @throws FormIsNotStartedException
     * @throws StepIsNotSubmittedException
     */
    public function handle(StepKey $key, mixed $data): ?Step;

    /**
     * Cancels current form session.
     *
     * @throws FormIsNotStartedException
     */
    public function cancel(): void;
}
