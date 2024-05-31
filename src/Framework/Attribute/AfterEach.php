<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AfterEach {

    public function __construct(public readonly int $priority = 0) {}

}