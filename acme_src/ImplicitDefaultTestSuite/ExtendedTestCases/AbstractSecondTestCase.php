<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;


use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Test;

abstract class AbstractSecondTestCase extends FirstTestCase {

    #[Test]
    public function secondEnsureSomething() {
        $this->assert->intEquals(42, 42);
        $this->assert->isFalse(false);
    }

}