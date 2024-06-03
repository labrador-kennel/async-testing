<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

#[Attribute]
final class AfterEachTest {

    public function __construct(public readonly int $priority = 0) {}

}