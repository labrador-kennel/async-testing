<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Labrador\AsyncUnit\Framework\Attribute\Test;

class FifthTestCase extends AbstractFourthTestCase {

    #[Test]
    public function fifthEnsureSomething() {
        $this->assert->isFalse(false);
        $this->assert->not()->stringEquals('AsyncUnit', 'PHPUnit');
        $this->assert->intEquals(1, 1);
        $this->assert->floatEquals(3.14, 3.14);
        $this->assert->isTrue(false);
    }

}