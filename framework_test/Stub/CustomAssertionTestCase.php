<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\TestCase;

class CustomAssertionTestCase extends TestCase {

    public function doCustomAssertion() {
        $this->assert()->myCustomAssertion(1, 2, 3);
    }

    public function doCustomAsyncAssertion() {
        $this->asyncAssert()->myCustomAssertion(1, 2, 3);
    }

}