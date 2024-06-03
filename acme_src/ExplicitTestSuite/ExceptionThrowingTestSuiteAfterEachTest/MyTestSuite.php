<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEachTest;

use Labrador\AsyncUnit\Framework\Attribute\AfterEachTest;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[AfterEachTest]
    public function throwEachTestException() {
        throw new \RuntimeException('AttachToTestSuite AfterEachTest');
    }

}