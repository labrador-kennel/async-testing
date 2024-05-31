<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;

use Amp\Delayed;
use Amp\Future;
use Amp\Success;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use function Amp\delay;

class ThirdTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        delay(0.1);
        $this->assert->floatEquals(3.14, 3.14);
    }

    #[Test]
    public function checkTwo() {
        delay(0.1);
        $this->asyncAssert()->stringEquals('AsyncUnit', Future::complete('AsyncUnit'));
    }

    #[Test]
    public function checkThree() {
        delay(0.1);
        $this->assert->countEquals(2, ['a', 0]);
    }

}