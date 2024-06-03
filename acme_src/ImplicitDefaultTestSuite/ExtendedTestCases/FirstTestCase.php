<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function firstEnsureSomething() {
        $this->assert->stringEquals('foo', 'foo');
    }

}