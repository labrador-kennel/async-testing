<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;

use Amp\Future;
use Labrador\AsyncUnit\Framework\TestCase;

class FailingTestCase extends TestCase {

    public function doFailure() {
        $this->assert->stringEquals('foo', 'bar');
    }

    public function doFailureWithCustomMessage() {
        $this->assert->stringEquals('foo', 'bar', 'my custom message');
    }

}