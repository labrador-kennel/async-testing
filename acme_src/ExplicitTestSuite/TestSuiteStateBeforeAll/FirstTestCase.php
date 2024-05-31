<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteStateBeforeAll;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function checkTestSuiteData() {
        $this->assert->stringEquals('bar', $this->testSuite->get('foo'));
    }

}