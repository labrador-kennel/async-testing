<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabled;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Disabled]
class MyTestSuite extends TestSuite {

}