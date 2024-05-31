<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[Disabled('The TestCase is disabled')]
class MyTestCase extends TestCase {

    #[Test]
    public function testOne() {

    }

}