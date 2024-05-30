<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\SingleTestAsyncAssertion;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function ensureAsyncAssert() {
        $this->asyncAssert()->stringEquals('foo', Future::complete('foo'));
    }

}