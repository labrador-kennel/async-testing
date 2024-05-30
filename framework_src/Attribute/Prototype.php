<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

/**
 *
 *
 * @codeCoverageIgnore
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Prototype {

    /**
     * @param non-empty-list<class-string> $validTypes
     */
    public function __construct(public readonly array $validTypes) {}

}
