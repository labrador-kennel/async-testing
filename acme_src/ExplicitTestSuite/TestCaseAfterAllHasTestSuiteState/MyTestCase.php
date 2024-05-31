<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseAfterAllHasTestSuiteState;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestCase extends TestCase {

    private static ?string $state = null;

    #[AfterAll]
    public static function setState(TestSuite $testSuite) {
        self::$state = $testSuite->get('state');
    }

    #[Test]
    public function checkState() {
        $this->assert->isNull(self::$state);
    }

    public function getState() : ?string {
        return self::$state;
    }

}