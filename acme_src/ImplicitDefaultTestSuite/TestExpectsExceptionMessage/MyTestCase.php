<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionMessage;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkExceptionMessage() {
        $this->expect()->exception(InvalidArgumentException::class);
        $this->expect()->exceptionMessage('This is the message that I expect');

        throw new InvalidArgumentException('This is the message that I expect');
    }

}