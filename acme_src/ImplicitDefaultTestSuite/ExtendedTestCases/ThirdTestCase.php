<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Test;

class ThirdTestCase extends AbstractSecondTestCase {

    #[Test]
    public function thirdEnsureSomething() {
        $this->assert->arrayEquals([1,2,3], [1,2,3]);
        $this->assert->stringEquals('bar', 'bar');
        $this->assert->isNull(null);
    }

}