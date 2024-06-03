<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseBeforeAllHasTestSuiteState;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestCase extends TestCase {

    private static string $state;

    #[BeforeAll]
    public static function setState(TestSuite $testSuite) {
        self::$state = $testSuite->get('state');
    }

    #[Test]
    public function checkState() {
        $this->assert->stringEquals('AsyncUnit', self::$state);
    }

}