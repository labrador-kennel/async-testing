<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function testSomething() : void {
        $this->assert->arrayEquals([], $this->testSuite->getState());
    }

    #[Test]
    public function testSomethingElse() : void {
        $this->assert->arrayEquals([], $this->testSuite->getState());
    }

    #[Test]
    public function testItAgain() : void {
        $this->assert->arrayEquals([], $this->testSuite->getState());
    }

}