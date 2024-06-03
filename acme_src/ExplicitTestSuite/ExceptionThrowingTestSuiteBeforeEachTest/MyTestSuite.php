<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEachTest;

use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEachTest;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeEachTest]
    public function throwEachTestException() {
        throw new \RuntimeException('AttachToTestSuite BeforeEachTest');
    }

}