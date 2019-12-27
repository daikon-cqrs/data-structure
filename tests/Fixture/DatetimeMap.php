<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure\Fixture;

use Countable;
use Daikon\DataStructure\TypedMapTrait;
use DateTimeInterface;
use IteratorAggregate;

final class DatetimeMap implements IteratorAggregate, Countable
{
    use TypedMapTrait;

    public function __construct(array $datetimes = [])
    {
        $this->init($datetimes, DatetimeInterface::class);
    }
}
