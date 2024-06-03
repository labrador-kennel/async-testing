<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionOnly;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Exception\Exception;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkWhenExpectException() {
        $this->expect()->exception(Exception::class);

        throw new InvalidArgumentException('The message does not matter');
    }

}