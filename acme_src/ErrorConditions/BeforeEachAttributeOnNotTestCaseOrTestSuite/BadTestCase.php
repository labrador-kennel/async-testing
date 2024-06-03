<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeEachAttributeOnNotTestCaseOrTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[BeforeEach]
    public function ensureSomething() {

    }

}