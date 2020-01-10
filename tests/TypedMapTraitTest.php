<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure;

use Daikon\Tests\DataStructure\Fixture\DatetimeList;
use Daikon\Tests\DataStructure\Fixture\DatetimeMap;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypedMapTraitTest extends TestCase
{
    public function testConstructWithoutParams(): void
    {
        $this->assertInstanceOf(DatetimeMap::class, new DatetimeMap);
    }

    public function testConstructWithParams(): void
    {
        $map = new DatetimeMap(['now' => new DateTime, 'nower' => new DateTimeImmutable]);
        /** @psalm-suppress RedundantCondition */
        $this->assertInstanceOf(DatetimeMap::class, $map);
    }

    public function testConstructWithIndexedParams(): void
    {
        $map = new DatetimeMap(['a1337' => new DateTime, 'yes' => new DateTimeImmutable]);
        $this->assertCount(2, $map);
        $this->assertTrue($map->has('yes'));
        $this->assertTrue($map->has('a1337'));
    }

    public function testConstructWithIntegerStringAsKeyThrowsBecausePhp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('Key must be a valid string');
        new DatetimeMap(['1337' => new DateTime]);
    } // @codeCoverageIgnore

    public function testConstructFailsOnInvalidIndex(): void
    {
        $d0 = new DateTime;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('Key must be a valid string');
        new DatetimeMap([123 => $d0]);
    } // @codeCoverageIgnore

    public function testKeys(): void
    {
        $map = new DatetimeMap(['a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertSame(['a', 'b'], $map->keys());
    }

    public function testHas(): void
    {
        $map = new DatetimeMap(['a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertTrue($map->has('a'));
        $this->assertTrue($map->has('b'));
        $this->assertFalse($map->has('A'));
        $this->assertFalse($map->has('B'));
    }

    public function testGet(): void
    {
        $d1 = new DateTime;
        $map = new DatetimeMap(['a' => $d1, 'b' => new DateTimeImmutable]);
        $unwrappedMap = $map->unwrap();
        $this->assertNotSame($d1, $unwrappedMap['a']);
        $this->assertEquals($d1, $unwrappedMap['a']);
        $this->assertNotSame($unwrappedMap['a'], $map->get('a'));
        $this->assertEquals($unwrappedMap['a'], $map->get('a'));
    }

    public function testGetWithDefault(): void
    {
        $d1 = new DateTime;
        $default = new DateTime('@1234567');
        $map = new DatetimeMap(['a' => $d1]);
        $this->assertNotSame($default, $map->get('x', $default));
        $this->assertEquals($default, $map->get('x', $default));
    }

    public function testGetWithNullDefault(): void
    {
        $d1 = new DateTime;
        $map = new DatetimeMap(['a' => $d1]);
        $this->assertNull($map->get('x', null));
    }

    public function testGetWithInvalidDefault(): void
    {
        $d1 = new DateTime;
        $map = new DatetimeMap(['a' => $d1]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            'Invalid object type given to Daikon\Tests\DataStructure\Fixture\DatetimeMap, '.
            "expected one of [DateTimeInterface] but was given 'stdClass'"
        );
        $map->get('x', new stdClass);
    }

    public function testGetWithNoDefault(): void
    {
        $map = new DateTimeMap;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage("Key 'x' not found and no default provided");
        $map->get('x');
    } // @codeCoverageIgnore

    public function testGetThrowsForInternalProperties(): void
    {
        $map = new DatetimeMap(['a' => new Datetime]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $map->validTypes;
    } // @codeCoverageIgnore

    public function testWith(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $map = new DatetimeMap(['a' => $d0]);
        $unwrappedMap = $map->with('b', $d1)->unwrap();
        $this->assertNotSame($d0, $unwrappedMap['a']);
        $this->assertEquals($d0, $unwrappedMap['a']);
        $this->assertNotSame($d1, $unwrappedMap['b']);
        $this->assertEquals($d1, $unwrappedMap['b']);
        $this->assertCount(2, $unwrappedMap);
    }

    public function testWithFailsOnUnacceptableType(): void
    {
        $d0 = new DateTime;
        $d1 = new stdClass;
        $map = new DatetimeMap(['a' => $d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            'Invalid object type given to Daikon\Tests\DataStructure\Fixture\DatetimeMap, '.
            "expected one of [DateTimeInterface] but was given 'stdClass'"
        );
        $map->with('b', $d1);
    } // @codeCoverageIgnore

    public function testWithout(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable;
        $d2 = new DateTime;
        $map = new DatetimeMap(['a' => $d0, 'b' => $d1, 'c' => $d2]);
        $unwrappedMap = $map->unwrap();
        $prunedMap = $map->without('b')->unwrap();
        $this->assertNotSame($unwrappedMap['a'], $prunedMap['a']);
        $this->assertEquals($unwrappedMap['a'], $prunedMap['a']);
        $this->assertCount(2, $prunedMap);
        $this->assertNotSame($d0, $prunedMap['a']);
        $this->assertEquals($d0, $prunedMap['a']);
        $this->assertNotSame($d2, $prunedMap['c']);
        $this->assertEquals($d2, $prunedMap['c']);
        $this->assertArrayNotHasKey('b', $prunedMap);
    }

    public function testWithoutWithNotExistentKey(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTime;
        $map = new DatetimeMap(['a' => $d0, 'b' => $d1]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage("Key 'c' not found");
        $map->without('c');
    } // @codeCoverageIgnore

    public function testFind(): void
    {
        $d1 = new DateTimeImmutable;
        $map = new DatetimeMap(['a' => new DateTimeImmutable, 'b' => $d1]);
        $unwrappedMap = $map->unwrap();
        $this->assertNotSame($d1, $unwrappedMap['b']);
        $this->assertEquals($d1, $unwrappedMap['b']);
        $this->assertEquals('b', $map->find($d1));
    }

    public function testFirst(): void
    {
        $d1 = new DateTimeImmutable;
        $map = new DatetimeMap(['a' => $d1, 'b' => new DateTimeImmutable]);
        $unwrappedMap = $map->unwrap();
        $this->assertNotSame($d1, $unwrappedMap['a']);
        $this->assertEquals($d1, $unwrappedMap['a']);
        $this->assertNotSame($unwrappedMap['a'], $map->first());
        $this->assertEquals($unwrappedMap['a'], $map->first());
    }

    public function testLast(): void
    {
        $d1 = new DateTimeImmutable;
        $d2 = new DateTimeImmutable;
        $map = new DatetimeMap(['a' => $d1, 'b' => $d2]);
        $unwrappedMap = $map->unwrap();
        $this->assertNotSame($d2, $unwrappedMap['b']);
        $this->assertEquals($d2, $unwrappedMap['b']);
        $this->assertNotSame($unwrappedMap['b'], $map->last());
        $this->assertEquals($unwrappedMap['b'], $map->last());
    }

    public function testIsEmpty(): void
    {
        $map = new DatetimeMap(['a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertFalse($map->isEmpty());
        $map = new DatetimeMap;
        $this->assertTrue($map->isEmpty());
    }

    public function testMerge(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $d2 = new DateTimeImmutable('@1234567');
        $d3 = new DateTimeImmutable('@7654321');
        $map0 = new DatetimeMap(['a' => $d0, 'c' => $d3]);
        $map1 = new DatetimeMap(['a' => $d1, 'b' => $d2]);
        $unwrappedMap0 = $map0->unwrap();
        $mergedMap = $map0->merge($map1)->unwrap();
        $this->assertCount(3, $mergedMap);
        $this->assertNotSame($unwrappedMap0['c'], $mergedMap['c']);
        $this->assertEquals($unwrappedMap0['c'], $mergedMap['c']);
        $this->assertNotSame($d1, $mergedMap['a']);
        $this->assertEquals($d1, $mergedMap['a']);
        $this->assertNotSame($d2, $mergedMap['b']);
        $this->assertEquals($d2, $mergedMap['b']);
        $this->assertNotSame($d3, $mergedMap['c']);
        $this->assertEquals($d3, $mergedMap['c']);
    }

    public function testMergeWithInvalidParam(): void
    {
        $map = new DatetimeMap;
        $list = new DatetimeList;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(28);
        $this->expectExceptionMessage(
            'Map operation must be on same type as Daikon\Tests\DataStructure\Fixture\DatetimeMap'
        );
        /** @psalm-suppress InvalidArgument */
        $map->merge($list);
    } // @codeCoverageIgnore

    public function testIntersect(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $d2 = new DateTimeImmutable('@1234567');
        $map0 = new DatetimeMap(['a' => $d0]);
        $map1 = new DatetimeMap(['a' => $d1, 'b' => $d2]);
        $unwrappedMap0 = $map0->unwrap();
        $intersectedMap = $map0->intersect($map1)->unwrap();
        $this->assertNotSame($unwrappedMap0['a'], $intersectedMap['a']);
        $this->assertEquals($unwrappedMap0['a'], $intersectedMap['a']);
        $this->assertCount(1, $intersectedMap);
        $this->assertNotSame($d0, $intersectedMap['a']);
        $this->assertEquals($d0, $intersectedMap['a']);
    }

    public function testIntersectWithInvalidParam(): void
    {
        $map = new DatetimeMap;
        $list = new DatetimeList;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(28);
        $this->expectExceptionMessage(
            'Map operation must be on same type as Daikon\Tests\DataStructure\Fixture\DatetimeMap'
        );
        /** @psalm-suppress InvalidArgument */
        $map->intersect($list);
    } // @codeCoverageIgnore

    public function testDiff(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $d2 = new DateTimeImmutable('@1234567');
        $map0 = new DatetimeMap(['a' => $d1, 'b' => $d2]);
        $map1 = new DatetimeMap(['a' => $d0]);
        $unwrappedMap0 = $map0->unwrap();
        $diffedMap = $map0->diff($map1)->unwrap();
        $this->assertNotSame($unwrappedMap0['b'], $diffedMap['b']);
        $this->assertEquals($unwrappedMap0['b'], $diffedMap['b']);
        $this->assertCount(1, $diffedMap);
        $this->assertNotSame($d2, $diffedMap['b']);
        $this->assertEquals($d2, $diffedMap['b']);
    }

    public function testDiffWithInvalidParam(): void
    {
        $map = new DatetimeMap;
        $list = new DatetimeList;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(28);
        $this->expectExceptionMessage(
            'Map operation must be on same type as Daikon\Tests\DataStructure\Fixture\DatetimeMap'
        );
        /** @psalm-suppress InvalidArgument */
        $map->diff($list);
    } // @codeCoverageIgnore

    public function testFilter(): void
    {
        $d0 = new DateTimeImmutable('@7654321');
        $d1 = new DateTimeImmutable('@1234567');
        $map = new DatetimeMap(['a' => $d0, 'b' => $d1]);
        $unwrappedMap = $map->unwrap();
        $filteredMap = $map->filter(
            fn(string $key, DateTimeInterface $d): bool => $d > new DateTimeImmutable('@4444444')
        )->unwrap();
        $this->assertNotSame($unwrappedMap['a'], $filteredMap['a']);
        $this->assertEquals($unwrappedMap['a'], $filteredMap['a']);
        $this->assertCount(1, $filteredMap);
        $this->assertNotSame($d0, $filteredMap['a']);
        $this->assertEquals($d0, $filteredMap['a']);
    }

    public function testFilterEmpty(): void
    {
        $map = new DatetimeMap;
        $filteredList = $map->filter(fn(): bool => true);
        $this->assertNotSame($map, $filteredList);
    }

    public function testSearch(): void
    {
        $d1 = new DateTimeImmutable('@1234567');
        $map = new DatetimeMap(['a' => new DateTimeImmutable, 'b' => $d1]);
        $unwrappedMap = $map->unwrap();
        $this->assertNotSame($d1, $unwrappedMap['b']);
        $this->assertEquals($d1, $unwrappedMap['b']);
        $predicate = fn(DateTimeInterface $object): bool => $d1->getTimestamp() === $object->getTimestamp();
        $this->assertEquals('b', $map->search($predicate));
    }

    public function testMap(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable('@1234567');
        $map = new DatetimeMap(['a' => $d0, 'b' => $d1]);
        $unwrappedMap = $map->unwrap();
        $appliedMap = $map->map(
            fn(string $key, DateTimeInterface $item) => $item
        )->unwrap();
        $this->assertNotSame($unwrappedMap['a'], $appliedMap['a']);
        $this->assertEquals($unwrappedMap['a'], $appliedMap['a']);
        $this->assertCount(2, $appliedMap);
        $this->assertNotSame($d0, $appliedMap['a']);
        $this->assertEquals($d0, $appliedMap['a']);
        $this->assertNotSame($d1, $appliedMap['b']);
        $this->assertEquals($d1, $appliedMap['b']);
    }

    public function testReduce(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable('@1234567');
        $map = new DatetimeMap(['a' => $d0, 'b' => $d1]);
        $result = $map->reduce(fn(string $carry, string $key): string => $key, 'a');
        $this->assertEquals('b', $result);
    }

    public function testGetValidTypes(): void
    {
        $this->assertEquals([DateTimeInterface::class], (new DatetimeMap)->getValidTypes());
    }

    public function testunwrap(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $a = ['a' => $d0, 'b' => $d1];
        $map = new DatetimeMap($a);
        $b = $map->unwrap();
        $this->assertNotSame($a, $b);
        $this->assertEquals($a, $b);
        $this->assertNotSame($a['a'], $b['a']);
        $this->assertEquals($a['a'], $b['a']);
        $this->assertNotSame($a['b'], $b['b']);
        $this->assertEquals($a['b'], $b['b']);
    }

    public function testIterator(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $state = ['a' => $d0, 'b' => $d1];
        $map = new DatetimeMap($state);
        $unwrappedMap = $map->unwrap();
        foreach ($map as $key => $current) {
            $this->assertNotSame($unwrappedMap[$key], $current);
            $this->assertEquals($unwrappedMap[$key], $current);
            $this->assertNotSame($state[$key], $current);
            $this->assertEquals($state[$key], $current);
        }
    }

    public function testImplicitGet(): void
    {
        $d1 = new DateTime;
        $map = new DatetimeMap(['a' => $d1, 'b' => new DateTimeImmutable]);
        $this->assertNotSame($d1, $map->a);
        $this->assertEquals($d1, $map->a);
    }

    public function testImplicitGetForWeirdKey(): void
    {
        $d1 = new DateTime;
        $key = '_a.b.123-456';
        $map = new DatetimeMap([$key => $d1]);
        $this->assertNotSame($d1, $map->{'_a.b.123-456'});
        $this->assertEquals($d1, $map->$key);
    }

    public function testCount(): void
    {
        $map = new DatetimeMap(['a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertCount(2, $map);
    }

    public function testClone(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $t0 = new stdClass;
        $a = ['a' => $d0, 'b' => $d1];
        $map = new DatetimeMap($a, $t0);
        $unwrappedMap = $map->unwrap();
        $clonedMap = clone $map;
        $unwrappedClone = $clonedMap->unwrap();
        $this->assertSame($map->getValidTypes(), $clonedMap->getValidTypes());
        $this->assertNotSame($map->getIterator(), $clonedMap->getIterator());
        $this->assertEquals($map->getIterator(), $clonedMap->getIterator());
        $this->assertNotSame($unwrappedMap['a'], $unwrappedClone['a']);
        $this->assertEquals($unwrappedMap['a'], $unwrappedClone['a']);
        $this->assertNotSame($t0, $clonedMap->getTestVar());
        $this->assertEquals($t0, $clonedMap->getTestVar());
    }
}
