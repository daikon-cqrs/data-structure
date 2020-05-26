<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Assert\Assert;
use Ds\Map;
use RuntimeException;

trait MapTrait
{
    private Map $compositeMap;

    private function init(iterable $values): void
    {
        if (isset($this->compositeVector)) {
            throw new RuntimeException('Cannot reinitialize map');
        }

        foreach ($values as $key => $value) {
            $this->assertValidKey($key);
            $this->assertValidType($value);
        }

        $this->compositeMap = new Map($values);
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
            Assert::that($this->has($key))->true("Key '$key' not found and no default provided");
            return $this->compositeMap->get($key);
        } else {
            if (!is_null($default)) {
                $this->assertValidType($default);
            }
            return $this->compositeMap->get($key, $default);
        }
    }

    public function with(string $key, $value): self
    {
        $this->assertInitialized();
        $this->assertValidType($value);
        $copy = clone $this;
        $copy->compositeMap->put($key, $value);
        return $copy;
    }

    public function without(string $key): self
    {
        $this->assertInitialized();
        Assert::that($this->has($key))->true("Key '$key' not found");
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

    /** @param static $map */
    public function equals($map): bool
    {
        $this->assertValidMap($map);
        return $this->unwrap() === $map->unwrap();
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
    public function getIterator(): Map
    {
        return $this->compositeMap;
    }

    private function assertInitialized(): void
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (!isset($this->compositeMap)) {
            throw new RuntimeException('Map is not initialized');
        }
    }

    /** @param mixed $key */
    private function assertValidKey($key): void
    {
        Assert::that($key, 'Key must be a valid string')->string()->notEmpty();
    }

    /** @param mixed $value */
    private function assertValidType($value): void
    {
        $valueIsValid = is_array($value) || is_scalar($value);

        Assert::that($valueIsValid)->true(sprintf(
            "Invalid value type given to %s, expected scalar or array but was given '%s'",
            static::class,
            is_object($value) ? get_class($value) : @gettype($value)
        ));
    }

    /** @param mixed $map */
    private function assertValidMap($map): void
    {
        Assert::that($map)->isInstanceOf(
            static::class,
            'Map operation must be on same type as '.static::class
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
