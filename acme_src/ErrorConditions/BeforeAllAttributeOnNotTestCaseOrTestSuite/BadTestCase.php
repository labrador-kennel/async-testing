<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeAllAttributeOnNotTestCaseOrTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[BeforeAll]
    public static function ensureSomething() {

    }

}