<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestCaseAfterAll\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[AfterAll]
    public static function checkSomething() {

    }

}