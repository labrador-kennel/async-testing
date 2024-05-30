<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsNoAsyncAssertionsAssertMade;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function noAssertionButAsyncAssertionMade() {
        $this->expect()->noAssertions();

        $this->asyncAssert()->isNull(Future::complete(null));
        $this->asyncAssert()->isEmpty(Future::complete([]));
    }

}