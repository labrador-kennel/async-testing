<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\WithAsyncUnitConfig\tests\Bar;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FooTestCase extends TestCase {

    #[Test]
    public function testIntEquals() {
        $this->assert->intEquals(1, 1);
    }

}