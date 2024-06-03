<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteBeforeAll\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    #[BeforeAll]
    public function checkBefore() {

    }

}