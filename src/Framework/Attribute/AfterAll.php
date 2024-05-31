<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

#[Attribute]
final class AfterAll {

    public function __construct(private int $priority = 0) {}

}