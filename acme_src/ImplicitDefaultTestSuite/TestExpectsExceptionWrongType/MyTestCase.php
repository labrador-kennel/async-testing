<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionWrongType;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkWrongExceptionThrown() {
        $this->expect()->exception(InvalidArgumentException::class);

        throw new InvalidStateException();
    }

}