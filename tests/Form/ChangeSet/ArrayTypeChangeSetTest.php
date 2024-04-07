<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\ChangeSet;

use Lexal\SteppedForm\Form\ChangeSet\ArrayTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSetTypeInterface;
use Lexal\SteppedForm\Form\ChangeSet\SimpleTypeChangeSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArrayTypeChangeSetTest extends TestCase
{
    /**
     * @param array<string, ChangeSetTypeInterface> $changeSet
     */
    #[DataProvider('isEmptyDataProvider')]
    public function testIsEmpty(array $changeSet, bool $expected): void
    {
        $changeSet = new ArrayTypeChangeSet($changeSet);

        self::assertEquals($expected, $changeSet->isEmpty());
    }

    /**
     * @return iterable<string, array{0: array<string, ChangeSetTypeInterface>, 1: bool}>
     */
    public static function isEmptyDataProvider(): iterable
    {
        yield 'is empty' => [[], true];
        yield 'is not empty' => [['name' => new SimpleTypeChangeSet('rename')], false];
    }

    /**
     * @param array<string, mixed> $reflectOn
     * @param array<string, mixed> $expected
     */
    #[DataProvider('reflectOnArrayDataProvider')]
    public function testReflectOnArray(array $reflectOn, array $expected): void
    {
        $changeSet = new ArrayTypeChangeSet([
            'name' => new SimpleTypeChangeSet('name'),
            'new' => new SimpleTypeChangeSet(24),
        ]);

        self::assertEquals($expected, $changeSet->reflect($reflectOn));
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public static function reflectOnArrayDataProvider(): iterable
    {
        yield 'reflect on array' => [
            ['name' => 'before', 'color' => 'red'],
            ['name' => 'name', 'new' => 24, 'color' => 'red'],
        ];

        yield 'reflect on empty array' => [[], ['name' => 'name', 'new' => 24]];
    }

    public function testReflectIsNotArray(): void
    {
        $changeSet = new ArrayTypeChangeSet(['name' => new SimpleTypeChangeSet('name')]);

        self::assertEquals(18, $changeSet->reflect(18));
    }
}
