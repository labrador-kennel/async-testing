<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink;

use Amp\Future;
use Amp\Success;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(FirstTestSuite::class)]
class FirstTestCase extends TestCase {

    #[Test]
    public function testOne() {
        $this->assert->countEquals(3, [1, 2, 3]);
    }

    #[Test]
    public function testTwo() {
        $this->assert->countEquals(4, ['a', 'b', 'c', 'd']);
    }

    #[Test]
    #[Disabled]
    public function disabledTest() {
        throw new \RuntimeException('We should not run this');
    }

}