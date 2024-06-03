<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterAllTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSuiteCounter() : void {
        $this->assert->intEquals(0, $this->testSuite->getCounter());
    }

}