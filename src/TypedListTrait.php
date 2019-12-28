<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Ds\Vector;
use InvalidArgumentException;
use OutOfRangeException;
use RuntimeException;
use Traversable;

trait TypedListTrait
{
    /** @var Vector */
    private $compositeVector;

    /** @var string[] */
    private $itemTypes = [];

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
    public function unshift($item): self
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
        $index = $this->indexOf($item);
        if ($index === false) {
            return $this;
        }
        $copy = clone $this;
        $copy->compositeVector->remove((int)$index);
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

    public function getIterator(): Traversable
    {
        return $this->compositeVector->getIterator();
    }

    public function getItemTypes(): array
    {
        return $this->itemTypes;
    }

    /** @param string|string[] $itemTypes */
    private function init(iterable $items, $itemTypes): void
    {
        $this->itemTypes = (array)$itemTypes;
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
                static::class,
                is_object($index) ? get_class($index) : @gettype($index)
            ));
        }
    }

    /** @param mixed $item */
    private function assertItemType($item): void
    {
        if (empty($this->itemTypes)) {
            throw new RuntimeException('Item types have not been specified.');
        }

        $itemIsValid = false;
        foreach ($this->itemTypes as $type) {
            if (is_a($item, $type)) {
                $itemIsValid = true;
                break;
            }
        }

        if (!$itemIsValid) {
            throw new InvalidArgumentException(sprintf(
                'Invalid item type given to %s. Expected one of %s but was given %s.',
                static::class,
                implode(', ', $this->itemTypes),
                is_object($item) ? get_class($item) : @gettype($item)
            ));
        }
    }

    private function __clone()
    {
        $this->compositeVector = new Vector($this->compositeVector->toArray());
    }
}
