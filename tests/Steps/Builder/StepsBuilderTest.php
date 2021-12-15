<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps\Builder;

use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Builder\StepsBuilder;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Tests\Steps\RenderStep;
use Lexal\SteppedForm\Tests\Steps\SimpleStep;
use PHPUnit\Framework\TestCase;

class StepsBuilderTest extends TestCase
{
    public function testAdd(): void
    {
        $builder = new StepsBuilder();

        $builder->add('key', new SimpleStep());
        $builder->add('key2', new RenderStep());

        $expected = new StepsCollection([new Step('key', new SimpleStep()), new Step('key2', new RenderStep())]);

        $this->assertEquals($expected, $builder->get());
    }

    public function testAddAfter(): void
    {
        $builder = new StepsBuilder();

        $builder->add('key', new SimpleStep());
        $builder->add('key2', new RenderStep());

        $builder->addAfter('key', 'key4', new RenderStep());

        $expected = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key4', new RenderStep()),
            new Step('key2', new RenderStep()),
        ]);

        $this->assertEquals($expected, $builder->get());
    }

    public function testAddAfterNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $builder = new StepsBuilder();

        $builder->addAfter('key', 'key4', new RenderStep());
    }

    public function testAddBefore(): void
    {
        $builder = new StepsBuilder();

        $builder->add('key', new SimpleStep());
        $builder->add('key2', new RenderStep());
        $builder->add('key3', new RenderStep());

        $builder->addBefore('key2', 'key4', new RenderStep());

        $expected = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key4', new RenderStep()),
            new Step('key2', new RenderStep()),
            new Step('key3', new RenderStep()),
        ]);

        $this->assertEquals($expected, $builder->get());
    }

    public function testAddBeforeNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $builder = new StepsBuilder();

        $builder->addBefore('key', 'key4', new RenderStep());
    }

    public function testMerge(): void
    {
        $builder = new StepsBuilder();

        $builder->add('key', new SimpleStep());
        $builder->add('key2', new RenderStep());

        $builder->merge(
            new StepsCollection([
                new Step('key', new SimpleStep()),
                new Step('key4', new RenderStep()),
            ]),
        );

        $expected = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new RenderStep()),
            new Step('key4', new RenderStep()),
        ]);

        $this->assertEquals($expected, $builder->get());
    }

    public function testRemove(): void
    {
        $builder = new StepsBuilder();

        $builder->add('key', new SimpleStep());
        $builder->add('key2', new RenderStep());

        $builder->remove('key2');

        $expected = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $this->assertEquals($expected, $builder->get());
    }
}
