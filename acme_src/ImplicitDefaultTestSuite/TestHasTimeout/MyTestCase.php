<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestHasTimeout;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\Timeout;
use Labrador\AsyncUnit\Framework\TestCase;
use function Amp\delay;

class MyTestCase extends TestCase {

    #[Test]
    #[Timeout(100)]
    public function timeOutTest() : void {
        delay(0.500);
        $this->assert->stringEquals('a', 'a');
    }

}