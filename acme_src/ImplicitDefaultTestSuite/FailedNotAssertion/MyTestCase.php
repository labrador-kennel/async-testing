<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\FailedNotAssertion;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkFailedNotAssertion() {
        $this->assert->not()->stringEquals('foo', 'foo');
    }

}