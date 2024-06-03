<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseAfterAllHasTestSuiteState;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeAll]
    public function setState() {
        $this->set('state', 'AsyncUnit');
    }

}