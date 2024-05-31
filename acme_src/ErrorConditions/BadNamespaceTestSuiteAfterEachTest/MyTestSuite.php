<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteAfterEachTest\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\AfterEachTest;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    #[AfterEachTest]
    public function checkEach() {

    }

}