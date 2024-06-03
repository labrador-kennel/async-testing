<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class BeforeAll {

    public function __construct(public readonly int $priority = 0) {}

}