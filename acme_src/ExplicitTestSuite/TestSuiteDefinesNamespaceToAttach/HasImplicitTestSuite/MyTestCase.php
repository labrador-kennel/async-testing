<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDefinesNamespaceToAttach\HasImplicitTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {
        $this->assert->isTrue(true);
    }

}