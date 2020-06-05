<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Assert\Assert;
use Daikon\Interop\RuntimeException;
use Ds\Map;

trait TypedMapTrait
{
    protected Map $compositeMap;

    /** @var string[] */
    protected array $validTypes = [];

    /** @param string[] $validTypes */
    protected function init(iterable $objects, array $validTypes): void
    {
        if (isset($this->compositeMap)) {
            throw new RuntimeException('Cannot reinitialize map.');
        }

        Assert::thatAll($validTypes, 'Invalid map types.')->string()->notEmpty();
        $this->validTypes = $validTypes;
        $this->compositeMap = new Map;

        foreach ($objects as $key => $object) {
            $this->assertValidKey($key);
            $this->assertValidType($object);
            /** @psalm-suppress MixedClone */
            $this->compositeMap->put($key, clone $object);
        }
    }

    public function keys(): array
    {
        $this->assertInitialized();
        return $this->compositeMap->keys()->toArray();
    }

    public function has(string $key): bool
    {
        $this->assertInitialized();
        $this->assertValidKey($key);
        return $this->compositeMap->hasKey($key);
    }

    public function get(string $key, $default = null): ?object
    {
        $this->assertInitialized();
        $this->assertValidKey($key);
        if (func_num_args() === 1) {
            Assert::that($this->has($key))->true("Key '$key' not found and no default provided.");
            /** @psalm-suppress MixedClone */
            return clone $this->compositeMap->get($key);
        } else {
            if (!is_null($default)) {
                $this->assertValidType($default);
            }
            $object = $this->compositeMap->get($key, $default);
            /** @psalm-suppress MixedClone */
            return is_null($object) ? null : clone $object;
        }
    }

    public function with(string $key, object $object): self
    {
        $this->assertInitialized();
        $this->assertValidKey($key);
        $this->assertValidType($object);
        $copy = clone $this;
        $copy->compositeMap->put($key, clone $object);
        return $copy;
    }

    public function without(string $key): self
    {
        $this->assertInitialized();
        Assert::that($this->has($key))->true("Key '$key' not found.");
        $copy = clone $this;
        $copy->compositeMap->remove($key);
        return $copy;
    }

    /**
     * Note that this does not do a strict equality check because all objects are immutable so it's
     * unlikely that you will request a reference to an internal object. If you require more specific
     * matching use search(), filter(), unwrap object, or iterate.
     */
    public function find(object $object)
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        return array_search($object, $this->compositeMap->toArray(), false);
    }

    public function first(): object
    {
        $this->assertInitialized();
        /**
         * @psalm-suppress MissingPropertyType
         * @psalm-suppress MixedClone
         */
        return clone $this->compositeMap->first()->value;
    }

    public function last(): object
    {
        $this->assertInitialized();
        /**
         * @psalm-suppress MissingPropertyType
         * @psalm-suppress MixedClone
         */
        return clone $this->compositeMap->last()->value;
    }

    public function isEmpty(): bool
    {
        $this->assertInitialized();
        return $this->compositeMap->isEmpty();
    }

    /** @param static $map */
    public function merge($map): self
    {
        $this->assertInitialized();
        $this->assertValidMap($map);
        $copy = clone $this;
        $copy->compositeMap = $copy->compositeMap->merge(clone $map);
        return $copy;
    }

    /** @param static $map */
    public function intersect($map): self
    {
        $this->assertInitialized();
        $this->assertValidMap($map);
        return $this->filter(fn(string $key): bool => $map->has($key));
    }

    /** @param static $map */
    public function diff($map): self
    {
        $this->assertInitialized();
        $this->assertValidMap($map);
        return $this->filter(fn(string $key): bool => !$map->has($key));
    }

    public function filter(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeMap = $copy->compositeMap->filter($predicate);
        return $copy;
    }

    public function search(callable $predicate)
    {
        $this->assertInitialized();
        foreach ($this as $key => $object) {
            if (call_user_func($predicate, $object) === true) {
                return $key;
            }
        }
        return false;
    }

    public function map(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeMap->apply($predicate);
        return $copy;
    }

    /**
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $predicate, $initial = null)
    {
        $this->assertInitialized();
        return $this->compositeMap->reduce($predicate, $initial);
    }

    public function count(): int
    {
        $this->assertInitialized();
        return $this->compositeMap->count();
    }

    public function getValidTypes(): array
    {
        return $this->validTypes;
    }

    /**
     * This function does not clone the internal objects because you may want to access
     * them specifically for some reason.
     */
    public function unwrap(): array
    {
        $this->assertInitialized();
        return $this->compositeMap->toArray();
    }

    /** @psalm-suppress ImplementedReturnTypeMismatch */
    public function getIterator(): Map
    {
        $this->assertInitialized();
        $copy = clone $this;
        return $copy->compositeMap;
    }

    /** @param mixed $map */
    protected function assertValidMap($map): void
    {
        Assert::that($map)->isInstanceOf(
            static::class,
            'Map operation must be on same type as '.static::class
        );
    }

    protected function assertInitialized(): void
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (!isset($this->compositeMap)) {
            throw new RuntimeException('Map is not initialized.');
        }
    }

    /** @param mixed $key */
    protected function assertValidKey($key): void
    {
        Assert::that($key, 'Key must be a valid string.')->string()->notEmpty();
    }

    /** @param mixed $object */
    protected function assertValidType($object): void
    {
        Assert::thatAll(
            $this->validTypes,
            'Object types specified in '.static::class.' must be valid class or interface names.'
        )->string()
        ->notEmpty();

        $objectIsValid = array_reduce(
            $this->validTypes,
            fn(bool $carry, string $type): bool => $carry || is_a($object, $type, true),
            false
        );

        Assert::that($objectIsValid)->true(sprintf(
            "Invalid object type given to %s, expected one of [%s] but was given '%s'.",
            static::class,
            implode(', ', $this->validTypes),
            is_object($object) ? get_class($object) : @gettype($object)
        ));
    }

    public function __get(string $key): ?object
    {
        return $this->get($key);
    }

    public function __clone()
    {
        $this->assertInitialized();
        $this->compositeMap = new Map(array_map(
            /** @return mixed */
            fn(object $object) => clone $object,
            $this->compositeMap->toArray()
        ));
    }
}
