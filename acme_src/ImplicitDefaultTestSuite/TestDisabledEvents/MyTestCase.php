<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledEvents;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testFailingFloatEquals() {
        $this->assert->not()->floatEquals(3.14, 3.14);
    }

    #[Test]
    public function testIsTrue() {
        $this->assert->isTrue(true);
    }

    #[Test]
    #[Disabled]
    public function testIsDisabled() {

    }

}