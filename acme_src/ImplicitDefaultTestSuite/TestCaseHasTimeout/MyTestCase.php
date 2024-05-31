<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseHasTimeout;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\Timeout;
use Labrador\AsyncUnit\Framework\TestCase;

#[Timeout(150)]
class MyTestCase extends TestCase {

    #[Test]
    public function testOne() {

    }

    #[Test]
    public function testTwo() {

    }
}