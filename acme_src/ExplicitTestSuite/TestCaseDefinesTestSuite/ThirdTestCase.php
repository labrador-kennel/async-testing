<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite as TestSuiteAttribute;
use Labrador\AsyncUnit\Framework\TestCase;

#[TestSuiteAttribute(MySecondTestSuite::class)]
class ThirdTestCase extends TestCase {

    #[Test]
    public function ensureStringEquals() {
        $this->assert->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}