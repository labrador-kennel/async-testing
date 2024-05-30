<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestKnownRunTime;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testTiming() {
        $this->asyncAssert()->floatEquals(3.14, Future::complete(3.14));
    }

}