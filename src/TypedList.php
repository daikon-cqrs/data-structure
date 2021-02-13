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

abstract class TypedList implements TypedListInterface
{
    protected Vector $compositeVector;

    protected array $validTypes = [];

    protected function init(iterable $objects, array $validTypes): void
    {
        Assertion::false(isset($this->compositeVector), 'Cannot reinitialize list.');
        Assertion::minCount($validTypes, 1, 'No valid types specified.');
        Assert::thatAll($validTypes, 'Invalid list types.')->string()->notEmpty();

        $this->validTypes = $validTypes;
        $this->compositeVector = new Vector;

        foreach ($objects as $index => $object) {
            $this->assertValidIndex($index);
            $this->assertValidType($object);
            $this->compositeVector->push(clone $object);
        }
    }

    /** @return static */
    public function empty(): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->clear();
        return $copy;
    }

    public function has(int $index): bool
    {
        $this->assertInitialized();
        try {
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
            return clone $this->compositeVector->get($index);
        }
        if (!is_null($default)) {
            $this->assertValidType($default);
        }
        $object = $this->has($index)
            ? $this->compositeVector->get($index)
            : $default;
        return is_null($object) ? null : clone $object;
    }

    /** @return static */
    public function with(int $index, object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        Assertion::satisfy($index, [$this, 'has'], "Index $index not found.");
        $copy = clone $this;
        $copy->compositeVector->set($index, clone $object);
        return $copy;
    }

    /** @return static */
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
        return clone $this->compositeVector->first();
    }

    public function last(): object
    {
        $this->assertInitialized();
        return clone $this->compositeVector->last();
    }

    public function isEmpty(): bool
    {
        $this->assertInitialized();
        return $this->compositeVector->isEmpty();
    }

    /**
     * @param static $list
     * @return static
     */
    public function append($list): self
    {
        $this->assertInitialized();
        $this->assertValidList($list);
        $copy = clone $this;
        /** @var iterable $list */
        $copy->compositeVector = $copy->compositeVector->merge($list);
        return $copy;
    }

    /** @return static */
    public function push(object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        $copy = clone $this;
        $copy->compositeVector->push(clone $object);
        return $copy;
    }

    /** @return static */
    public function unshift(object $object): self
    {
        $this->assertInitialized();
        $this->assertValidType($object);
        $copy = clone $this;
        $copy->compositeVector->unshift(clone $object);
        return $copy;
    }

    /** @return static */
    public function reverse(): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->reverse();
        return $copy;
    }

    /** @return static */
    public function replace(callable $predicate, object $replacement): self
    {
        $this->assertInitialized();
        $this->assertValidType($replacement);
        $copy = $this->empty();
        foreach ($this as $object) {
            $copy = $copy->push($predicate($object) === true ? $replacement : $object);
        }
        return $copy;
    }

    /** @return static */
    public function sort(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector->sort($predicate);
        return $copy;
    }

    /** @return static */
    public function filter(callable $predicate): self
    {
        $this->assertInitialized();
        $copy = clone $this;
        $copy->compositeVector = $copy->compositeVector->filter($predicate);
        return $copy;
    }

    public function search(callable $predicate)
    {
        $this->assertInitialized();
        foreach ($this as $index => $object) {
            if ($predicate($object) === true) {
                return $index;
            }
        }
        return false;
    }

    /** @return static */
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

    /** @psalm-suppress RedundantPropertyInitializationCheck */
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

    public function __get(string $index): ?object
    {
        return $this->get((int)$index);
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
