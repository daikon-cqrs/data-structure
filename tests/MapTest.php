<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure;

use Daikon\Interop\InvalidArgumentException;
use Daikon\Tests\DataStructure\Fixture\PlainMap;
use DateTime;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MapTest extends TestCase
{
    public function testConstructWithoutParams(): void
    {
        $this->assertInstanceOf(PlainMap::class, new PlainMap);
    }

    public function testConstructWithParams(): void
    {
        $map = new PlainMap(['k' => 'v']);
        $this->assertCount(1, $map);
    }

    public function testConstructWithObjects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid value type given to 'Daikon\Tests\DataStructure\Fixture\PlainMap', ".
            "expected scalar or array but was given 'DateTime'."
        );
        new PlainMap(['a1337' => new DateTime]);
    }

    public function testConstructWithIntegerKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(16);
        $this->expectExceptionMessage('Key must be a valid string.');
        new PlainMap([0 => 'v0']);
    }

    public function testKeys(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 1]);
        $this->assertSame(['k0', 'k1'], $map->keys());
    }

    public function testEmpty(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 1]);
        $empty = $map->empty();
        $this->assertNotSame($map, $empty);
        $this->assertCount(0, $empty);
        $this->assertTrue($empty->isEmpty());
    }

    public function testHas(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $this->assertTrue($map->has('k0'));
        $this->assertTrue($map->has('k1'));
        $this->assertFalse($map->has('K0'));
        $this->assertFalse($map->has('K1'));
    }

    public function testGet(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 1]);
        $this->assertSame('v0', $map->get('k0'));
        $this->assertSame(1, $map->get('k1'));
    }

    public function testGetWithDefault(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $this->assertSame('y', $map->get('x', 'y'));
    }

    public function testGetWithNullDefault(): void
    {
        $map = new PlainMap(['k0' => 'v0']);
        $this->assertNull($map->get('x', null));
    }

    public function testGetWithInvalidDefault(): void
    {
        $map = new PlainMap(['k0' => 'v0']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid value type given to 'Daikon\Tests\DataStructure\Fixture\PlainMap', ".
            "expected scalar or array but was given 'stdClass'."
        );
        $map->get('x', new stdClass);
    }

    public function testGetWithNoDefault(): void
    {
        $map = new PlainMap(['k0' => 'v0']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $this->expectExceptionMessage("Key 'x' not found and no default provided.");
        $map->x;
    }

    public function testGetThrowsForInternalProperties(): void
    {
        $map = new PlainMap(['k0' => 'v1']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $map->validTypes;
    }

    public function testWith(): void
    {
        $map = new PlainMap(['k0' => 'v0']);
        $unwrappedMap = $map->with('k1', 'v1')->unwrap();
        $this->assertSame('v0', $unwrappedMap['k0']);
        $this->assertSame('v1', $unwrappedMap['k1']);
        $this->assertCount(2, $unwrappedMap);
    }

    public function testWithFailsOnUnacceptableType(): void
    {
        $map = new PlainMap(['k0' => 'v0']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid value type given to 'Daikon\Tests\DataStructure\Fixture\PlainMap', ".
            "expected scalar or array but was given 'stdClass'."
        );
        $map->with('k1', new stdClass);
    }

    public function testWithout(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1', 'k2' => 'v2']);
        $unwrappedMap = $map->unwrap();
        $prunedMap = $map->without('k1')->unwrap();
        $this->assertSame($unwrappedMap['k0'], $prunedMap['k0']);
        $this->assertCount(2, $prunedMap);
        $this->assertSame('v0', $prunedMap['k0']);
        $this->assertSame('v2', $prunedMap['k2']);
        $this->assertArrayNotHasKey('k1', $prunedMap);
    }

    public function testWithoutWithNotExistentKey(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $this->expectExceptionMessage("Key 'k2' not found.");
        $map->without('k2');
    }

    public function testFirst(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $unwrappedMap = $map->unwrap();
        $this->assertSame('v0', $unwrappedMap['k0']);
        $this->assertSame($unwrappedMap['k0'], $map->first());
    }

    public function testLast(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $unwrappedMap = $map->unwrap();
        $this->assertSame('v1', $unwrappedMap['k1']);
        $this->assertSame($unwrappedMap['k1'], $map->last());
    }

    public function testIsEmpty(): void
    {
        $map = new PlainMap(['k0' => 'v1']);
        $this->assertFalse($map->isEmpty());
        $map = new PlainMap;
        $this->assertTrue($map->isEmpty());
    }

    public function testEquals(): void
    {
        $map0 = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $map1 = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $map2 = new PlainMap(['k2' => 'v2']);
        $this->assertTrue($map0->equals($map1));
        $this->assertFalse($map1->equals($map2));
        $this->assertFalse($map0->equals($map2));
    }

    public function testCount(): void
    {
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1']);
        $this->assertCount(2, $map);
    }

    public function testunwrap(): void
    {
        $state = ['k0' => 'v0', 'k1' => 'v1'];
        $map = new PlainMap($state);
        $this->assertSame($state, $map->unwrap());
    }

    public function testGetIterator(): void
    {
        $state = ['k0' => 'v0', 'k1' => 'v1'];
        $map = new PlainMap($state);
        foreach ($map as $key => $value) {
            $this->assertSame($state[$key], $value);
        }
    }

    public function testClone(): void
    {
        $t0 = [new stdClass];
        $map = new PlainMap(['k0' => 'v0', 'k1' => 'v1', 'k2' => $t0]);
        $clonedMap = clone $map;
        $this->assertNotSame($map->getIterator(), $clonedMap->getIterator());
        $this->assertEquals($map->getIterator(), $clonedMap->getIterator());
        $this->assertSame('v1', $map->get('k1'));
        //@todo handle deep clone objects in array?
        // $this->assertNotSame($t0, $clonedMap->get('k2'));
        $this->assertEquals($t0, $clonedMap->get('k2'));
    }
}
