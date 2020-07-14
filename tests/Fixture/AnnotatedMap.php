<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/data-structure project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\DataStructure\Fixture;

use Daikon\DataStructure\TypedMap;
use Daikon\Interop\SupportsAnnotations;

/**
 * @type(DateTime)
 * @type(DateTimeImmutable)
 */
final class AnnotatedMap extends TypedMap
{
    use SupportsAnnotations;

    public function __construct(iterable $objects = [])
    {
        $this->init($objects, static::inferValidTypes());
    }
}
