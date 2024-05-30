<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\TestCase;

class FailingTestCase extends TestCase {

    public function doFailure() {
        $this->assert()->stringEquals('foo', 'bar');
    }

    public function doAsyncFailure() {
        $this->asyncAssert()->stringEquals('foo', Future::complete('bar'));
    }

    public function doFailureWithCustomMessage() {
        $this->assert()->stringEquals('foo', 'bar', 'my custom message');
    }

}