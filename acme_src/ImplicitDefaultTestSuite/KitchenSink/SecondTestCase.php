<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink;

use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(FirstTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function checkTwo() {
        $this->assert()->instanceOf(TestCase::class, $this);
        $this->asyncAssert()->instanceOf(TestCase::class, Future::complete($this));
    }

    #[Test]
    #[Disabled]
    public function checkTwoDisabled() {
        throw new \RuntimeException('We should not run this');
    }

}