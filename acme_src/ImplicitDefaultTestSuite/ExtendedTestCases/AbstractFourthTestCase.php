<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Labrador\AsyncUnit\Framework\Attribute\Test;

abstract class AbstractFourthTestCase extends ThirdTestCase {

    #[Test]
    public function fourthEnsureSomething() {
        $this->assert->isNull(null);
        $this->assert->isFalse(false);
        $this->assert->isTrue(true);
        $this->assert->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}