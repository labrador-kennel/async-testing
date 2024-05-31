<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeEachTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class ThirdTestCase extends TestCase {

    #[Test]
    public function testFoo() : void {
        $this->assert->isNull(null);
    }

    #[Test]
    public function testBar() : void {
        $this->assert->isTrue(true);
    }

}