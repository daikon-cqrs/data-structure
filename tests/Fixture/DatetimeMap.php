<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure\Fixture;

use Daikon\DataStructure\TypedMapInterface;
use Daikon\DataStructure\TypedMapTrait;
use DateTimeInterface;
use stdClass;

final class DatetimeMap implements TypedMapInterface
{
    use TypedMapTrait {
        __clone as __mapclone;
    }

    private stdClass $testVar;

    public function __construct(iterable $datetimes = [], stdClass $testVar = null)
    {
        $this->testVar = $testVar ?? new stdClass;
        $this->init($datetimes, [DatetimeInterface::class]);
    }

    public function getTestVar(): stdClass
    {
        return $this->testVar;
    }

    public function __clone()
    {
        $this->__mapclone();
        $this->testVar = clone $this->testVar;
    }
}
