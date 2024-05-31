<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestCaseAfterEach\IntentionallyBad;

use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[AfterEach]
    public function afterEach() {

    }

}