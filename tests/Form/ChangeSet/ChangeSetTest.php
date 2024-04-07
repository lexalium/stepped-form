<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests\Form\ChangeSet;

use Lexal\SteppedForm\Form\ChangeSet\ArrayTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\ChangeSetTypeInterface;
use Lexal\SteppedForm\Form\ChangeSet\ObjectTypeChangeSet;
use Lexal\SteppedForm\Form\ChangeSet\SimpleTypeChangeSet;
use Lexal\SteppedForm\Tests\SimpleEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ChangeSetTest extends TestCase
{
    /**
     * @param object|array<string, mixed> $current
     * @param object|array<string, mixed> $previous
     */
    #[DataProvider('computeChangeSetDataProvider')]
    public function testComputeChangeSet(
        object|array $current,
        object|array $previous,
        ChangeSetTypeInterface $expected,
    ): void {
        $changeSet = ChangeSet::compute($current, $previous);

        self::assertEquals($expected, $changeSet);
    }

    /**
     * @return iterable<string, array{
     *     0: object|array<string, mixed>,
     *     1: object|array<string, mixed>,
     *     2: ChangeSetTypeInterface,
     *   }>
     */
    public static function computeChangeSetDataProvider(): iterable
    {
        $current = [
            'id' => 5,
            'name' => 'test',
            'properties' => [
                ['color' => 'red'],
                ['brand' => 'brady'],
            ],
            'price' => [
                'original' => 126,
                'discount' => 14,
            ],
            'rating' => 4.7,
            'object' => self::createObject(['name' => 'test', 'nested' => self::createObject(['name' => 'nested'])]),
        ];

        yield 'compute for two arrays without changes' => [$current, $current, new ArrayTypeChangeSet([])];

        $current = [
            'id' => 5,
            'name' => 'rename',
            'properties' => [
                ['color' => 'red', 'condition' => 'new'],
                ['brand' => 'brady'],
                ['type' => 'vehicle'],
            ],
            'price' => [
                'original' => 126,
                'discount' => 18,
            ],
            'rating' => 4.69,
            'created_at' => '2024-04-06',
            'object' => self::createObject([
                'name' => 'test',
                'nested' => self::createObject(['name' => 'nested', 'color' => 'red']),
            ]),
            'new_object' => self::createObject(['name' => 'test']),
            'changed_type' => 'string',
        ];

        $previous = [
            'id' => 5,
            'name' => 'test',
            'properties' => [
                ['color' => 'red'],
                ['brand' => 'brady'],
            ],
            'price' => [
                'original' => 126,
                'discount' => 14,
            ],
            'rating' => 4.7,
            'object' => self::createObject(['name' => 'rename', 'nested' => self::createObject(['name' => 'nested'])]),
            'changed_type' => true,
        ];

        yield 'compute for two arrays with changes' => [
            $current,
            $previous,
            new ArrayTypeChangeSet([
                'name' => new SimpleTypeChangeSet('rename'),
                'properties' => new ArrayTypeChangeSet([
                    0 => new ArrayTypeChangeSet(['condition' => new SimpleTypeChangeSet('new')]),
                    2 => new SimpleTypeChangeSet(['type' => 'vehicle']),
                ]),
                'price' => new ArrayTypeChangeSet(['discount' => new SimpleTypeChangeSet(18)]),
                'rating' => new SimpleTypeChangeSet(4.69),
                'created_at' => new SimpleTypeChangeSet('2024-04-06'),
                'object' => new ObjectTypeChangeSet(
                    [
                        'name' => new SimpleTypeChangeSet('test'),
                        'nested' => new ObjectTypeChangeSet(
                            ['color' => new SimpleTypeChangeSet('red')],
                            self::createObject(['name' => 'nested', 'color' => 'red']),
                        ),
                    ],
                    self::createObject([
                        'name' => 'test',
                        'nested' => self::createObject(['name' => 'nested', 'color' => 'red']),
                    ]),
                ),
                'new_object' => new SimpleTypeChangeSet(self::createObject(['name' => 'test'])),
                'changed_type' => new SimpleTypeChangeSet('string'),
            ]),
        ];

        $current = self::createObject([
            'id' => 5,
            'name' => 'test',
            'properties' => [
                self::createObject(['name' => 'color', 'value' => 'red']),
                self::createObject(['name' => 'brand', 'value' => 'brady']),
            ],
            'price' => self::createObject(['original' => 126, 'discount' => 14]),
            'rating' => 4.7,
        ]);

        yield 'compute for two objects without changes' => [$current, $current, new ObjectTypeChangeSet([], $current)];

        $current = self::createObject([
            'id' => 5,
            'name' => 'rename',
            'properties' => [
                self::createObject(['name' => 'type', 'value' => 'vehicle']),
                self::createObject(['name' => 'brand', 'value' => 'brady']),
                self::createObject(['name' => 'color', 'value' => 'red']),
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
                self::createObject(['name' => 'color', 'value' => 'red']),
                self::createObject(['name' => 'brand', 'value' => 'brady']),
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
                        0 => new ObjectTypeChangeSet(
                            [
                                'name' => new SimpleTypeChangeSet('type'),
                                'value' => new SimpleTypeChangeSet('vehicle'),
                            ],
                            self::createObject(['name' => 'type', 'value' => 'vehicle']),
                        ),
                        2 => new SimpleTypeChangeSet(self::createObject(['name' => 'color', 'value' => 'red'])),
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

        $current = ['object' => self::createObject(['name' => 'rename'])];
        $previous = ['object' => new SimpleEntity()];

        yield 'compute for two different objects' => [$current, $previous, new ArrayTypeChangeSet([])];
    }

    /**
     * @param array<string, mixed> $properties
     */
    private static function createObject(array $properties): object
    {
        $object = new stdClass();

        foreach ($properties as $name => $value) {
            $object->{$name} = $value;
        }

        return $object;
    }
}
