<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterEachAttributeOnNotTestCaseOrTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;

class BadTestCase {

    // We forgot to implement TestCase

    #[AfterEach]
    public function ensureSomething() {

    }

}