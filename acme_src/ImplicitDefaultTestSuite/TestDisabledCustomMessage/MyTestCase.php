<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledCustomMessage;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Disabled('Not sure what we should do here yet')]
    #[Test]
    public function testOne() {

    }

}