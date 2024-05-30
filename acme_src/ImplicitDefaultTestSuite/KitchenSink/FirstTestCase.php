<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink;

use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(FirstTestSuite::class)]
class FirstTestCase extends TestCase {

    #[Test]
    public function testOne() {
        $this->assert()->countEquals(3, [1, 2, 3]);
    }

    #[Test]
    public function testTwo() {
        $this->asyncAssert()->countEquals(4, Future::complete(['a', 'b', 'c', 'd']));
    }

    #[Test]
    #[Disabled]
    public function disabledTest() {
        throw new \RuntimeException('We should not run this');
    }

}