<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class BeforeEach {

    public function __construct(private int $priority = 0) {}

}