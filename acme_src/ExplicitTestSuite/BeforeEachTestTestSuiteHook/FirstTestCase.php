<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeEachTestTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function testSomething() : void {
        $this->assert->arrayEquals('foo', 'foo');
    }

    #[Test]
    public function testSomethingElse() : void {
        $this->assert->stringEquals('bar', 'bar');
    }

    #[Test]
    public function testItAgain() : void {
        $this->assert->stringEquals('baz', 'baz');
    }

}