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
use Ds\Vector;
use OutOfRangeException;

trait TypedListTrait
{
    protected Vector $compositeVector;

    /** @param string[] $validTypes */
    protected array $validTypes = [];

    /** @param string[] $validTypes */
    protected function init(iterable $objects, array $validTypes): void
    {
        Assertion::false(isset($this->compositeVector), 'Cannot reinitialize map.');
        Assert::thatAll($validTypes, 'Invalid list types.')->string()->notEmpty();

        $this->validTypes = $validTypes;
        $this->compositeVector = new Vector;

        foreach ($objects as $index => $object) {
            $this->assertValidIndex($index);
            $this->assertValidType($object);
            /** @psalm-suppress MixedClone */
            $this->compositeVector->push(clone $object);
        }
    }

    public function has(int $index): bool
    {
        $this->assertInitialized();
        try {
            //@todo maybe a better way to do this
            $this->compositeVector->get($index);
            return true;
        } catch (OutOfRangeException $error) {
            return false;
        }
    }

    public function get(int $index, $default = null): ?object
    {
        $this->assertInitialized();
        if (func_num_args() === 1) {
            Assertion::satisfy($index, [$this, 'has'], "Index $index not found and no default provided.");
            /** @psalm-suppress MixedClone */
            return clone $this->compositeVector->get($index);
        } else {
            if (!is_null($default)) {
                $this->assertValidType($default);
            }
            $object = $this->has($index)
                ? $this->compositeVector->get($index)
                : $default;
            /** @psalm-suppress MixedClone */
            return is_null($object) ? null : clone $object;
        }
    }

    public function with(int $index, object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        Assertion::satisfy($index, [$this, 'has'], "Index $index not found.");
        $copy = clone $this;
        $copy->compositeVector->set($index, clone $object);
        return $copy;
    }

    public function without(int $index): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->remove($index);
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
        return array_search($object, $this->compositeVector->toArray(), false);
    }

    public function first(): object
    {
        $this->assertInitialized();
        /** @psalm-suppress MixedClone */
        return clone $this->compositeVector->first();
    }

    public function last(): object
    {
        $this->assertInitialized();
        /** @psalm-suppress MixedClone */
        return clone $this->compositeVector->last();
    }

    public function isEmpty(): bool
    {
        $this->assertInitialized();
        return $this->compositeVector->isEmpty();
    }

    /** @param static $list */
    public function append($list): self
    {
        $this->assertInitialized();
        $this->assertValidList($list);
        $copy = clone $this;
        /** @psalm-suppress PropertyTypeCoercion */
        $copy->compositeVector = $copy->compositeVector->merge($list);
        return $copy;
    }

    public function push(object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        $copy = clone $this;
        $copy->compositeVector->push(clone $object);
        return $copy;
    }

    public function unshift(object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        $copy = clone $this;
        $copy->compositeVector->unshift(clone $object);
        return $copy;
    }

    public function reverse(): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->reverse();
        return $copy;
    }

    public function replace(callable $predicate, object $replacement): self
    {
        $this->assertInitialized();
        $this->assertValidType($replacement);
        $objects = [];
        foreach ($this as $object) {
            $objects[] = $predicate($object) === true ? $replacement : $object;
        }
        /** @psalm-suppress TooManyArguments */
        return new static($objects);
    }

    public function sort(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->sort($predicate);
        return $copy;
    }

    public function filter(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        /** @psalm-suppress PropertyTypeCoercion */
        $copy->compositeVector = $copy->compositeVector->filter($predicate);
        return $copy;
    }

    public function search(callable $predicate)
    {
        $this->assertInitialized();
        foreach ($this as $index => $object) {
            if (call_user_func($predicate, $object) === true) {
                return $index;
            }
        }
        return false;
    }

    public function map(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->apply($predicate);
        return $copy;
    }

    public function reduce(callable $predicate, $initial = null)
    {
        $this->assertInitialized();
        /** @psalm-suppress PossiblyNullArgument */
        return $this->compositeVector->reduce($predicate, $initial);
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
        return $this->compositeVector->toArray();
    }

    public function count(): int
    {
        $this->assertInitialized();
        return $this->compositeVector->count();
    }

    /** @psalm-suppress ImplementedReturnTypeMismatch */
    public function getIterator(): Vector
    {
        $this->assertInitialized();
        $copy = clone $this;
        return $copy->compositeVector;
    }

    protected function assertInitialized(): void
    {
        Assertion::true(isset($this->compositeVector), 'List is not initialized.');
    }

    /** @param mixed $list */
    protected function assertValidList($list): void
    {
        Assertion::isInstanceOf(
            $list,
            static::class,
            sprintf("List operation must be on same type as '%s'.", static::class)
        );
    }

    /** @param mixed $index */
    protected function assertValidIndex($index): void
    {
        Assertion::integerish($index, 'Index must be a valid integer.');
    }

    /** @param mixed $object */
    protected function assertValidType($object): void
    {
        Assert::thatAll(
            $this->validTypes,
            sprintf("Object types specified in '%s' must be valid class or interface names.", static::class)
        )->string()
        ->notEmpty();

        Assertion::true(
            array_reduce(
                $this->validTypes,
                fn(bool $carry, string $type): bool => $carry || is_a($object, $type, true),
                false
            ),
            sprintf(
                "Invalid object type given to '%s', expected one of [%s] but was given '%s'.",
                static::class,
                implode(', ', $this->validTypes),
                is_object($object) ? get_class($object) : @gettype($object)
            )
        );
    }

    public function __get(int $index): ?object
    {
        return $this->get($index);
    }

    public function __clone()
    {
        $this->assertInitialized();
        $this->compositeVector = new Vector(array_map(
            fn(object $object): object => clone $object,
            $this->compositeVector->toArray()
        ));
    }
}
