<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink;

use Amp\Future;
use Amp\Success;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Context\AssertionContext;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(FirstTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function checkTwo() {
        $this->assert->instanceOf(TestCase::class, $this);
        $this->assert->instanceOf(AssertionContext::class, $this->assert);
    }

    #[Test]
    #[Disabled]
    public function checkTwoDisabled() {
        throw new \RuntimeException('We should not run this');
    }

}