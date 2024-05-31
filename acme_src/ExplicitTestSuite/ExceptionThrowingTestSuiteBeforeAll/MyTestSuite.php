<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeAll;

use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeAll]
    public function throwException() {
        throw new \RuntimeException('Thrown in AttachToTestSuite');
    }

}