<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HandleNonPhpFiles;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkAsyncNull() {
        $this->asyncAssert()->isNull(Future::complete());
    }

}