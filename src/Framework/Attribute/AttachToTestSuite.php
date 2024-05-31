<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Attribute;

use Attribute;

/**
 * Class AttachToTestSuite
 * @package Labrador\AsyncUnit\Framework\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AttachToTestSuite {

    public function __construct(private string $testSuiteClass) {}

}