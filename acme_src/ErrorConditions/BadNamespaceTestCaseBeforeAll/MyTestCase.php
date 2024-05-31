<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestCaseBeforeAll\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[BeforeAll]
    public static function checkBefore() {

    }

}