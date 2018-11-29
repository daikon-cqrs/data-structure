<?php
declare(strict_types=1);

namespace Daikon\DataStructure;

use Ds\Vector;
use InvalidArgumentException;
use Iterator;

trait TypedListTrait
{
    /**
     * @var Vector internal vector to store items
     */
    private $compositeVector;

    /**
     * @var string fully qualified class name of acceptable types
     */
    private $itemFqcn;

    public function has(int $index): bool
    {
        return $this->compositeVector->offsetExists($index);
    }

    /**
     * @throws OutOfRangeException
     */
    public function get(int $index)
    {
        return $this->compositeVector->get($index);
    }

    /**
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
     * @throws InvalidArgumentException
     */
    public function remove($item): self
    {
        $idx = $this->indexOf($item);
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
     * @throws InvalidArgumentException
     */
    public function indexOf($item): int
    {
        $this->assertItemType($item);
        return $this->compositeVector->find($item);
    }

    public function getFirst()
    {
        return $this->compositeVector->first();
    }

    public function getLast()
    {
        return $this->compositeVector->last();
    }

    public function toNative(): array
    {
        return $this->compositeVector->toArray();
    }

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
