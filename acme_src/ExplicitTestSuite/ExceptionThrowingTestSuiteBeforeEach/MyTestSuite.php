<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEach;

use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeEach]
    public function throwEachException() {
        throw new \RuntimeException('AttachToTestSuite BeforeEach');
    }

}