<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use function Amp\delay;

class FirstTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        delay(0.1);
        $this->assert()->arrayEquals([1, 2, 3], [1, 2, 3]);
    }

}