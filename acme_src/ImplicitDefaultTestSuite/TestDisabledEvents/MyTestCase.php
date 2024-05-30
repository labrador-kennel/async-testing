<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledEvents;

use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testFailingFloatEquals() {
        $this->asyncAssert()->not()->floatEquals(3.14, Future::complete(3.14));
    }

    #[Test]
    public function testIsTrue() {
        $this->asyncAssert()->isTrue(Future::complete(true));
    }

    #[Test]
    #[Disabled]
    public function testIsDisabled() {

    }

}