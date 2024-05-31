<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDefinesNamespaceToAttach;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {
        $this->assert->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}