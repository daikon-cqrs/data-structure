<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Ds\Map;

trait TypedMapTrait
{
    /** @var Map */
    private $compositeMap;

    /** @var null|string[] */
    private $itemTypes;

    public function has(string $key): bool
    {
        return $this->compositeMap->hasKey($key);
    }

    /**
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get(string $key)
    {
        return $this->compositeMap->get($key);
    }

    /**
     * @param mixed $item
     * @throws \InvalidArgumentException
     */
    public function set(string $key, $item): self
    {
        $this->assertItemType($item);
        $copy = clone $this;
        $copy->compositeMap->put($key, $item);
        return $copy;
    }

    public function count(): int
    {
        return count($this->compositeMap);
    }

    public function toNative(): array
    {
        return $this->compositeMap->toArray();
    }

    public function isEmpty(): bool
    {
        return $this->compositeMap->isEmpty();
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function getIterator(): \Iterator
    {
        return $this->compositeMap->getIterator();
    }

    public function getItemTypes(): ?array
    {
        return $this->itemTypes;
    }

    /**
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /** @param string|string[] $itemTypes */
    private function init(iterable $items, $itemTypes): void
    {
        $this->itemTypes = (array)$itemTypes;
        foreach ($items as $key => $item) {
            $this->assertItemKey($key);
            $this->assertItemType($item);
        }
        /** @psalm-suppress InvalidArgument */
        $this->compositeMap = new Map($items);
    }

    /** @param string $key */
    private function assertItemKey($key): void
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid item key given to %s. Expected string but was given %s.',
                static::class,
                is_object($key) ? get_class($key) : @gettype($key)
            ));
        }
    }

    /** @param mixed $item */
    private function assertItemType($item): void
    {
        if (empty($this->itemTypes)) {
            throw new \RuntimeException('Item types have not been specified.');
        }

        $itemIsValid = false;
        foreach ($this->itemTypes as $type) {
            if (is_a($item, $type)) {
                $itemIsValid = true;
                break;
            }
        }

        if (!$itemIsValid) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid item type given to %s. Expected one of %s but was given %s.',
                static::class,
                implode(', ', $this->itemTypes),
                is_object($item) ? get_class($item) : @gettype($item)
            ));
        }
    }

    public function __clone()
    {
        $this->compositeMap = new Map($this->compositeMap->toArray());
    }
}
