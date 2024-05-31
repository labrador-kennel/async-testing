<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use function Amp\delay;

class FirstTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        delay(0.1);
        $this->assert->arrayEquals([1, 2, 3], [1, 2, 3]);
    }

}