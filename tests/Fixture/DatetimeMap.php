<?php
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Daikon\Tests\DataStructure\Fixture;

use Daikon\DataStructure\TypedMapTrait;
use DateTimeInterface;

final class DatetimeMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $datetimes = [])
    {
        $this->init($datetimes, DatetimeInterface::CLASS);
    }
}
