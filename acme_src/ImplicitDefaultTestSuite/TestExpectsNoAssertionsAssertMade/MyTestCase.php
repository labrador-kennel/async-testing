<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsNoAssertionsAssertMade;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testNoAssertionAssertionMade() : void {
        $this->expect()->noAssertions();
        $this->assert->isNull(null);
        $this->assert->isTrue(true);
    }

}