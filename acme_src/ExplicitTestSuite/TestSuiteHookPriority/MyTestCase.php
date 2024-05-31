<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteHookPriority;

use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {
        $this->assert->isTrue(true);
    }

}