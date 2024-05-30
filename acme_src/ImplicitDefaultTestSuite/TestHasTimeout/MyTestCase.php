<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestHasTimeout;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\Timeout;
use Cspray\Labrador\AsyncUnit\TestCase;
use function Amp\delay;

class MyTestCase extends TestCase {

    #[Test]
    #[Timeout(100)]
    public function timeOutTest() : void {
        delay(0.500);
        $this->assert()->stringEquals('a', 'a');
    }

}