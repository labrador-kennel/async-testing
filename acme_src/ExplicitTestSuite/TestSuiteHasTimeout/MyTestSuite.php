<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteHasTimeout;

use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Timeout;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Timeout(125)]
#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

}