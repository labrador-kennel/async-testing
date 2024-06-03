<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestCase;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class BazTestCase extends TestCase {

    private bool $testInvoked = false;

    #[Test]
    public function ensureSomething() {
        $this->testInvoked = true;
    }

    public function getName() : string {
        return self::class;
    }

    public function getTestInvoked() : bool {
        return $this->testInvoked;
    }
}