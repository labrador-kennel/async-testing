<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\NoAssertions;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function noAssertions() {

    }

}