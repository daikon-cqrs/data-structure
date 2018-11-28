<?php
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\DataStructure;

use Daikon\Tests\DataStructure\Fixture\DatetimeMap;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class TypedMapTraitTest extends TestCase
{
    public function testConstructNoParamsWorks()
    {
        $this->assertInstanceOf(DatetimeMap::class, new DatetimeMap);
    }

    public function testConstructWithParamsWorks()
    {
        $map = new DatetimeMap([ 'now' => new DateTime, 'nower' => new DateTimeImmutable ]);
        $this->assertInstanceOf(DatetimeMap::class, $map);
    }

    public function testConstructWithIndexedParamsWorks()
    {
        $map = new DatetimeMap([ 'a1337' => new DateTime, 'yes' => new DateTimeImmutable ]);
        $this->assertInstanceOf(DatetimeMap::class, $map);
        $this->assertEquals(2, $map->count());
        $this->assertTrue($map->has('yes'));
        $this->assertTrue($map->has('a1337'));
    }

    public function testConstructWithIntegerStringAsKeyThrowsBecausePhp()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Invalid item key given to Daikon\Tests\DataStructure\Fixture\DatetimeMap. '.
            'Expected string but was given integer.'
        );
        new DatetimeMap([ '1337' => new DateTime ]);
    } // @codeCoverageIgnore

    public function testConstructFailsOnInvalidIndex()
    {
        $d0 = new DateTime;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Invalid item key given to Daikon\Tests\DataStructure\Fixture\DatetimeMap. '.
            'Expected string but was given integer.'
        );
        new DatetimeMap([ 123 => $d0 ]);
    } // @codeCoverageIgnore

    public function testGetItemFqcnWorks()
    {
        $this->assertEquals([DateTimeInterface::class], (new DatetimeMap)->getItemFqcn());
    }

    public function testCountWorks()
    {
        $map = new DatetimeMap([ 'a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertEquals(2, $map->count());
    }

    public function testIsEmptyWorks()
    {
        $map = new DatetimeMap([ 'a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertFalse($map->isEmpty());
        $map = new DatetimeMap;
        $this->assertTrue($map->isEmpty());
    }

    public function testHasWorks()
    {
        $map = new DatetimeMap([ 'a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertTrue($map->has('a'));
        $this->assertTrue($map->has('b'));
        $this->assertFalse($map->has('A'));
        $this->assertFalse($map->has('B'));
    }

    public function testGetIteratorWorks()
    {
        $map = new DatetimeMap([ 'a' => new DateTime, 'b' => new DateTimeImmutable]);
        $this->assertInstanceOf(\Iterator::class, $map->getIterator());
    }

    public function testGetWorks()
    {
        $d1 = new DateTime;
        $map = new DatetimeMap([ 'a' => $d1, 'b' => new DateTimeImmutable]);
        $this->assertSame($d1, $map->get('a'));
        $this->assertTrue($d1 === $map->get('a'));
    }

    public function testGetThrowsForNonExistantKey()
    {
        $map = new DatetimeMap([ 'a' => new Datetime ]);
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode(0);
        $map->get('non-existant');
    } // @codeCoverageIgnore

    public function testGetThrowsForInternalProperties()
    {
        $map = new DatetimeMap([ 'a' => new Datetime ]);
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode(0);
        $map->itemFqcns;
    } // @codeCoverageIgnore

    public function testImplicitGetWorks()
    {
        $d1 = new DateTime;
        $map = new DatetimeMap([ 'a' => $d1, 'b' => new DateTimeImmutable]);
        $this->assertSame($d1, $map->a);
        $this->assertTrue($d1 === $map->a);
    }

    public function testImplicitGetWorksForWeirdKey()
    {
        $d1 = new DateTime;
        $key = '_a.b.123-456';
        $map = new DatetimeMap([ $key => $d1 ]);
        $this->assertSame($d1, $map->{'_a.b.123-456'});
        $this->assertTrue($d1 === $map->$key);
    }

    public function testSetWorks()
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $map = new DatetimeMap([ 'a' => $d0 ]);
        $map = $map->set('b', $d1);
        $this->assertSame($d0, $map->get('a'));
        $this->assertSame($d1, $map->get('b'));
        $this->assertEquals(2, $map->count());
    }

    public function testSetFailsOnUnacceptableType()
    {
        $d0 = new DateTime;
        $d1 = new \stdClass;
        $map = new DatetimeMap([ 'a' => $d0 ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Invalid item type given to Daikon\Tests\DataStructure\Fixture\DatetimeMap. '.
            'Expected one of DateTimeInterface but was given stdClass.'
        );
        $map->set('b', $d1);
    } // @codeCoverageIgnore

    public function testToNativeWorks()
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $a = [ 'a' => $d0, 'b' => $d1 ];
        $map = new DatetimeMap($a);
        $b = $map->toNative();
        $this->assertTrue($a === $b);
        $this->assertTrue($a['a'] === $b['a']);
        $this->assertTrue($a['b'] === $b['b']);
    }
}
