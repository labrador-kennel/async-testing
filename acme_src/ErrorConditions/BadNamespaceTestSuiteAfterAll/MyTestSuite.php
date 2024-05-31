<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteAfterAll\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    #[AfterAll]
    public function checkAll() {

    }

}