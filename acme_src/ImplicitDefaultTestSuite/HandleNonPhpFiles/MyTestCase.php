<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HandleNonPhpFiles;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkAsyncNull() {
        $this->asyncAssert()->isNull(Future::complete());
    }

}