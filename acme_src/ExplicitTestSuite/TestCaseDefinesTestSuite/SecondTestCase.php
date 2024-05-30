<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite as TestSuiteAttribute;
use Generator;

#[TestSuiteAttribute(MySecondTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSomethingIsNull() : void {
        $this->asyncAssert()->isNull(Future::complete());
    }

}