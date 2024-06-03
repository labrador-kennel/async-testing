<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteBeforeEachTest\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\BeforeEachTest;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    #[BeforeEachTest]
    public function checkEach() {

    }

}