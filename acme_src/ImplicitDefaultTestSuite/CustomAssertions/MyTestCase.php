<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\CustomAssertions;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function ensureCustomAssertionsPass() {
        $this->assert()->theCustomAssertion('foo', 'bar');
        $this->asyncAssert()->theCustomAssertion('foo', Future::complete('bar'));
    }

}