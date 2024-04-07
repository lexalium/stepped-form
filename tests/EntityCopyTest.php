<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

use DateTime;
use Exception;
use Lexal\SteppedForm\EntityCopy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EntityCopyTest extends TestCase
{
    /**
     * @param array{0?: mixed} $replace
     */
    #[DataProvider('copyCommonDataProvider')]
    public function testCopyCommon(mixed $entity, array $replace, mixed $expected): void
    {
        $copied = EntityCopy::copy($entity, ...$replace);

        self::assertEquals($expected, $copied);
    }

    /**
     * @return iterable<string, array{0: mixed, 1: array{0?: mixed}, 2: mixed}>
     */
    public static function copyCommonDataProvider(): iterable
    {
        yield 'copy array' => [
            ['id' => 5, 'name' => 'test', ['properties' => ['red', 'vehicle']]],
            [],
            ['id' => 5, 'name' => 'test', ['properties' => ['red', 'vehicle']]],
        ];

        yield 'copy array with replace' => [
            ['id' => 5, 'name' => 'test', ['properties' => ['red', 'vehicle']]],
            [['name' => 'replaced', ['properties' => ['green']]]],
            ['id' => 5, 'name' => 'replaced', ['properties' => ['green', 'vehicle']]],
        ];

        yield 'copy array when replace is not array' => [
            ['id' => 5, 'name' => 'test', ['properties' => ['red', 'vehicle']]],
            [5],
            ['id' => 5, 'name' => 'test', ['properties' => ['red', 'vehicle']]],
        ];

        yield 'copy string' => ['string', [], 'string'];
        yield 'copy string with replace' => ['string', ['replaced'], 'replaced'];
        yield 'copy integer' => [5, [], 5];
        yield 'copy integer with replace' => [5, [7], 7];
        yield 'copy float' => [12.4, [], 12.4];
        yield 'copy float with replace' => [12.4, [24.6], 24.6];
        yield 'copy boolean' => [true, [], true];
        yield 'copy boolean with replace' => [true, [false], false];

        $entity = new Entity('string', 5, 14.8, true, 'readonly', 'private');
        $std = new stdClass();
        $std->name = 'name';

        $entity2 = new Entity('string', 16, 14.8, true, 'readonly', 'private');
        $std2 = new stdClass();
        $std2->name = 'replaced';

        yield 'copy mixed types' => [
            ['id' => 5, 'entity' => $entity, 'std' => $std, 'enum' => EntityEnum::Active],
            [['entity' => ['protected' => 16], 'std' => ['name' => 'replaced'], 'enum' => EntityEnum::Canceled]],
            ['id' => 5, 'entity' => $entity2, 'std' => $std2, 'enum' => EntityEnum::Active],
        ];
    }

    /**
     * @param array{0?: array<string, mixed>} $replace
     */
    #[DataProvider('copyObjectsDataProvider')]
    public function testCopyObjects(mixed $entity, array $replace, mixed $expected): void
    {
        $copied = EntityCopy::copy($entity, ...$replace);

        self::assertEquals($expected, $copied);
        self::assertNotSame($entity, $copied);

        if ($entity instanceof Entity) {
            self::assertNotSame($entity->getNested(), $copied->getNested());
        }
    }

    /**
     * @return iterable<string, array{0: object, 1: array{0?: int|array<string, mixed>}, 2: object}>
     */
    public static function copyObjectsDataProvider(): iterable
    {
        $nested = new Entity('string2', 42, 26.7, false, 'readonly2', 'private2');
        $entity = new Entity('string', 5, 14.8, true, 'readonly', 'private', $nested);

        yield 'copy object' => [$entity, [], $entity];

        $nested2 = new Entity('replaced4', 68, 127.45, true, 'replaced5', 'replaced6');
        $entity2 = new Entity('replaced', 8, 17.98, false, 'replaced2', 'replaced3', $nested2);

        yield 'copy object with replace' => [
            $entity,
            [
                [
                    'public' => 'replaced',
                    'protected' => 8,
                    'private' => 17.98,
                    'publicReadonly' => false,
                    'protectedReadonly' => 'replaced2',
                    'privateReadonly' => 'replaced3',
                    'nested' => [
                        'public' => 'replaced4',
                        'protected' => 68,
                        'private' => 127.45,
                        'publicReadonly' => true,
                        'protectedReadonly' => 'replaced5',
                        'privateReadonly' => 'replaced6',
                    ],
                ],
            ],
            $entity2,
        ];

        $entity2 = new Entity('string', 5, 64.8, true, 'readonly', 'replaced3');

        yield 'copy object with replace some properties' => [
            $entity,
            [
                [
                    'private' => 64.8,
                    'privateReadonly' => 'replaced3',
                    'nested' => null,
                ],
            ],
            $entity2,
        ];

        $entity = new stdClass();
        $entity->name = 'test';

        yield 'copy stdClass without replace' => [$entity, [], $entity];

        $entity2 = new stdClass();
        $entity2->name = 'replaced';

        yield 'copy stdClass with replace' => [$entity, [['name' => 'replaced']], $entity2];

        yield 'copy stdClass when replace is not array' => [$entity, [5], $entity];

        yield 'copy internal objects' => [
            new DateTime('2024-04-05'),
            [['timezone' => 'Pacific/Nauru']],
            new DateTime('2024-04-05'),
        ];

        yield 'copy cloneable object' => [
            new CloneableEntity('text'),
            [['text' => 'replace', 'name' => 'rename']],
            new CloneableEntity('text', 'rename'),
        ];
    }

    /**
     * @param array{0?: array<string, mixed>} $replace
     */
    #[DataProvider('copyUncloneableDataProvider')]
    public function testCopyUncloneable(object $object, array $replace, object $expected): void
    {
        $copied = EntityCopy::copy($object, ...$replace);

        self::assertSame($expected, $copied);
    }

    /**
     * @return iterable<string, array{0: object, 1: array{0?: array<string, mixed>}, 2: object}>
     */
    public static function copyUncloneableDataProvider(): iterable
    {
        yield 'copy enum without replace' => [EntityEnum::Active, [], EntityEnum::Active];
        yield 'copy enum with replace' => [
            EntityEnum::Active,
            [['name' => 'Canceled', 'value' => 'canceled']],
            EntityEnum::Active,
        ];

        $object = new Exception('test');

        yield 'copy uncloneable object' => [$object, [['message' => 'rename']], $object];
    }
}
