<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteAfterEach\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    #[AfterEach]
    public function checkEach() {

    }

}