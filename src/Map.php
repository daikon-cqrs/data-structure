<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Daikon\Interop\Assert;
use Daikon\Interop\Assertion;
use Ds\Map as DsMap;

abstract class Map implements MapInterface
{
    protected DsMap $compositeMap;

    protected function init(iterable $values): void
    {
        Assertion::false(isset($this->compositeMap), 'Cannot reinitialize map.');

        foreach ($values as $key => $value) {
            $this->assertValidKey($key);
            $this->assertValidType($value);
        }

        /** @var \Traversable $values */
        $this->compositeMap = new DsMap($values);
    }

    /** @return static */
    public function empty(): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeMap->clear();
        return $copy;
    }

    public function keys(): array
    {
        $this->assertInitialized();
        return $this->compositeMap->keys()->toArray();
    }

    public function has(string $key): bool
    {
        $this->assertInitialized();
        return $this->compositeMap->hasKey($key);
    }

    public function get(string $key, $default = null)
    {
        $this->assertInitialized();
        if (func_num_args() === 1) {
            Assertion::satisfy($key, [$this, 'has'], "Key '$key' not found and no default provided.");
            return $this->compositeMap->get($key);
        } else {
            if (!is_null($default)) {
                $this->assertValidType($default);
            }
            return $this->compositeMap->get($key, $default);
        }
    }

    /** @return static */
    public function with(string $key, $value): self
    {
        $this->assertInitialized();
        $this->assertValidType($value);
        $copy = clone $this;
        $copy->compositeMap->put($key, $value);
        return $copy;
    }

    /** @return static */
    public function without(string $key): self
    {
        $this->assertInitialized();
        Assertion::satisfy($key, [$this, 'has'], "Key '$key' not found.");
        $copy = clone $this;
        $copy->compositeMap->remove($key);
        return $copy;
    }

    public function first()
    {
        $this->assertInitialized();
        /** @psalm-suppress MissingPropertyType */
        return $this->compositeMap->first()->value;
    }

    public function last()
    {
        $this->assertInitialized();
        /** @psalm-suppress MissingPropertyType */
        return $this->compositeMap->last()->value;
    }

    public function isEmpty(): bool
    {
        $this->assertInitialized();
        return $this->compositeMap->isEmpty();
    }

    /** @param static $comparator */
    public function equals($comparator): bool
    {
        $this->assertValidMap($comparator);
        return $this->unwrap() === $comparator->unwrap();
    }

    public function count(): int
    {
        $this->assertInitialized();
        return $this->compositeMap->count();
    }

    public function unwrap(): array
    {
        $this->assertInitialized();
        return $this->compositeMap->toArray();
    }

    /** @psalm-suppress ImplementedReturnTypeMismatch */
    public function getIterator(): DsMap
    {
        return $this->compositeMap;
    }

    protected function assertInitialized(): void
    {
        Assertion::true(isset($this->compositeMap), 'Map is not initialized.');
    }

    /** @param mixed $key */
    protected function assertValidKey($key): void
    {
        Assert::that($key, 'Key must be a valid string.')->string()->notEmpty();
    }

    /** @param mixed $value */
    protected function assertValidType($value): void
    {
        Assertion::true(
            is_array($value) || is_scalar($value),
            sprintf(
                "Invalid value type given to '%s', expected scalar or array but was given '%s'.",
                static::class,
                is_object($value) ? get_class($value) : @gettype($value)
            )
        );
    }

    /** @param mixed $map */
    protected function assertValidMap($map): void
    {
        Assertion::isInstanceOf(
            $map,
            static::class,
            sprintf("Map operation must be on same type as '%s'.", static::class)
        );
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function __clone()
    {
        $this->compositeMap = clone $this->compositeMap;
    }
}
