<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Steps\Collection;

use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Tests\Steps\RenderStep;
use Lexal\SteppedForm\Tests\Steps\SimpleStep;
use Lexal\SteppedForm\Tests\Steps\TitledStep;
use PHPUnit\Framework\TestCase;

class StepsCollectionTest extends TestCase
{
    public function testFirst(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new SimpleStep()),
        ]);

        $expected = new Step('key', new SimpleStep());

        $this->assertEquals($expected, $collection->first());
    }

    public function testFirstNoStepsAddedException(): void
    {
        $this->expectExceptionObject(new NoStepsAddedException());

        $collection = new StepsCollection([]);

        $collection->first();
    }

    public function testNext(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new SimpleStep()),
        ]);

        $expected = new Step('key2', new SimpleStep());

        $this->assertEquals($expected, $collection->next('key'));
    }

    public function testNextIsNotExists(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $this->assertNull($collection->next('key'));
    }

    public function testNextStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $collection = new StepsCollection([]);

        $collection->next('key');
    }

    public function testPrevious(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new SimpleStep()),
        ]);

        $expected = new Step('key', new SimpleStep());

        $this->assertEquals($expected, $collection->previous('key2'));
    }

    public function testPreviousIsNotExists(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $this->assertNull($collection->previous('key'));
    }

    public function testPreviousStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $collection = new StepsCollection([]);

        $collection->previous('key');
    }

    public function testHas(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $this->assertTrue($collection->has('key'));
        $this->assertFalse($collection->has('key2'));
    }

    public function testGet(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
        ]);

        $expected = new Step('key', new SimpleStep());

        $this->assertEquals($expected, $collection->get('key'));
    }

    public function testGetStepNotFoundException(): void
    {
        $this->expectExceptionObject(new StepNotFoundException('key'));

        $collection = new StepsCollection([]);

        $collection->get('key');
    }

    public function testGetTitled(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new TitledStep()),
            new Step('key3', new SimpleStep()),
        ]);

        $expected = new StepsCollection([
            new Step('key2', new TitledStep()),
        ]);

        $this->assertEquals($expected, $collection->getTitled());
    }

    public function testCount(): void
    {
        $collection = new StepsCollection([
            new Step('key', new SimpleStep()),
            new Step('key2', new TitledStep()),
            new Step('key3', new SimpleStep()),
            new Step('key', new RenderStep()),
        ]);

        $this->assertCount(3, $collection);
    }
}
