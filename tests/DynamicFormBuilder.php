<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Tests\Step\RenderStep;

final class DynamicFormBuilder implements FormBuilderInterface
{
    public function __construct(private readonly mixed $handleReturn)
    {
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function build(mixed $entity): Steps
    {
        $step1 = new Step(new StepKey('key1'), new RenderStep(handleReturn: $this->handleReturn));
        $step2 = new Step(new StepKey('key2'), new RenderStep());
        $step3 = new Step(new StepKey('key3'), new RenderStep());

        return isset($entity['rebuild']) ? new Steps([$step1, $step2, $step3]) : new Steps([$step1, $step3]);
    }
}
