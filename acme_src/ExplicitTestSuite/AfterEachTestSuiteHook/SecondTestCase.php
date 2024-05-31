<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class SecondTestCase extends TestCase {

    #[Test]
    public function ensureIntEquals() : void {
        $this->assert->arrayEquals([], $this->testSuite->getState());
    }

}