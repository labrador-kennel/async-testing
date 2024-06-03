<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestKnownRunTime;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testTiming() {
        $this->asyncAssert()->floatEquals(3.14, Future::complete(3.14));
    }

}