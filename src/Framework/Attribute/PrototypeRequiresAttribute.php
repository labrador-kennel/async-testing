<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

/**
 * Class PrototypeRequiresAttribute
 * @package Labrador\AsyncUnit\Framework\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class PrototypeRequiresAttribute {

    public function __construct(
        private string $requiredAttribute
    ) {}

}