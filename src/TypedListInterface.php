<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\DataStructure;

use Countable;
use Ds\Vector;
use IteratorAggregate;

interface TypedListInterface extends IteratorAggregate, Countable
{
    public function empty(): self;

    public function has(int $index): bool;

    /** @param null|object $default */
    public function get(int $index, $default = null): ?object;

    public function with(int $index, object $object): self;

    public function without(int $index): self;

    /** @return int|bool */
    public function find(object $object);

    public function first(): object;

    public function last(): object;

    public function isEmpty(): bool;

    /** @psalm-suppress MissingParamType */
    public function append($list): self;

    public function push(object $object): self;

    public function unshift(object $object): self;

    public function reverse(): self;

    public function replace(callable $predicate, object $replacement): self;

    public function sort(callable $predicate): self;

    public function filter(callable $predicate): self;

    /** @return int|bool */
    public function search(callable $predicate);

    public function map(callable $predicate): self;

    /**
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $predicate, $initial = null);

    public function getValidTypes(): array;

    public function unwrap(): array;

    public function getIterator(): Vector;

    public function __get(int $index): ?object;
}
