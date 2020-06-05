<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Countable;
use Ds\Map;
use IteratorAggregate;

interface TypedMapInterface extends IteratorAggregate, Countable
{
    public function keys(): array;

    public function has(string $key): bool;

    /** @param null|object $default */
    public function get(string $key, $default = null): ?object;

    public function with(string $key, object $object): self;

    public function without(string $key): self;

    /** @return string|bool */
    public function find(object $object);

    public function first(): object;

    public function last(): object;

    public function isEmpty(): bool;

    /** @psalm-suppress MissingParamType */
    public function merge($map): self;

    /** @psalm-suppress MissingParamType */
    public function intersect($map): self;

    /** @psalm-suppress MissingParamType */
    public function diff($map): self;

    public function filter(callable $predicate): self;

    /** @return string|bool */
    public function search(callable $predicate);

    public function map(callable $predicate): self;
    /**
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $predicate, $initial = null);

    public function getValidTypes(): array;

    public function unwrap(): array;

    public function getIterator(): Map;

    public function __get(string $key): ?object;
}
