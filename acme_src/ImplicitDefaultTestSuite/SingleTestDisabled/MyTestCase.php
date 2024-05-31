<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\SingleTestDisabled;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    #[Disabled]
    public function skippedTest() {
        throw new \RuntimeException('We should not actually execute this function');
    }

}