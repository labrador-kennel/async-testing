<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;

class ThirdTestCase extends AbstractSecondTestCase {

    #[Test]
    public function thirdEnsureSomething() {
        $this->asyncAssert()->arrayEquals([1,2,3], Future::complete([1,2,3]));
        $this->assert()->stringEquals('bar', 'bar');
        $this->assert()->isNull(null);
    }

}