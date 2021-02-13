<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure;

use Daikon\Interop\InvalidArgumentException;
use Daikon\Tests\DataStructure\Fixture\DatetimeList;
use Daikon\Tests\DataStructure\Fixture\DatetimeMap;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypedListTest extends TestCase
{
    public function testConstructWithoutParams(): void
    {
        $this->assertInstanceOf(DatetimeList::class, new DatetimeList);
    }

    public function testConstructWithParams(): void
    {
        $list = new DatetimeList([new DateTime, new DateTimeImmutable]);
        $this->assertCount(2, $list);
    }

    public function testConstructWithIndexedParams(): void
    {
        $list = new DatetimeList([1337 => new DateTime, -7 => new DateTimeImmutable]);
        $this->assertCount(2, $list);
        $this->assertTrue($list->has(0));
        $this->assertTrue($list->has(1));
        $this->assertFalse($list->has(1337));
    }

    public function testConstructFailsOnInvalidIndex(): void
    {
        $d0 = new DateTime;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(12);
        $this->expectExceptionMessage('Index must be a valid integer.');
        new DatetimeList(['a' => $d0]);
    }

    public function testEmpty(): void
    {
        $list = new DatetimeList([new DateTime, new DateTimeImmutable]);
        $empty = $list->empty();
        $this->assertNotSame($list, $empty);
        $this->assertCount(0, $empty);
        $this->assertTrue($empty->isEmpty());
    }

    public function testHas(): void
    {
        $list = new DatetimeList([new DateTime, new DateTimeImmutable]);
        $this->assertTrue($list->has(0));
        $this->assertTrue($list->has(1));
        $this->assertFalse($list->has(3));
        $this->assertFalse($list->has(-1));
    }

    public function testGet(): void
    {
        $d1 = new DateTime;
        $list = new DatetimeList([$d1]);
        $unwrappedList = $list->unwrap();
        $this->assertNotSame($d1, $unwrappedList[0]);
        $this->assertEquals($d1, $unwrappedList[0]);
        $this->assertNotSame($d1, $list->get(0));
        $this->assertEquals($d1, $list->get(0));
    }

    public function testGetWithDefault(): void
    {
        $d1 = new DateTime;
        $default = new DateTime('@1234567');
        $list = new DatetimeList([$d1]);
        $this->assertNotSame($default, $list->get(1, $default));
        $this->assertEquals($default, $list->get(1, $default));
    }

    public function testGetWithNullDefault(): void
    {
        $d1 = new DateTime;
        $list = new DatetimeList([$d1]);
        $this->assertNull($list->get(1, null));
    }

    public function testGetWithInvalidDefault(): void
    {
        $d1 = new DateTime;
        $list = new DatetimeList([$d1]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid object type given to 'Daikon\Tests\DataStructure\Fixture\DatetimeList', ".
            "expected one of [DateTimeInterface] but was given 'stdClass'."
        );
        $list->get(1, new stdClass);
    }

    public function testGetWithNoDefault(): void
    {
        $list = new DatetimeList;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $this->expectExceptionMessage('Index 1 not found and no default provided.');
        $list->get(1);
    }

    public function testGetWithInvalidIndex(): void
    {
        $d0 = new DateTime;
        $list = new DatetimeList([$d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $this->expectExceptionMessage('Index 1 not found and no default provided.');
        $list->{1};
    }

    public function testWith(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d0]);
        $unwrappedList = $list->with(0, $d1)->unwrap();
        $this->assertNotSame($d1, $unwrappedList[0]);
        $this->assertEquals($d1, $unwrappedList[0]);
        $this->assertCount(1, $unwrappedList);
    }

    public function testWithInvalidIndex(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(217);
        $this->expectExceptionMessage('Index 1 not found.');
        $list->with(1, $d1);
    }

    public function testWithFailsOnUnacceptableType(): void
    {
        $d0 = new DateTime;
        $d1 = new stdClass;
        $list = new DatetimeList([$d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid object type given to 'Daikon\Tests\DataStructure\Fixture\DatetimeList', ".
            "expected one of [DateTimeInterface] but was given 'stdClass'."
        );
        $list->with(0, $d1);
    }

    public function testWithout(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable;
        $d2 = new DateTime;
        $list = new DatetimeList([$d0, $d1, $d2]);
        $unwrappedList = $list->unwrap();
        $this->assertCount(3, $list);
        $prunedList = $list->without(1)->unwrap();
        $this->assertNotSame($unwrappedList[0], $prunedList[0]);
        $this->assertEquals($unwrappedList[0], $prunedList[0]);
        $this->assertCount(2, $prunedList);
        $this->assertNotSame($d0, $prunedList[0]);
        $this->assertEquals($d0, $prunedList[0]);
        $this->assertNotSame($d2, $prunedList[1]);
        $this->assertEquals($d2, $prunedList[1]);
    }

    public function testFind(): void
    {
        $d0 = new DateTimeImmutable('@1234567');
        $d1 = new DateTimeImmutable('@7654321');
        $list = new DatetimeList([$d0, $d1]);
        $this->assertEquals(0, $list->find($d0));
        $this->assertEquals(1, $list->find($d1));
    }

    public function testFirst(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $this->assertNotSame($d0, $list->first());
        $this->assertEquals($d0, $list->first());
        $this->assertNotSame($unwrappedList[0], $list->first());
        $this->assertEquals($unwrappedList[0], $list->first());
    }

    public function testLast(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $this->assertNotSame($d1, $list->last());
        $this->assertEquals($d1, $list->last());
        $this->assertNotSame($unwrappedList[1], $list->last());
        $this->assertEquals($unwrappedList[1], $list->last());
    }

    public function testIsEmpty(): void
    {
        $list0 = new DatetimeList([new DateTime, new DateTimeImmutable]);
        $this->assertFalse($list0->isEmpty());
        $list1 = new DatetimeList;
        $this->assertTrue($list1->isEmpty());
        $list2 = new DatetimeList([new DateTime]);
        $this->assertTrue($list2->without(0)->isEmpty());
    }

    public function testAppend(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list0 = new DatetimeList([$d0]);
        $list1 = new DatetimeList([$d1]);
        $unwrappedList0 = $list0->unwrap();
        $unwrappedList1 = $list1->unwrap();
        $appendedList = $list0->append($list1)->unwrap();
        $this->assertNotSame($unwrappedList0[0], $appendedList[0]);
        $this->assertEquals($unwrappedList0[0], $appendedList[0]);
        $this->assertNotSame($unwrappedList1[0], $appendedList[1]);
        $this->assertEquals($unwrappedList1[0], $appendedList[1]);
        $this->assertNotSame($list0, $appendedList);
        $this->assertNotSame($list1, $appendedList);
        $this->assertCount(2, $appendedList);
        $this->assertNotSame($d0, $appendedList[0]);
        $this->assertEquals($d0, $appendedList[0]);
        $this->assertNotSame($d1, $appendedList[1]);
        $this->assertEquals($d1, $appendedList[1]);
    }

    public function testAppendWithInvalidParam(): void
    {
        $list = new DatetimeList;
        $map = new DatetimeMap;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(28);
        $this->expectExceptionMessage(
            "List operation must be on same type as 'Daikon\Tests\DataStructure\Fixture\DatetimeList'."
        );
        /** @psalm-suppress InvalidArgument */
        $list->append($map);
    }

    public function testPush(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable('@765432');
        $list = new DatetimeList([$d0]);
        $setList = $list->push($d1)->unwrap();
        $this->assertNotSame($d0, $setList[0]);
        $this->assertEquals($d0, $setList[0]);
        $this->assertNotSame($d1, $setList[1]);
        $this->assertEquals($d1, $setList[1]);
        $this->assertCount(2, $setList);
    }

    public function testPushInvalidType(): void
    {
        $d0 = new DateTime;
        $list = new DatetimeList([$d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid object type given to 'Daikon\Tests\DataStructure\Fixture\DatetimeList', ".
            "expected one of [DateTimeInterface] but was given 'stdClass'."
        );
        $list->push(new stdClass);
    }

    public function testUnshift(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d1]);
        $unwrappedList = $list->unwrap();
        $unshiftedList = $list->unshift($d0)->unwrap();
        $this->assertNotSame($unwrappedList[0], $unshiftedList[1]);
        $this->assertEquals($unwrappedList[0], $unshiftedList[1]);
        $this->assertCount(2, $unshiftedList);
        $this->assertNotSame($d0, $unshiftedList[0]);
        $this->assertEquals($d0, $unshiftedList[0]);
        $this->assertNotSame($d1, $unshiftedList[1]);
        $this->assertEquals($d1, $unshiftedList[1]);
    }

    public function testUnshiftFailsOnUnacceptableType(): void
    {
        $d0 = new DateTime;
        $d1 = new stdClass;
        $list = new DatetimeList([$d0]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(32);
        $this->expectExceptionMessage(
            "Invalid object type given to 'Daikon\Tests\DataStructure\Fixture\DatetimeList', ".
            "expected one of [DateTimeInterface] but was given 'stdClass'."
        );
        $list->unshift($d1);
    }

    public function testReverse(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $reversedList = $list->reverse()->unwrap();
        $this->assertNotSame($unwrappedList[0], $reversedList[1]);
        $this->assertEquals($unwrappedList[0], $reversedList[1]);
        $this->assertNotSame($d0, $unwrappedList[0]);
        $this->assertEquals($d0, $unwrappedList[0]);
        $this->assertNotSame($d1, $unwrappedList[1]);
        $this->assertEquals($d1, $unwrappedList[1]);
        $this->assertCount(2, $unwrappedList);
        $this->assertNotSame($d1, $reversedList[0]);
        $this->assertEquals($d1, $reversedList[0]);
        $this->assertNotSame($d0, $reversedList[1]);
        $this->assertEquals($d0, $reversedList[1]);
        $this->assertCount(2, $reversedList);
    }

    public function testReplace(): void
    {
        $d0 = new DateTimeImmutable('@1234567');
        $d1 = new DateTimeImmutable('@7654321');
        $d2 = new DateTimeImmutable('@3333333');
        $list = new DatetimeList([$d0, $d2]);
        $replacedList = $list->replace(
            fn(DateTimeInterface $object): bool => $object->getTimestamp() === 1234567,
            $d1
        )->unwrap();
        $this->assertNotSame($list, $replacedList);
        $this->assertNotSame($d1, $replacedList[0]);
        $this->assertEquals($d1, $replacedList[0]);
        $this->assertNotSame($d2, $replacedList[1]);
        $this->assertEquals($d2, $replacedList[1]);
        $this->assertCount(2, $replacedList);
    }

    public function testSort(): void
    {
        $d0 = new DateTime('@7654321');
        $d1 = new DateTimeImmutable('@1234567');
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $this->assertNotSame($d0, $unwrappedList[0]);
        $this->assertEquals($d0, $unwrappedList[0]);
        $this->assertNotSame($d1, $unwrappedList[1]);
        $this->assertEquals($d1, $unwrappedList[1]);
        $sortedList = $list->sort(
            fn(DateTimeInterface $f0, DateTimeInterface $f1): int => $f0 > $f1 ? 1 : 0
        )->unwrap();
        $this->assertNotSame($unwrappedList[1], $sortedList[0]);
        $this->assertEquals($unwrappedList[1], $sortedList[0]);
        $this->assertNotSame($unwrappedList[0], $sortedList[1]);
        $this->assertEquals($unwrappedList[0], $sortedList[1]);
    }

    public function testFilter(): void
    {
        $d0 = new DateTimeImmutable('@7654321');
        $d1 = new DateTimeImmutable('@1234567');
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $filteredList = $list->filter(
            fn(DateTimeInterface $d): bool => $d > new DateTimeImmutable('@4444444')
        )->unwrap();
        $this->assertNotSame($unwrappedList[0], $filteredList[0]);
        $this->assertEquals($unwrappedList[0], $filteredList[0]);
        $this->assertCount(1, $filteredList);
        $this->assertNotSame($d0, $filteredList[0]);
        $this->assertEquals($d0, $filteredList[0]);
    }

    public function testFilterEmpty(): void
    {
        $list = new DatetimeList;
        $filteredList = $list->filter(fn(): bool => true);
        $this->assertNotSame($list, $filteredList);
    }

    public function testSearch(): void
    {
        $d1 = new DateTimeImmutable('@1234567');
        $list = new DatetimeList([new DateTimeImmutable, $d1]);
        $unwrappedList = $list->unwrap();
        $this->assertNotSame($d1, $unwrappedList[1]);
        $this->assertEquals($d1, $unwrappedList[1]);
        $predicate = fn(DateTimeInterface $object): bool => $d1->getTimestamp() === $object->getTimestamp();
        $this->assertEquals(1, $list->search($predicate));
    }

    public function testMap(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable('@1234567');
        $list = new DatetimeList([$d0, $d1]);
        $unwrappedList = $list->unwrap();
        $appliedList = $list->map(fn(DateTimeInterface $item) => $item)->unwrap();
        $this->assertNotSame($unwrappedList[0], $appliedList[0]);
        $this->assertEquals($unwrappedList[0], $appliedList[0]);
        $this->assertCount(2, $appliedList);
        $this->assertNotSame($d0, $appliedList[0]);
        $this->assertEquals($d0, $appliedList[0]);
        $this->assertNotSame($d1, $appliedList[1]);
        $this->assertEquals($d1, $appliedList[1]);
    }

    public function testReduce(): void
    {
        $d0 = new DateTimeImmutable;
        $d1 = new DateTimeImmutable('@1234567');
        $list = new DatetimeList([$d0, $d1]);
        $result = $list->reduce(fn(): bool => true, false);
        $this->assertTrue($result);
    }

    public function testGetValidTypes(): void
    {
        $this->assertEquals([DateTimeInterface::class], (new DatetimeList)->getValidTypes());
    }

    public function testunwrap(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $a = [$d0, $d1];
        $list = new DatetimeList($a);
        $b = $list->unwrap();
        $this->assertNotSame($a, $b);
        $this->assertEquals($a, $b);
        $this->assertNotSame($a[0], $b[0]);
        $this->assertEquals($a[0], $b[0]);
        $this->assertNotSame($a[1], $b[1]);
        $this->assertEquals($a[1], $b[1]);
    }

    public function testIterator(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $state = [$d0, $d1];
        $list = new DatetimeList($state);
        $unwrappedList = $list->unwrap();
        foreach ($list as $index => $current) {
            $this->assertNotSame($unwrappedList[$index], $current);
            $this->assertEquals($unwrappedList[$index], $current);
            $this->assertNotSame($state[$index], $current);
            $this->assertEquals($state[$index], $current);
        }
    }

    public function testImplicitGet(): void
    {
        $d1 = new DateTime;
        $d2 = new DateTimeImmutable;
        $map = new DatetimeList([$d1, $d2]);
        $this->assertNotSame($d1, $map->{0});
        $this->assertEquals($d1, $map->{0});
        $this->assertNotSame($d2, $map->{1});
        $this->assertEquals($d2, $map->{1});
    }

    public function testCount(): void
    {
        $list = new DatetimeList([new DateTime, new DateTimeImmutable]);
        $this->assertCount(2, $list);
    }

    public function testClone(): void
    {
        $d0 = new DateTime;
        $d1 = new DateTimeImmutable;
        $a = [$d0, $d1];
        $list = new DatetimeList($a);
        $unwrappedList = $list->unwrap();
        $clonedList = clone $list;
        $unwrappedClone = $clonedList->unwrap();
        $this->assertSame($list->getValidTypes(), $clonedList->getValidTypes());
        $this->assertNotSame($list->getIterator(), $clonedList->getIterator());
        $this->assertEquals($list->getIterator(), $clonedList->getIterator());
        $this->assertNotSame($unwrappedList[0], $unwrappedClone[0]);
        $this->assertEquals($unwrappedList[0], $unwrappedClone[0]);
    }
}
