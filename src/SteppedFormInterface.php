<?php

declare(strict_types=1);

namespace Lexal\SteppedForm;

use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Form\Storage\FormStorageInterface;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\TemplateDefinition;

interface SteppedFormInterface
{
    /**
     * Returns a form data.
     *
     * @throws FormNotStartedException
     */
    public function getEntity(): object;

    /**
     * Starts a new form session and return a first Step Key.
     * Throws AlreadyStartedException exception if it has already been started.
     *
     * @throws NoStepsAddedException
     * @throws AlreadyStartedException
     * @throws SteppedFormErrorsException
     */
    public function start(object $entity, string $session = FormStorageInterface::DEFAULT_SESSION_KEY): ?StepKey;

    /**
     * Returns a Template Definition for given step.
     *
     * @throws StepNotFoundException
     * @throws StepNotRenderableException
     * @throws EntityNotFoundException
     * @throws FormNotStartedException
     * @throws StepNotSubmittedException
     */
    public function render(StepKey $key): TemplateDefinition;

    /**
     * Handles a form step and returns next step key or null when there is no next step.
     *
     * @throws StepNotFoundException
     * @throws SteppedFormErrorsException
     * @throws EntityNotFoundException
     * @throws FormNotStartedException
     * @throws StepNotSubmittedException
     */
    public function handle(StepKey $key, mixed $data): ?StepKey;

    /**
     * Cancels current form session.
     *
     * @throws FormNotStartedException
     */
    public function cancel(): void;
}
