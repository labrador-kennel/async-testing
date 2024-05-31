<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledCustomMessage;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Disabled('The AttachToTestSuite is disabled')]
class MyTestSuite extends TestSuite {

}