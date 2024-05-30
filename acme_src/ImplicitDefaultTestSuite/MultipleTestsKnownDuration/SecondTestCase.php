<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;


use Amp\Delayed;
use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use function Amp\delay;

class SecondTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        delay(0.1);
        $this->asyncAssert()->isEmpty(Future::complete([]));
    }

    #[Test]
    public function checkTwo() {
        delay(0.1);
        $this->assert()->isTrue(true);
    }

}