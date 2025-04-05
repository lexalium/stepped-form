<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\ChangeSet;

use Lexal\SteppedForm\Form\ChangeSet\ArrayTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSetTypeInterface;
use Lexal\SteppedForm\Form\ChangeSet\ObjectTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\SimpleTypeChangeSet;
use Lexal\SteppedForm\Tests\CreateObjectTrait;
use Lexal\SteppedForm\Tests\SimpleEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ChangeSetTest extends TestCase
{
    use CreateObjectTrait;

    #[DataProvider('computeChangeSetDataProvider')]
    public function testComputeChangeSet(
        object $current,
        object $previous,
        ChangeSetTypeInterface $expected,
    ): void {
        $changeSet = ChangeSet::compute($current, $previous);

        self::assertEquals($expected, $changeSet);
    }

    /**
     * @return iterable<string, array{
     *     0: object,
     *     1: object,
     *     2: ChangeSetTypeInterface,
     *   }>
     */
    public static function computeChangeSetDataProvider(): iterable
    {
        $current = self::createObject([
            'id' => 5,
            'name' => 'test',
            'properties' => [
                ['name' => 'color', 'value' => 'red'],
                ['name' => 'brand', 'value' => 'brady'],
            ],
            'price' => self::createObject(['original' => 126, 'discount' => 14]),
            'rating' => 4.7,
        ]);

        yield 'compute for two objects without changes' => [$current, $current, new ObjectTypeChangeSet([], $current)];

        $current = self::createObject([
            'id' => 5,
            'name' => 'rename',
            'properties' => [
                ['name' => 'type', 'value' => 'vehicle'],
                ['name' => 'brand', 'value' => 'brady'],
                ['name' => 'color', 'value' => 'red'],
            ],
            'price' => self::createObject(['original' => 126, 'discount' => 18]),
            'rating' => 4.69,
            'created_at' => '2024-04-06',
            'change_type' => true,
        ]);

        $previous = self::createObject([
            'id' => 5,
            'name' => 'test',
            'properties' => [
                ['name' => 'color', 'value' => 'red'],
                ['name' => 'brand', 'value' => 'brady'],
            ],
            'price' => self::createObject(['original' => 126, 'discount' => 14]),
            'rating' => 4.7,
            'change_type' => 'string',
        ]);

        yield 'compute for two objects with changes' => [
            $current,
            $previous,
            new ObjectTypeChangeSet(
                [
                    'name' => new SimpleTypeChangeSet('rename'),
                    'properties' => new ArrayTypeChangeSet([
                        0 => new ArrayTypeChangeSet([
                            'name' => new SimpleTypeChangeSet('type'),
                            'value' => new SimpleTypeChangeSet('vehicle'),
                        ]),
                        2 => new SimpleTypeChangeSet(['name' => 'color', 'value' => 'red']),
                    ]),
                    'price' => new ObjectTypeChangeSet(
                        ['discount' => new SimpleTypeChangeSet(18)],
                        self::createObject(['original' => 126, 'discount' => 18]),
                    ),
                    'rating' => new SimpleTypeChangeSet(4.69),
                    'created_at' => new SimpleTypeChangeSet('2024-04-06'),
                    'change_type' => new SimpleTypeChangeSet(true),
                ],
                $current,
            ),
        ];

        $current = self::createObject(['object' => self::createObject(['name' => 'rename'])]);
        $previous = self::createObject(['object' => new SimpleEntity()]);

        yield 'compute for two different objects' => [$current, $previous, new ObjectTypeChangeSet([], $current)];

        $current = self::createObject(['id' => [8, 5]]);

        yield 'compute for objects with different types within one property (array)' => [
            $current,
            self::createObject(['id' => 8]),
            new ObjectTypeChangeSet(['id' => new SimpleTypeChangeSet([8, 5])], $current),
        ];

        $current = self::createObject(['id' => self::createObject(['name' => 'id'])]);

        yield 'compute for objects with different types within one property (object)' => [
            $current,
            self::createObject(['id' => 8]),
            new ObjectTypeChangeSet(['id' => new SimpleTypeChangeSet(self::createObject(['name' => 'id']))], $current),
        ];
    }
}
