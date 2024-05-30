<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;


use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;

abstract class AbstractSecondTestCase extends FirstTestCase {

    #[Test]
    public function secondEnsureSomething() {
        $this->assert()->intEquals(42, 42);
        $this->asyncAssert()->isFalse(Future::complete(false));
    }

}