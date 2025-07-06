<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\ChangeSet;

use DateTime;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSetTypeInterface;
use Lexal\SteppedForm\Form\ChangeSet\ObjectTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\SimpleTypeChangeSet;
use Lexal\SteppedForm\Tests\CreateObjectTrait;
use Lexal\SteppedForm\Tests\EntityEnum;
use Lexal\SteppedForm\Tests\SimpleEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ObjectTypeChangeSetTest extends TestCase
{
    use CreateObjectTrait;

    /**
     * @param array<string, ChangeSetTypeInterface> $changeSet
     */
    #[DataProvider('isEmptyDataProvider')]
    public function testIsEmpty(array $changeSet, bool $expected): void
    {
        $changeSet = new ObjectTypeChangeSet($changeSet, new stdClass());

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
     * @param array<string, ChangeSetTypeInterface> $changeSet
     */
    #[DataProvider('reflectDataProvider')]
    public function testReflect(mixed $reflectOn, array $changeSet, object $object, mixed $expected): void
    {
        $changeSet = new ObjectTypeChangeSet($changeSet, $object);

        $reflected = $changeSet->reflect($reflectOn);

        self::assertEquals($expected, $reflected);
        self::assertNotSame($object, $reflected);
        self::assertNotSame($reflectOn, $reflected);
    }

    /**
     * @return iterable<string, array{0: object, 1: array<string, ChangeSetTypeInterface>, 2: object, 3: object}>
     */
    public static function reflectDataProvider(): iterable
    {
        yield 'reflect on object with change set' => [
            self::createObject(['name' => 'name', 'type' => 'type', 'color' => 'red']),
            ['name' => new SimpleTypeChangeSet('replaced'), 'type' => new SimpleTypeChangeSet('rename')],
            self::createObject(['name' => 'replaced', 'type' => 'rename', 'color' => 'blue']),
            self::createObject(['name' => 'replaced', 'type' => 'rename', 'color' => 'red']),
        ];

        yield 'reflect on object with dynamic properties' => [
            self::createObject(['name' => 'name', 'type' => 'type']),
            ['name' => new SimpleTypeChangeSet('replaced'), 'color' => new SimpleTypeChangeSet('red')],
            self::createObject(['name' => 'replaced', 'color' => 'red']),
            self::createObject(['name' => 'replaced', 'type' => 'type', 'color' => 'red']),
        ];

        $expected = new SimpleEntity(5);
        $expected->name = 'replaced';

        yield 'reflect on object with static properties' => [
            new SimpleEntity(),
            [
                'name' => new SimpleTypeChangeSet('replaced'),
                'price' => new SimpleTypeChangeSet(5),
                'color' => new SimpleTypeChangeSet('blue'),
                'random' => new SimpleTypeChangeSet('value'),
            ],
            new SimpleEntity(12),
            $expected,
        ];

        $date = new DateTime('2024-04-05');
        $expected = new DateTime('2024-04-06');

        yield 'reflect on internal class' => [
            $date,
            ['timezone' => new SimpleTypeChangeSet('Europe/Tallinn')],
            $expected,
            $expected,
        ];
    }

    public function testReflectOnEnum(): void
    {
        $changeSet = new ObjectTypeChangeSet(
            [
                'name' => new SimpleTypeChangeSet('Canceled'),
                'value' => new SimpleTypeChangeSet('Canceled'),
            ],
            EntityEnum::Canceled,
        );

        $reflected = $changeSet->reflect(EntityEnum::Active);

        self::assertEquals(EntityEnum::Canceled, $reflected);
    }

    public function testReflectOnNotObject(): void
    {
        $object = new stdClass();
        $object->name = 'name';

        $changeSet = new ObjectTypeChangeSet(['name' => new SimpleTypeChangeSet('replaced')], $object);

        self::assertSame(18, $changeSet->reflect(18));
    }

    public function testReflectOnObjectWithoutChangeSet(): void
    {
        $object = new stdClass();
        $object->name = 'name';

        $changeSet = new ObjectTypeChangeSet([], $object);

        self::assertSame($object, $changeSet->reflect($object));
    }
}
