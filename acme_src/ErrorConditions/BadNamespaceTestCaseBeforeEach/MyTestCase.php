<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestCaseBeforeEach\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[BeforeEach]
    public function checkEach() {

    }

}