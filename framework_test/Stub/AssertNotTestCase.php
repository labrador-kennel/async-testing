<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\TestCase;

class AssertNotTestCase extends TestCase {

    public function doNotAssertion() {
        $this->assert()->not()->stringEquals('foo', 'bar');
    }

    public function doFailingNotAssertions() {
        $this->assert()->not()->stringEquals('foo', 'foo');
    }

    public function doBothAssertions() {
        $this->assert()->stringEquals('bar', 'bar');
        $this->assert()->not()->stringEquals('foo', 'bar');
        $this->assert()->stringEquals('foo', 'foo');
    }

    public function doAsyncNotAssertion() {
        $this->asyncAssert()->not()->intEquals(1, Future::complete(2));
    }

}