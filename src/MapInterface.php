<?php declare(strict_types=1);

namespace Daikon\DataStructure;

use Countable;
use Ds\Map;
use IteratorAggregate;

interface MapInterface extends IteratorAggregate, Countable
{
    public function keys(): array;

    public function has(string $key): bool;

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /** @param mixed $value */
    public function with(string $key, $value): self;

    public function without(string $key): self;

    /** @return mixed */
    public function first();

    /** @return mixed */
    public function last();

    public function isEmpty(): bool;

    /** @psalm-suppress MissingParamType */
    public function equals($map): bool;

    public function unwrap(): array;

    public function getIterator(): Map;

    /** @return mixed */
    public function __get(string $key);
}
