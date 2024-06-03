<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;


use Labrador\AsyncUnit\Framework\TestCase;

class CustomAssertionTestCase extends TestCase {

    public function doCustomAssertion() {
        $this->assert->myCustomAssertion(1, 2, 3);
    }

}