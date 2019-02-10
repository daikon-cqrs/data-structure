<?php
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\DataStructure;

use Ds\Vector;
use InvalidArgumentException;
use Iterator;

trait TypedListTrait
{
    /** @var Vector */
    private $compositeVector;

    /** @var string fully qualified class name of acceptable types */
    private $itemFqcn;

    public function has(int $index): bool
    {
        return $this->compositeVector->offsetExists($index);
    }

    /**
     * @return mixed
     * @throws OutOfRangeException
     */
    public function get(int $index)
    {
        return $this->compositeVector->get($index);
    }

    /**
     * @param mixed $item
     * @throws InvalidArgumentException
     */
    public function push($item): self
    {
        $this->assertItemType($item);
        $copy = clone $this;
        $copy->compositeVector->push($item);
        return $copy;
    }

    /**
     * @param mixed $item
     * @throws InvalidArgumentException
     */
    public function prepend($item): self
    {
        $this->assertItemType($item);
        $copy = clone $this;
        $copy->compositeVector->unshift($item);
        return $copy;
    }

    /**
     * @param mixed $item
     * @throws InvalidArgumentException
     */
    public function remove($item): self
    {
        $idx = $this->indexOf($item);
        if (!is_int($idx)) {
            return $this;
        }
        $copy = clone $this;
        $copy->compositeVector->remove($idx);
        return $copy;
    }

    public function reverse(): self
    {
        $copy = clone $this;
        $copy->compositeVector->reverse();
        return $copy;
    }

    public function count(): int
    {
        return $this->compositeVector->count();
    }

    public function isEmpty(): bool
    {
        return $this->compositeVector->isEmpty();
    }

    /**
     * @param mixed $item
     * @return int|bool
     * @throws InvalidArgumentException
     */
    public function indexOf($item)
    {
        $this->assertItemType($item);
        return $this->compositeVector->find($item);
    }

    /** @return mixed */
    public function getFirst()
    {
        return $this->compositeVector->first();
    }

    /** @return mixed */
    public function getLast()
    {
        return $this->compositeVector->last();
    }

    public function toNative(): array
    {
        return $this->compositeVector->toArray();
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function getIterator(): Iterator
    {
        return $this->compositeVector->getIterator();
    }

    public function getItemFqcn(): string
    {
        return $this->itemFqcn;
    }

    private function init(iterable $items, string $itemFqcn): void
    {
        $this->itemFqcn = $itemFqcn;
        foreach ($items as $index => $item) {
            $this->assertItemIndex($index);
            $this->assertItemType($item);
        }
        $this->compositeVector = new Vector($items);
    }

    /** @param mixed $index */
    private function assertItemIndex($index): void
    {
        if (!is_int($index)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid item key given to %s. Expected int but was given %s.',
                static::CLASS,
                is_object($index) ? get_class($index) : @gettype($index)
            ));
        }
    }

    /** @param mixed $item */
    private function assertItemType($item): void
    {
        if (!is_a($item, $this->itemFqcn)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid item type given to %s. Expected %s but was given %s.',
                static::CLASS,
                $this->itemFqcn,
                is_object($item) ? get_class($item) : @gettype($item)
            ));
        }
    }

    private function __clone()
    {
        $this->compositeVector = clone $this->compositeVector;
    }
}
