<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure\Fixture;

use Daikon\DataStructure\TypedMap;
use DateTimeInterface;

final class DatetimeMap extends TypedMap
{
    public function __construct(iterable $datetimes = [])
    {
        $this->init($datetimes, [DatetimeInterface::class]);
    }
}
