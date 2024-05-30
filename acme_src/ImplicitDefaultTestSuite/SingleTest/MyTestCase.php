<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\SingleTest;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;
use function Amp\delay;

class MyTestCase extends TestCase {

    private bool $testInvoked = false;

    #[Test]
    public function ensureSomethingHappens() : void {
        delay(0.5);
        $this->testInvoked = true;
        $this->assert()->stringEquals('foo', 'foo');
    }

    public function getTestInvoked() : bool {
        return $this->testInvoked;
    }
}