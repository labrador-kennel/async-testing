<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class BadTestCase extends TestCase {

    #[Test]
    public function throwException() {
        throw new \RuntimeException(__METHOD__);
    }

}