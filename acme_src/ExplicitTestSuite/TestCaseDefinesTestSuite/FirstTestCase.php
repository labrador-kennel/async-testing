<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite as TestSuiteAttribute;
use Labrador\AsyncUnit\Framework\TestCase;

#[TestSuiteAttribute(MyFirstTestSuite::class)]
class FirstTestCase extends TestCase {

    #[Test]
    public function ensureIntEquals() {
        $this->assert->intEquals(42, 42);
    }
}