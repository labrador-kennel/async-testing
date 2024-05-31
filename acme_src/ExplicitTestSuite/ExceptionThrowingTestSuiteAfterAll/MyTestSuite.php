<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;
use RuntimeException;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[AfterAll]
    public function throwException() {
        throw new RuntimeException('AttachToTestSuite AfterAll');
    }

}