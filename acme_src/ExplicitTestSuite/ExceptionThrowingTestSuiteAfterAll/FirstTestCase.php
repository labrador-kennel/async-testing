<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function ensureSomething() {
        $this->assert->isTrue(true);
    }

}