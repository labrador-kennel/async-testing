<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

/**
 * Class DataProvider
 * @package Labrador\AsyncUnit\Framework\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class DataProvider {

    public function __construct(private string $methodName) {}

}