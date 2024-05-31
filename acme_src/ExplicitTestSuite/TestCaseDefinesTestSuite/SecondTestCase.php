<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite as TestSuiteAttribute;
use Generator;

#[TestSuiteAttribute(MySecondTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSomethingIsNull() : void {
        $this->assert->isNull(null);
    }

}