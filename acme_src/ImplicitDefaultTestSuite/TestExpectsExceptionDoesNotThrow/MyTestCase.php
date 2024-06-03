<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionDoesNotThrow;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkDoesNotThrow() {
        $this->expect()->exception(InvalidArgumentException::class);

        $this->assert->isEmpty([]);
    }

}