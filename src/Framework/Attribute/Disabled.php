<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

/**
 * @package Labrador\AsyncUnit\Framework\Attribute
 * @codeCoverageIgnore
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class Disabled {

    public function __construct(private ?string $reason = null) {}

}