<?php

namespace Daikon\DataStructures;

use Ds\Vector;

trait TypedListTrait
{
    private $compositeVector;

    private $itemFqcn;

    public function has(int $index): bool
    {
        return $this->compositeVector->offsetExists($index);
    }

    public function get(int $index)
    {
        return $this->compositeVector->get($index);
    }

    public function push($item): self
    {
        $this->assertItemType($item);
        $copy = clone $this;
        $copy->compositeVector->push($item);
        return $copy;
    }

    public function remove($item): self
    {
        $idx = $this->indexOf($item);
        $copy = clone $this;
        $copy->compositeVector->remove($idx);
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

    public function indexOf($item)
    {
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

    public function toArray(): array
    {
        return $this->compositeVector->toArray();
    }

    public function getIterator(): \Iterator
    {
        return $this->compositeVector->getIterator();
    }

    public function getItemFqcn()
    {
        return $this->itemFqcn;
    }

    private function init(array $items, string $itemFqcn)
    {
        $this->itemFqcn = $itemFqcn;
        foreach ($items as $index => $item) {
            $this->assertItemIndex($index);
            $this->assertItemType($item);
        }
        $this->compositeVector = new Vector($items);
    }

    private function assertItemIndex($index)
    {
        if (!is_int($index)) {
            throw new \Exception(sprintf(
                'Invalid item-key given to %s. Expected int but was given %s',
                static::CLASS,
                is_object($index) ? get_class($index) : @gettype($index)
            ));
        }
    }

    private function assertItemType($item)
    {
        if (!is_a($item, $this->itemFqcn)) {
            throw new \Exception(sprintf(
                'Invalid item-type given to %s. Expected %s but was given %s',
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
